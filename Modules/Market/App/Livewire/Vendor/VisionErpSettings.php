<?php

namespace Modules\Market\App\Livewire\Vendor;

use Exception;
use Livewire\Component;
use Modules\Market\App\Services\Vision\VisionCatalogSyncService;
use Modules\Market\App\Services\Vision\VisionSoapClient;
use Modules\Market\App\Services\Vision\VisionStockSyncService;
use Modules\Market\Entities\Vendor;
use Modules\Market\Entities\VendorVisionSetting;
use Modules\Market\Entities\Warehouse;

class VisionErpSettings extends Component
{
    public ?int $vendorId = null;
    public bool $isAdminMode = false;

    // Form inputs
    public bool $is_active = false;
    public string $server_url = '';
    public string $ws_username = '';
    public string $ws_password = '';
    public string $admin_pass = '';
    public string $default_visitor_code = '';
    public string $default_warehouse_code = '';
    public string $default_customer_code = '';
    public bool $auto_sync_stock = false;
    public bool $auto_dispatch_order = false;

    // Layer 1: Scope, Category Whitelist & Price Mapping
    public array $selected_groups = [];
    public array $selected_subgroups = [];
    public string $price_field = 'Pricef1';
    public ?string $compare_price_field = 'PriceF';
    public array $selected_warehouses = [];
    public string $initial_product_status = 'draft';
    public ?array $cached_metadata = null;
    public string $groupSearch = '';
    public bool $fetchingMetadata = false;

    // Status
    public ?string $last_sync_at = null;
    public ?string $last_sync_status = null;
    public ?string $last_error_message = null;

    // UI feedback
    public ?string $connectionMessage = null;
    public ?bool $connectionSuccess = null;
    public ?string $syncMessage = null;
    public ?bool $syncSuccess = null;
    public bool $testingConnection = false;
    public bool $syncingStock = false;

    public function mount(?int $vendorId = null, bool $isAdminMode = false)
    {
        $this->isAdminMode = $isAdminMode;

        if ($vendorId) {
            $this->vendorId = $vendorId;
        } else {
            // Find current user's vendor, or default vendor in single-store mode
            $user = auth()->user();
            $vendor = Vendor::where('user_id', $user?->id)->first() ?? Vendor::first();
            $this->vendorId = $vendor?->id;
        }

        $this->loadSettings();
    }

    public function loadSettings(): void
    {
        if (!$this->vendorId) {
            return;
        }

        $setting = VendorVisionSetting::where('vendor_id', $this->vendorId)->first();

        if ($setting) {
            $this->is_active = (bool)$setting->is_active;
            $this->server_url = (string)$setting->server_url;
            $this->ws_username = (string)$setting->ws_username;
            $this->ws_password = (string)$setting->ws_password;
            $this->admin_pass = (string)$setting->admin_pass;
            $this->default_visitor_code = (string)$setting->default_visitor_code;
            $this->default_warehouse_code = (string)$setting->default_warehouse_code;
            $this->default_customer_code = (string)$setting->default_customer_code;
            $this->auto_sync_stock = (bool)$setting->auto_sync_stock;
            $this->auto_dispatch_order = (bool)$setting->auto_dispatch_order;

            // Layer 1 mappings
            $this->selected_groups = (array)($setting->selected_groups ?: []);
            $this->selected_subgroups = (array)($setting->selected_subgroups ?: []);
            $this->price_field = (string)($setting->price_field ?: 'Pricef1');
            $this->compare_price_field = $setting->compare_price_field ?: 'PriceF';
            $this->selected_warehouses = (array)($setting->selected_warehouses ?: []);
            $this->initial_product_status = (string)($setting->initial_product_status ?: 'draft');
            $this->cached_metadata = $setting->cached_metadata;

            $this->last_sync_at = $setting->last_sync_at?->format('Y/m/d H:i');
            $this->last_sync_status = $setting->last_sync_status;
            $this->last_error_message = $setting->last_error_message;
        }
    }

