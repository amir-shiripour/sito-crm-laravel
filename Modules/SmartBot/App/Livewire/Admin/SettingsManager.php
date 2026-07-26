<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\SmartBot\App\Models\BotSetting;
use Illuminate\Support\Facades\Storage;

class SettingsManager extends Component
{
    use WithFileUploads;

    public string $name = 'SmartBot';
    public string $welcome_message = '';
    public string $primary_color = '#6366f1';
    public bool $is_widget_enabled = true;
    public float $match_threshold = 0.25;
    public string $fallback_response = '';
    public int $max_suggestions = 5;
    public bool $allow_custom_typing = true;
    public bool $require_client_auth = false;
    public int $assistant_level = 1;
    public $bot_icon;
    public ?string $existing_bot_icon = null;

    public function mount()
    {
        if (!auth()->user()->can('smartbot.settings')) {
            abort(403);
        }

        $this->name = (string) BotSetting::getValue('name', 'SmartBot');
        $this->welcome_message = (string) BotSetting::getValue('welcome_message', 'سلام! من دستیار هوشمند شما هستم. چطور می‌توانم کمکتان کنم؟');
        $this->primary_color = (string) BotSetting::getValue('primary_color', '#6366f1');
        $this->is_widget_enabled = filter_var(BotSetting::getValue('is_widget_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $this->match_threshold = (float) BotSetting::getValue('match_threshold', 0.25);
        $this->fallback_response = (string) BotSetting::getValue('fallback_response', 'متأسفانه پاسخ مناسبی برای این سوال پیدا نکردم. می‌توانید سوال دیگری بپرسید یا با پشتیبانی تماس بگیرید.');
        $this->max_suggestions = (int) BotSetting::getValue('max_suggestions', 5);
        $this->allow_custom_typing = filter_var(BotSetting::getValue('allow_custom_typing', true), FILTER_VALIDATE_BOOLEAN);
        $this->require_client_auth = filter_var(BotSetting::getValue('require_client_auth', false), FILTER_VALIDATE_BOOLEAN);
        $this->assistant_level = (int) BotSetting::getValue('assistant_level', 1);
        $this->existing_bot_icon = BotSetting::getValue('bot_icon');
    }

    public function deleteIcon(): void
    {
        if ($this->existing_bot_icon) {
            Storage::disk('public')->delete($this->existing_bot_icon);
            BotSetting::setValue('bot_icon', '');
            $this->existing_bot_icon = null;
            $this->dispatch('notify', type: 'success', text: 'آیکون با موفقیت حذف شد.');
        }
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:50',
            'welcome_message' => 'required|string|max:500',
            'primary_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'is_widget_enabled' => 'required|boolean',
            'match_threshold' => 'required|numeric|between:0.0,1.0',
            'fallback_response' => 'required|string|max:500',
            'max_suggestions' => 'required|integer|between:1,10',
            'allow_custom_typing' => 'required|boolean',
            'require_client_auth' => 'required|boolean',
            'assistant_level' => 'required|integer|in:1,2',
        ];

        if ($this->bot_icon) {
            $rules['bot_icon'] = 'image|max:1024'; // Max 1MB
        }

        $this->validate($rules);

        if ($this->bot_icon) {
            // Delete previous if exists
            if ($this->existing_bot_icon) {
                Storage::disk('public')->delete($this->existing_bot_icon);
            }
            $path = $this->bot_icon->store('smartbot', 'public');
            BotSetting::setValue('bot_icon', $path);
            $this->existing_bot_icon = $path;
            $this->bot_icon = null;
        }

        BotSetting::setValue('name', $this->name);
        BotSetting::setValue('welcome_message', $this->welcome_message);
        BotSetting::setValue('primary_color', $this->primary_color);
        BotSetting::setValue('is_widget_enabled', $this->is_widget_enabled ? '1' : '0');
        BotSetting::setValue('match_threshold', (string) $this->match_threshold);
        BotSetting::setValue('fallback_response', $this->fallback_response);
        BotSetting::setValue('max_suggestions', (string) $this->max_suggestions);
        BotSetting::setValue('allow_custom_typing', $this->allow_custom_typing ? '1' : '0');
        BotSetting::setValue('require_client_auth', $this->require_client_auth ? '1' : '0');
        BotSetting::setValue('assistant_level', (string) $this->assistant_level);

        $this->dispatch('notify', type: 'success', text: 'تنظیمات با موفقیت ذخیره شدند.');
    }

    public function render()
    {
        return view('smartbot::livewire.admin.settings-manager')
            ->layout('layouts.user', ['title' => 'تنظیمات دستیار هوشمند']);
    }
}
