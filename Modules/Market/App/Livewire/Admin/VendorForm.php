<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Modules\Market\Entities\Vendor;
use Modules\Market\Entities\VendorDocument;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;


class VendorForm extends Component
{
    public ?Vendor $vendor = null;

    // اطلاعات پایه
    public $user_id = '';
    public $store_name = '';
    public $slug = '';
    public $support_phone = '';
    public $description = '';
    public $status = 'pending';
    public $kyc_status = 'pending';
    public $commission_rate = '';

    // اطلاعات حقوقی (KYC)
    public $legal_type = 'real';
    public $national_code = '';
    public $economic_code = '';

    // اطلاعات مالی
    public $shaba_number = '';
    public $account_owner_name = '';
    public $bank_name = '';

    // متغیرهای مودال رد مدرک
    public $rejectingDocId = null;
    public $rejectionReason = '';

    public $kyc_rejection_reason = '';

    public function mount(?Vendor $vendor = null)
    {
        $this->vendor = $vendor ?? new Vendor();

        if ($this->vendor->exists) {
            $this->user_id = $this->vendor->user_id;
            $this->store_name = $this->vendor->store_name;
            $this->slug = $this->vendor->slug;
            $this->support_phone = $this->vendor->support_phone;
            $this->description = $this->vendor->description;
            $this->status = $this->vendor->status;
            $this->kyc_status = $this->vendor->kyc_status;
            $this->commission_rate = $this->vendor->commission_rate;

            $this->legal_type = $this->vendor->legal_type ?? 'real';
            $this->national_code = $this->vendor->national_code;
            $this->economic_code = $this->vendor->economic_code;

            $this->shaba_number = $this->vendor->shaba_number;
            $this->account_owner_name = $this->vendor->account_owner_name;
            $this->bank_name = $this->vendor->bank_name;
            $this->kyc_rejection_reason = $this->vendor->kyc_rejection_reason;
        }
    }

    public function save()
    {
        $this->validate([
            'user_id' => 'required|exists:users,id|unique:market_vendors,user_id,' . ($this->vendor->id ?? 'NULL'),
            'store_name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:market_vendors,slug,' . ($this->vendor->id ?? 'NULL'),
            'status' => 'required|in:pending,active,suspended',
            'kyc_status' => 'required|in:pending,approved,rejected',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'legal_type' => 'required|in:real,legal',
            'national_code' => 'nullable|string|max:20',
            'shaba_number' => 'nullable|string|size:24', // IR حذف شده و فقط ۲۴ رقم شبا
        ]);

        $this->vendor->fill([
            'user_id' => $this->user_id,
            'store_name' => $this->store_name,
            'slug' => $this->slug ?: Str::slug($this->store_name),
            'support_phone' => $this->support_phone,
            'description' => $this->description,
            'status' => $this->status,
            'kyc_status' => $this->kyc_status,
            'commission_rate' => $this->commission_rate ?: null,
            'legal_type' => $this->legal_type,
            'national_code' => $this->national_code,
            'economic_code' => $this->economic_code,
            'shaba_number' => $this->shaba_number,
            'account_owner_name' => $this->account_owner_name,
            'bank_name' => $this->bank_name,
            'kyc_rejection_reason' => $this->kyc_status === 'rejected' ? $this->kyc_rejection_reason : null,
        ])->save();

        // اختصاص نقش به کاربر
        $user = User::find($this->user_id);
        if ($user && !$user->hasRole('vendor')) {
            $user->assignRole('vendor');
        }

        $this->dispatch('notify', type: 'success', text: 'اطلاعات فروشگاه با موفقیت ذخیره شد.');
        \Illuminate\Support\Facades\Cache::forget('vendor_edit_lock_' . $this->vendor->id);
        return redirect()->route('user.market.vendors.index');
    }

    // --- متدهای بررسی مدارک ---

    public function approveDocument($docId)
    {
        $doc = VendorDocument::find($docId);
        if ($doc && $doc->vendor_id === $this->vendor->id) {
            $doc->update(['status' => 'approved', 'rejection_reason' => null]);
            $this->dispatch('notify', type: 'success', text: 'مدرک تایید شد.');
        }
    }

    public function promptRejectDocument($docId)
    {
        $this->rejectingDocId = $docId;
        $this->rejectionReason = '';
    }

    public function confirmRejectDocument()
    {
        $this->validate(['rejectionReason' => 'required|string|min:5']);

        $doc = VendorDocument::find($this->rejectingDocId);
        if ($doc && $doc->vendor_id === $this->vendor->id) {
            $doc->update(['status' => 'rejected', 'rejection_reason' => $this->rejectionReason]);
            $this->dispatch('notify', type: 'success', text: 'مدرک رد شد و دلیل آن برای فروشنده ثبت گردید.');
        }

        $this->rejectingDocId = null;
        $this->rejectionReason = '';
    }

    public function cancelReject()
    {
        $this->rejectingDocId = null;
    }

    public function render()
    {
        return view('market::livewire.admin.vendor-form', [
            'users' => User::select('id', 'name', 'mobile')->latest()->get(),
            // لود کردن آدرس‌ها و مدارک فقط در صورت ویرایش
            'addresses' => $this->vendor->exists ? $this->vendor->addresses : collect(),
            'documents' => $this->vendor->exists ? $this->vendor->documents : collect(),
        ]);
    }

    public function cancelReview()
    {
        if ($this->vendor->exists) {
            Cache::forget('vendor_edit_lock_' . $this->vendor->id);
        }
        return redirect()->route('user.market.vendors.index');
    }
}