    public function saveSettings(): void
    {
        if (!$this->vendorId) {
            session()->flash('error', 'هیچ فروشنده‌ای انتخاب نشده است.');
            return;
        }

        $this->validate([
            'server_url' => 'nullable|string|max:255',
            'ws_username' => 'nullable|string|max:100',
            'default_visitor_code' => 'nullable|string|max:50',
            'default_warehouse_code' => 'nullable|string|max:50',
            'default_customer_code' => 'nullable|string|max:50',
            'price_field' => 'nullable|string|max:30',
            'compare_price_field' => 'nullable|string|max:30',
            'initial_product_status' => 'required|in:draft,published',
        ]);

        $data = [
            'is_active' => $this->is_active,
            'server_url' => $this->server_url,
            'ws_username' => $this->ws_username,
            'default_visitor_code' => $this->default_visitor_code,
            'default_warehouse_code' => $this->default_warehouse_code,
            'default_customer_code' => $this->default_customer_code,
            'auto_sync_stock' => $this->auto_sync_stock,
            'auto_dispatch_order' => $this->auto_dispatch_order,
            'selected_groups' => array_values(array_unique($this->selected_groups)),
            'selected_subgroups' => array_values(array_unique($this->selected_subgroups)),
            'price_field' => $this->price_field ?: 'Pricef1',
            'compare_price_field' => $this->compare_price_field ?: null,
            'selected_warehouses' => $this->selected_warehouses,
            'initial_product_status' => $this->initial_product_status ?: 'draft',
        ];

        if (!empty($this->ws_password)) {
            $data['ws_password'] = $this->ws_password;
        }
        if (!empty($this->admin_pass)) {
            $data['admin_pass'] = $this->admin_pass;
        }

        VendorVisionSetting::updateOrCreate(
            ['vendor_id' => $this->vendorId],
            $data
        );

        session()->flash('success', 'تنظیمات و نگاشت‌های وب‌سرویس ویژن با موفقیت ذخیره شد.');
        $this->loadSettings();
    }

    /**
     * Fetch Layer 1 discovery metadata from Vision ERP (categories, subgroups, total count)
     */
    public function fetchDiscoveryMetadata(): void
    {
        $this->syncMessage = null;
        $this->syncSuccess = null;

        $setting = VendorVisionSetting::where('vendor_id', $this->vendorId)->first();
        if (!$setting) {
            $this->syncSuccess = false;
            $this->syncMessage = 'ابتدا فرم اتصال را ذخیره کنید.';
            return;
        }

        $catalogService = new VisionCatalogSyncService($setting);
        $res = $catalogService->fetchDiscoveryMetadata();

        $this->syncSuccess = $res['success'];
        $this->syncMessage = $res['message'];

        if ($res['success']) {
            $this->cached_metadata = $res['data'];
            $this->loadSettings();
        }
    }

    /**
     * Toggle a category group selection
     */
    public function toggleGroup(string $groupCode): void
    {
        $current = $this->selected_groups;
        if (in_array($groupCode, $current, true)) {
            $this->selected_groups = array_values(array_diff($current, [$groupCode]));
        } else {
            $current[] = $groupCode;
            $this->selected_groups = array_values(array_unique($current));
        }
        $this->saveSettings();
    }

    /**
     * Select all discovered groups
     */
    public function selectAllGroups(): void
    {
        $allGroups = $this->cached_metadata['groups'] ?? [];
        $codes = [];
        foreach ($allGroups as $g) {
            if (!empty($g['CodeG'])) {
                $codes[] = (string)$g['CodeG'];
            }
        }
        $this->selected_groups = $codes;
        $this->saveSettings();
    }

    /**
     * Deselect all groups
     */
    public function deselectAllGroups(): void
    {
        $this->selected_groups = [];
        $this->selected_subgroups = [];
        $this->saveSettings();
    }

