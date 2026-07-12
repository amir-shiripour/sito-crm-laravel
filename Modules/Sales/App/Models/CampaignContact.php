<?php

namespace Modules\Sales\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignContact extends Model
{
    protected $table = 'sales_campaign_contacts';

    protected $fillable = [
        'campaign_id', 'client_id', 'assigned_to', 'assigned_role', 'name', 'phone', 'email',
        'status', 'source', 'added_at'
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    // Dynamic relationship if Clients module is available
    public function client()
    {
        if (class_exists(\Modules\Clients\Entities\Client::class)) {
            return $this->belongsTo(\Modules\Clients\Entities\Client::class, 'client_id');
        }
        return null;
    }

    /**
     * Get or create the custom field ID for sales agent assignment.
     */
    public static function getOrCreateSalesAgentFieldId(): ?string
    {
        if (!class_exists(\Modules\Clients\Entities\ClientForm::class)) {
            return null;
        }

        $activeForm = \Modules\Clients\Entities\ClientForm::active();
        if (!$activeForm) {
            return null;
        }

        $schema = $activeForm->schema;
        $fields = $schema['fields'] ?? [];

        // 1. First look for the exact ID 'sales_agent_id'
        foreach ($fields as $field) {
            if (($field['id'] ?? '') === 'sales_agent_id') {
                return 'sales_agent_id';
            }
        }

        // 2. Look for any select-user-by-role field targeting the 'sales' role
        foreach ($fields as $field) {
            if (($field['type'] ?? '') === 'select-user-by-role' && ($field['role'] ?? '') === 'sales') {
                return $field['id'];
            }
        }

        // 3. Not found, create it dynamically
        $newFieldId = 'sales_agent_id';
        $existingIds = collect($fields)->pluck('id')->toArray();
        $counter = 1;
        while (in_array($newFieldId, $existingIds, true)) {
            $newFieldId = 'sales_agent_id_' . $counter++;
        }

        $fields[] = [
            'id' => $newFieldId,
            'type' => 'select-user-by-role',
            'label' => 'کارشناس فروش',
            'placeholder' => 'انتخاب کارشناس فروش...',
            'group' => 'اطلاعات هویتی',
            'width' => '1/2',
            'required' => false,
            'quick_create' => true,
            'client_auth' => false,
            'role' => 'sales',
            'multiple' => false,
            'lock_current_if_role' => true,
        ];

        $schema['fields'] = $fields;
        $activeForm->schema = $schema;
        $activeForm->save();

        return $newFieldId;
    }

    /**
     * Ensure a Client record is created and linked for this CampaignContact.
     */
    public function ensureClientCreated(?int $assignedToUserId = null)
    {
        if (!class_exists(\Modules\Clients\Entities\Client::class)) {
            return null;
        }

        $client = null;
        if ($this->client_id) {
            $client = \Modules\Clients\Entities\Client::find($this->client_id);
        }

        if (!$client) {
            // Check by phone to prevent duplicates
            $client = \Modules\Clients\Entities\Client::where('phone', $this->phone)->first();
        }

        $salesAgentFieldId = self::getOrCreateSalesAgentFieldId();

        $meta = [];
        if ($assignedToUserId && $salesAgentFieldId) {
            $meta[$salesAgentFieldId] = (string) $assignedToUserId;
        }

        if (!$client) {
            // 1. Generate Username based on Username Settings Strategy
            $strategy = \Modules\Clients\Entities\ClientSetting::getValue('username_strategy')
                ?: config('clients.username.strategy', 'email_local');
            $prefix = \Modules\Clients\Entities\ClientSetting::getValue('username_prefix', 'clt');
            $minLen = 3;

            $existsInClients = fn(string $u) =>
                \DB::table('clients')->where('username', $u)->exists();

            $candidate = null;
            switch ($strategy) {
                case 'email':
                    $candidate = (string) $this->email;
                    break;
                case 'national_code':
                    $candidate = null;
                    break;
                case 'mobile':
                    $digits = preg_replace('/\D+/', '', (string) $this->phone);
                    $candidate = $digits ?: null;
                    break;
                case 'name_increment':
                    $base = \Illuminate\Support\Str::slug((string) $this->name);
                    if (!$base || strlen($base) < $minLen) {
                        $base = \Illuminate\Support\Str::slug(
                            (string) \Illuminate\Support\Str::before((string)$this->email, '@')
                        ) ?: 'user';
                    }
                    $candidate = $base;
                    break;
                case 'prefix_increment':
                    $last = \DB::table('clients')
                        ->where('username', 'like', "{$prefix}-%")
                        ->selectRaw("MAX(CAST(SUBSTRING_INDEX(username, '-', -1) AS UNSIGNED)) as mx")
                        ->value('mx');
                    $next = (int)$last + 1;
                    $candidate = sprintf('%s-%04d', $prefix, $next);
                    break;
                case 'email_local':
                default:
                    $local = (string) \Illuminate\Support\Str::before((string)$this->email, '@');
                    $base  = \Illuminate\Support\Str::slug($local ?: (string)$this->name) ?: 'user';
                    $candidate = $base;
                    break;
            }

            if (!$candidate) {
                $candidate = 'clt_' . ($this->phone ?: \Illuminate\Support\Str::random(10));
            }

            $candidate = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $candidate);
            if (empty($candidate)) {
                $candidate = 'clt_' . \Illuminate\Support\Str::random(8);
            }

            if ($existsInClients($candidate) && $strategy !== 'email' && $strategy !== 'mobile') {
                $baseCandidate = $candidate;
                $i = 1;
                while ($existsInClients($baseCandidate . '_' . $i)) {
                    $i++;
                }
                $candidate = $baseCandidate . '_' . $i;
            }

            // 2. Client Status Management
            $defaultStatus = \Modules\Clients\Entities\ClientStatus::active()->orderBy('sort_order')->first()
                ?? \Modules\Clients\Entities\ClientStatus::first();
            $statusId = $defaultStatus ? $defaultStatus->id : null;

            // Create client
            $client = \Modules\Clients\Entities\Client::create([
                'full_name' => $this->name ?: ($this->campaign?->name ? $this->campaign->name . ' (' . substr($this->phone, -4) . ')' : 'ناشناس'),
                'phone' => $this->phone,
                'email' => $this->email,
                'username' => $candidate,
                'status_id' => $statusId,
                'meta' => $meta,
                'created_by' => auth()->id() ?? $assignedToUserId,
            ]);
        } else {
            // Client already exists, update its metadata if needed
            if ($assignedToUserId && $salesAgentFieldId) {
                $existingMeta = $client->meta ?? [];
                if (!isset($existingMeta[$salesAgentFieldId]) || $existingMeta[$salesAgentFieldId] != $assignedToUserId) {
                    $existingMeta[$salesAgentFieldId] = (string) $assignedToUserId;
                    $client->update(['meta' => $existingMeta]);
                }
            }
        }

        // Link client to user in pivot table
        if ($client && $assignedToUserId) {
            $client->users()->sync([$assignedToUserId]);
        }

        if ($client && $this->client_id !== $client->id) {
            $this->update(['client_id' => $client->id]);
        }

        // Auto create deal if setting is enabled!
        if ($client && \Modules\Sales\App\Models\SalesSetting::getValue('auto_create_deal', false)) {
            $dealExists = \Modules\Sales\App\Models\SalesDeal::where('client_id', $client->id)->exists();
            if (!$dealExists) {
                $firstStage = \Modules\Sales\App\Models\SalesPipeline::orderBy('order')->first();
                if (!$firstStage) {
                    $firstStage = \Modules\Sales\App\Models\SalesPipeline::create([
                        'name' => 'ارتباط اولیه', 
                        'color' => '#3b82f6', 
                        'order' => 1
                    ]);
                }
                
                \Modules\Sales\App\Models\SalesDeal::create([
                    'title' => 'پرونده: ' . ($this->name ?: $client->full_name),
                    'client_id' => $client->id,
                    'pipeline_stage_id' => $firstStage->id,
                    'user_id' => $assignedToUserId ?? auth()->id(),
                    'expected_revenue' => 0.0,
                    'probability' => 10,
                    'status' => 'open',
                    'stage_entered_at' => now(),
                    'lead_source' => 'campaign',
                    'created_by' => auth()->id() ?? $assignedToUserId,
                ]);
                
                $this->update(['status' => 'converted']);
            }
        }

        return $client;
    }
}
