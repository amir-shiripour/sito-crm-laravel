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

        session()->flash('success', 'تنظیمات اتصال به وب‌سرویس ویژن با موفقیت ذخیره شد.');
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