    /**
     * Toggle a subgroup selection
     */
    public function toggleSubgroup(string $subCode, string $parentGroupCode): void
    {
        $current = $this->selected_subgroups;
        if (in_array($subCode, $current, true)) {
            $this->selected_subgroups = array_values(array_diff($current, [$subCode]));
        } else {
            $current[] = $subCode;
            $this->selected_subgroups = array_values(array_unique($current));
            // Ensure parent group is also active
            if (!in_array($parentGroupCode, $this->selected_groups, true)) {
                $this->selected_groups[] = $parentGroupCode;
            }
        }
        $this->saveSettings();
    }

    /**
     * Get filtered groups for UI search
     */
    public function getFilteredGroupsProperty(): array
    {
        $groups = $this->cached_metadata['groups'] ?? [];
        if (empty($this->groupSearch)) {
            return $groups;
        }

        $term = trim(mb_strtolower($this->groupSearch));
        return array_values(array_filter($groups, function ($g) use ($term) {
            $name = mb_strtolower($g['NameG'] ?? '');
            $code = mb_strtolower((string)($g['CodeG'] ?? ''));
            return str_contains($name, $term) || str_contains($code, $term);
        }));
    }

    /**
     * Sync catalog goods strictly respecting Layer 1 active groups
     */
    public function syncSelectedCatalog(): void
    {
        $this->syncMessage = null;
        $this->syncSuccess = null;

        $setting = VendorVisionSetting::where('vendor_id', $this->vendorId)->first();
        if (!$setting || !$setting->is_active) {
            $this->syncSuccess = false;
            $this->syncMessage = 'اتصال وب‌سرویس ویژن فعال نیست یا ذخیره نشده است.';
            return;
        }

        $catalogService = new VisionCatalogSyncService($setting);
        $res = $catalogService->syncBaseData(3, 500, 1);

        $this->syncSuccess = $res['success'];
        $this->syncMessage = $res['message'];
        $this->loadSettings();
    }

    public function testConnection(): void
    {
        $this->connectionMessage = null;
        $this->connectionSuccess = null;

        if (empty($this->server_url) || empty($this->ws_username) || empty($this->ws_password)) {
            $this->connectionSuccess = false;
            $this->connectionMessage = 'لطفاً آدرس سرور، نام کاربری و رمز عبور را وارد کنید.';
            return;
        }

        // Create temporary in-memory model for test
        $tempSetting = new VendorVisionSetting([
            'vendor_id' => $this->vendorId ?: 1,
            'server_url' => $this->server_url,
            'ws_username' => $this->ws_username,
            'ws_password' => $this->ws_password,
            'admin_pass' => $this->admin_pass,
        ]);

        $client = new VisionSoapClient($tempSetting);
        $result = $client->testConnection();

        $this->connectionSuccess = $result['success'];
        $this->connectionMessage = $result['message'];
    }

    public function syncStockNow(): void
    {
        $this->syncMessage = null;
        $this->syncSuccess = null;

        $setting = VendorVisionSetting::where('vendor_id', $this->vendorId)->first();
        if (!$setting || !$setting->is_active) {
            $this->syncSuccess = false;
            $this->syncMessage = 'ابتدا تنظیمات ویژن را ذخیره و وضعیت اتصال را فعال کنید.';
            return;
        }

        $stockService = new VisionStockSyncService($setting);
        $res = $stockService->syncStock();

        $this->syncSuccess = $res['success'];
        $this->syncMessage = $res['message'];
        $this->loadSettings();
    }

    public function syncBaseCatalog(int $type): void
    {
        $setting = VendorVisionSetting::where('vendor_id', $this->vendorId)->first();
        if (!$setting || !$setting->is_active) {
            $this->syncSuccess = false;
            $this->syncMessage = 'اتصال ویژن فعال نیست.';
            return;
        }

        $catalogService = new VisionCatalogSyncService($setting);
        $res = $catalogService->syncBaseData($type);

        $this->syncSuccess = $res['success'];
        $this->syncMessage = $res['message'];
    }

    public function render()
    {
        $warehouses = Warehouse::where('vendor_id', $this->vendorId)->get();

        return view('market::livewire.vendor.vision-erp-settings', [
            'warehouses' => $warehouses,
        ]);
    }
}
