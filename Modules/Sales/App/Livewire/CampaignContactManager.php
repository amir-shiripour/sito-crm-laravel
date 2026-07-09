<?php

declare(strict_types=1);

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Modules\Sales\App\Models\Campaign;
use Modules\Sales\App\Models\CampaignContact;
use Modules\Clients\Entities\Client;
use Illuminate\Support\Facades\Log;

class CampaignContactManager extends Component
{
    public Campaign $campaign;
    
    // Single contact form
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    
    // Bulk paste textarea
    public string $bulkNumbers = '';
    
    public string $search = '';

    // Bulk selection and assignment properties
    public array $selectedContactIds = [];
    public ?string $assigneeValue = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'email' => 'nullable|email|max:255',
    ];

    private function convertToEnglishDigits(string $string): string
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $string = str_replace($persian, $english, $string);
        return str_replace($arabic, $english, $string);
    }

    public function mount(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function addSingle()
    {
        $this->phone = $this->convertToEnglishDigits(trim($this->phone));
        $this->validate();

        // Check if phone already exists in this campaign
        $exists = CampaignContact::where('campaign_id', $this->campaign->id)
            ->where('phone', $this->phone)
            ->exists();

        if ($exists) {
            $this->addError('phone', 'این شماره تماس قبلاً در این کمپین ثبت شده است.');
            return;
        }

        // Check if phone belongs to an existing client to soft-link them
        $client = null;
        if (class_exists(Client::class)) {
            $client = Client::where('phone', $this->phone)->first();
        }

        CampaignContact::create([
            'campaign_id' => $this->campaign->id,
            'client_id' => $client ? $client->id : null,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'status' => 'pending',
            'source' => 'manual',
        ]);

        $this->reset(['name', 'phone', 'email']);
        $this->dispatch('notify', message: 'مخاطب با موفقیت به کمپین اضافه شد.', type: 'success');
    }

    public function addBulk()
    {
        $this->validate([
            'bulkNumbers' => 'required|string',
        ]);

        // Split only by newlines to parse line-by-line (which may contain names and numbers)
        $lines = explode("\n", $this->bulkNumbers);
        $addedCount = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Convert Persian/Arabic digits to English digits
            $englishLine = $this->convertToEnglishDigits($line);

            // Match phone number using regex (look for sequence of 8 to 15 digits, optionally with separators)
            if (preg_match('/(\+?[0-9][0-9\s\-\(\)]{6,14}[0-9])/', $englishLine, $phoneMatches)) {
                $rawPhone = $phoneMatches[0];
                $cleanPhone = preg_replace('/[^\d\+]/', '', $rawPhone);
                
                // Name is everything else on the line
                $namePart = trim(str_replace($rawPhone, '', $line));
                // Strip separators like commas, hyphens, colons from start/end
                $namePart = trim($namePart, " \t\n\r\0\x0B,-:;|");

                if (empty($namePart)) {
                    $clientName = $this->campaign->name;
                } else {
                    $clientName = $namePart;
                }
            } else {
                // Fallback: if no clear long digit sequence, try cleaning the whole string
                $cleanPhone = preg_replace('/[^\d\+]/', '', $englishLine);
                $clientName = $this->campaign->name;
            }

            if (strlen($cleanPhone) < 8) {
                continue;
            }

            // Check if already in campaign
            $exists = CampaignContact::where('campaign_id', $this->campaign->id)
                ->where('phone', $cleanPhone)
                ->exists();

            if ($exists) {
                continue;
            }

            // Check if exists as a Client to link
            $client = null;
            if (class_exists(Client::class)) {
                $client = Client::where('phone', $cleanPhone)->first();
                if ($client) {
                    $clientName = $client->full_name;
                }
            }

            CampaignContact::create([
                'campaign_id' => $this->campaign->id,
                'client_id' => $client ? $client->id : null,
                'name' => $clientName . ' (' . substr($cleanPhone, -4) . ')',
                'phone' => $cleanPhone,
                'status' => 'pending',
                'source' => 'import',
            ]);

            $addedCount++;
        }

        $this->reset('bulkNumbers');
        $this->dispatch('notify', message: "تعداد {$addedCount} شماره با موفقیت به کمپین اضافه شد.", type: 'success');
    }

    public function updateContactStatus(int $contactId, string $status)
    {
        $contact = CampaignContact::where('campaign_id', $this->campaign->id)->findOrFail($contactId);
        $allowedStatuses = ['pending', 'contacted', 'responded', 'converted', 'lost'];
        if (in_array($status, $allowedStatuses)) {
            $contact->update(['status' => $status]);
            $this->dispatch('notify', message: 'وضعیت مخاطب به‌روزرسانی شد.', type: 'success');
        }
    }

    public function convertToDeal(int $contactId)
    {
        $contact = CampaignContact::where('campaign_id', $this->campaign->id)->findOrFail($contactId);

        if ($contact->status === 'converted') {
            $this->dispatch('notify', message: 'این مخاطب قبلاً به پرونده فروش تبدیل شده است.', type: 'warning');
            return;
        }

        $clientId = $contact->client_id;
        if (!$clientId && class_exists(Client::class)) {
            $client = Client::where('phone', $contact->phone)->first();
            if (!$client) {
                $clientName = $contact->name ?: $this->campaign->name . ' (' . substr($contact->phone, -4) . ')';
                $username = 'client_' . ($contact->phone ?: \Illuminate\Support\Str::random(10));
                
                $counter = 1;
                while (Client::where('username', $username)->exists()) {
                    $username = 'client_' . ($contact->phone ?: \Illuminate\Support\Str::random(10)) . '_' . $counter;
                    $counter++;
                }

                $client = Client::create([
                    'full_name' => $clientName,
                    'phone' => $contact->phone,
                    'email' => $contact->email,
                    'username' => $username,
                    'created_by' => auth()->id(),
                ]);
            }
            $clientId = $client->id;
            $contact->update(['client_id' => $clientId]);
        }

        $firstStage = \Modules\Sales\App\Models\SalesPipeline::orderBy('order')->first();
        if (!$firstStage) {
            $firstStage = \Modules\Sales\App\Models\SalesPipeline::create([
                'name' => 'ارتباط اولیه', 
                'color' => '#3b82f6', 
                'order' => 1
            ]);
        }

        \Modules\Sales\App\Models\SalesDeal::create([
            'title' => 'پرونده: ' . $contact->name,
            'client_id' => $clientId,
            'pipeline_stage_id' => $firstStage->id,
            'user_id' => auth()->id(),
            'expected_revenue' => 0.0,
            'probability' => 10,
            'status' => 'open',
            'stage_entered_at' => now(),
            'lead_source' => 'campaign',
            'created_by' => auth()->id(),
        ]);

        $contact->update(['status' => 'converted']);
        $this->dispatch('notify', message: 'مخاطب با موفقیت به پرونده فروش تبدیل شد.', type: 'success');
    }

    public function bulkAssignContacts()
    {
        $this->validate([
            'assigneeValue' => 'required|string',
            'selectedContactIds' => 'required|array|min:1',
        ]);

        $assigned_to = null;
        $assigned_role = null;

        if ($this->assigneeValue && str_contains($this->assigneeValue, ':')) {
            [$type, $value] = explode(':', $this->assigneeValue, 2);
            if ($type === 'user') {
                $assigned_to = $value;
            } elseif ($type === 'role') {
                $assigned_role = $value;
            }
        }

        CampaignContact::where('campaign_id', $this->campaign->id)
            ->whereIn('id', $this->selectedContactIds)
            ->update([
                'assigned_to' => $assigned_to,
                'assigned_role' => $assigned_role,
            ]);

        $this->selectedContactIds = [];
        $this->assigneeValue = null;
        $this->dispatch('notify', message: 'مخاطبین با موفقیت به کارشناس/نقش منتخب تخصیص یافتند.', type: 'success');
    }

    public function toggleSelectAllContacts($contactIds)
    {
        if (count($this->selectedContactIds) === count($contactIds)) {
            $this->selectedContactIds = [];
        } else {
            $this->selectedContactIds = $contactIds;
        }
    }

    public function deleteContact(int $id)
    {
        $contact = CampaignContact::where('campaign_id', $this->campaign->id)->find($id);
        if ($contact) {
            $contact->delete();
            $this->dispatch('notify', message: 'مخاطب از کمپین حذف شد.', type: 'success');
        }
    }

    public function render()
    {
        $query = CampaignContact::where('campaign_id', $this->campaign->id)->with('assignee');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        $contacts = $query->orderBy('id', 'desc')->paginate(10);
        $salesAgents = \App\Models\User::orderBy('name')->get();
        $roles = class_exists(\Spatie\Permission\Models\Role::class) ? \Spatie\Permission\Models\Role::all() : collect();

        return view('sales::livewire.campaign-contact-manager', [
            'contacts' => $contacts,
            'salesAgents' => $salesAgents,
            'roles' => $roles,
            'contactIdsOnPage' => $contacts->pluck('id')->toArray(),
        ]);
    }
}
