<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeVendors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'market:merge-vendors {from=2} {to=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge one vendor store into another, including products, warehouse stocks, transactions, and orders.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fromId = (int)$this->argument('from');
        $toId = (int)$this->argument('to');

        $this->info("Merging Vendor ID {$fromId} into Vendor ID {$toId}...");

        $vFrom = DB::table('market_vendors')->where('id', $fromId)->first();
        $vTo = DB::table('market_vendors')->where('id', $toId)->first();

        if (!$vFrom) {
            $this->error("Source Vendor (ID {$fromId}) not found.");
            return 1;
        }

        if (!$vTo) {
            $this->error("Destination Vendor (ID {$toId}) not found.");
            return 1;
        }

        // Get Warehouses
        $whFrom = DB::table('market_warehouses')->where('vendor_id', $fromId)->first();
        $whTo = DB::table('market_warehouses')->where('vendor_id', $toId)->first();

        if (!$whFrom) {
            $this->warn("Source Warehouse for Vendor ID {$fromId} not found.");
        } else {
            $this->info("Source Warehouse: {$whFrom->name} ({$whFrom->code}) [ID: {$whFrom->id}]");
        }

        if (!$whTo) {
            $this->warn("Destination Warehouse for Vendor ID {$toId} not found.");
        } else {
            $this->info("Destination Warehouse: {$whTo->name} ({$whTo->code}) [ID: {$whTo->id}]");
        }

        $this->warn("This action will transfer all products, warehouse stocks, order items, questions, and reviews from Vendor {$fromId} to Vendor {$toId}, then delete Vendor {$fromId}.");
        
        if (!$this->confirm('Do you wish to proceed?')) {
            $this->info('Cancelled.');
            return 0;
        }

        DB::beginTransaction();

        try {
            // Get all products of source vendor
            $fromProducts = DB::table('market_vendor_products')->where('vendor_id', $fromId)->get();
            $this->info("Found " . $fromProducts->count() . " products in Source Vendor.");

            foreach ($fromProducts as $vp2) {
                // Check if destination vendor already has this product variant
                $vp1 = DB::table('market_vendor_products')
                    ->where('vendor_id', $toId)
                    ->where('product_variant_id', $vp2->product_variant_id)
                    ->first();

                if (!$vp1) {
                    // Case A: Destination vendor does NOT have this variant.
                    // Simply move the product to destination vendor.
                    DB::table('market_vendor_products')
                        ->where('id', $vp2->id)
                        ->update([
                            'vendor_id' => $toId,
                            'updated_at' => now(),
                        ]);
                    $this->line("Transferred Product Variant ID {$vp2->product_variant_id} to Destination Vendor.");

                    // If source warehouse exists and destination warehouse exists, update warehouse stocks warehouse_id
                    if ($whFrom && $whTo) {
                        DB::table('market_warehouse_stocks')
                            ->where('vendor_product_id', $vp2->id)
                            ->where('warehouse_id', $whFrom->id)
                            ->update([
                                'warehouse_id' => $whTo->id,
                                'updated_at' => now(),
                            ]);

                        DB::table('market_warehouse_transactions')
                            ->where('vendor_product_id', $vp2->id)
                            ->where('warehouse_id', $whFrom->id)
                            ->update([
                                'warehouse_id' => $whTo->id,
                                'updated_at' => now(),
                            ]);
                    }
                } else {
                    // Case B: Conflict (Destination vendor already has this variant).
                    $this->line("Variant ID {$vp2->product_variant_id} already exists in Destination Vendor (Product ID {$vp1->id}). Merging stocks...");

                    if ($whFrom && $whTo) {
                        // Find stocks
                        $stockFrom = DB::table('market_warehouse_stocks')
                            ->where('vendor_product_id', $vp2->id)
                            ->where('warehouse_id', $whFrom->id)
                            ->first();

                        $stockTo = DB::table('market_warehouse_stocks')
                            ->where('vendor_product_id', $vp1->id)
                            ->where('warehouse_id', $whTo->id)
                            ->first();

                        if ($stockFrom) {
                            if ($stockTo) {
                                // Add stocks together
                                DB::table('market_warehouse_stocks')
                                    ->where('id', $stockTo->id)
                                    ->update([
                                        'physical_stock' => $stockTo->physical_stock + $stockFrom->physical_stock,
                                        'online_stock' => $stockTo->online_stock + $stockFrom->online_stock,
                                        'reserved_stock' => $stockTo->reserved_stock + $stockFrom->reserved_stock,
                                        'updated_at' => now(),
                                    ]);
                                // Delete source stock record
                                DB::table('market_warehouse_stocks')->where('id', $stockFrom->id)->delete();
                            } else {
                                // Simply update warehouse and vendor product ID
                                DB::table('market_warehouse_stocks')
                                    ->where('id', $stockFrom->id)
                                    ->update([
                                        'warehouse_id' => $whTo->id,
                                        'vendor_product_id' => $vp1->id,
                                        'updated_at' => now(),
                                    ]);
                            }
                        }
                    }

                    // Update order items referencing VP2 to point to VP1
                    DB::table('market_order_items')
                        ->where('vendor_product_id', $vp2->id)
                        ->update(['vendor_product_id' => $vp1->id]);

                    // Update reviews referencing VP2 to point to VP1
                    DB::table('market_product_reviews')
                        ->where('vendor_product_id', $vp2->id)
                        ->update(['vendor_product_id' => $vp1->id]);

                    // Update warehouse transactions referencing VP2 to point to VP1 and whTo
                    $txUpdate = ['vendor_product_id' => $vp1->id];
                    if ($whFrom && $whTo) {
                        $txUpdate['warehouse_id'] = $whTo->id;
                    }
                    DB::table('market_warehouse_transactions')
                        ->where('vendor_product_id', $vp2->id)
                        ->update($txUpdate);

                    // Delete the duplicate product from source vendor
                    DB::table('market_vendor_products')->where('id', $vp2->id)->delete();
                }
            }

            // Update remaining references of vendor_id
            DB::table('market_order_items')
                ->where('vendor_id', $fromId)
                ->update(['vendor_id' => $toId]);

            DB::table('market_product_questions')
                ->where('vendor_id', $fromId)
                ->update(['vendor_id' => $toId]);

            // Update market_vendor_user pivot table entries
            $pivotUsers = DB::table('market_vendor_user')->where('vendor_id', $fromId)->get();
            foreach ($pivotUsers as $pivot) {
                DB::table('market_vendor_user')->insertOrIgnore([
                    'vendor_id' => $toId,
                    'user_id' => $pivot->user_id,
                    'created_at' => $pivot->created_at ?: now(),
                    'updated_at' => $pivot->updated_at ?: now(),
                ]);
            }
            DB::table('market_vendor_user')->where('vendor_id', $fromId)->delete();

            // Update addresses to point to destination vendor (or delete if redundant, but let's keep them)
            DB::table('market_vendor_addresses')
                ->where('vendor_id', $fromId)
                ->update(['vendor_id' => $toId]);

            // Update documents to point to destination vendor
            DB::table('market_vendor_documents')
                ->where('vendor_id', $fromId)
                ->update(['vendor_id' => $toId]);

            // Delete source warehouse
            if ($whFrom) {
                DB::table('market_warehouses')->where('id', $whFrom->id)->delete();
                $this->info("Source Warehouse deleted successfully.");
            }

            // Delete source vendor
            DB::table('market_vendors')->where('id', $fromId)->delete();
            $this->info("Source Vendor deleted successfully.");

            DB::commit();
            $this->info("Migration completed successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error occurred: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
