<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Livewire\Admin;

use Livewire\Component;
use Modules\ContentForge\Entities\ContentSetting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ContentSettings extends Component
{
    use AuthorizesRequests;

    public array $settings = [];

    protected function rules(): array
    {
        return [
            'settings.general.posts_per_page'    => 'required|integer|min:1|max:100',
            'settings.general.default_theme_key' => 'required|string|max:50',
            'settings.general.enable_comments'   => 'required|in:true,false',
            'settings.general.enable_tags'       => 'required|in:true,false',
            'settings.general.reading_time_wpm'  => 'required|integer|min:50|max:500',
            'settings.seo.auto_generate_description' => 'required|in:true,false',
            'settings.seo.description_length'    => 'required|integer|min:100|max:250',
            'settings.seo.auto_schema_markup'    => 'required|in:true,false',
            'settings.short_link.enabled'        => 'required|in:true,false',
            'settings.short_link.prefix'         => 'required|string|alpha|min:1|max:10',
            'settings.short_link.code_length'    => 'required|integer|min:4|max:10',
        ];
    }

    protected $validationAttributes = [
        'settings.general.posts_per_page'    => 'تعداد پست در هر صفحه',
        'settings.general.default_theme_key' => 'قالب پیش‌فرض',
        'settings.short_link.prefix'         => 'پیشوند لینک کوتاه',
    ];

    public function mount(): void
    {
        $this->authorize('content.settings.manage');

        foreach (ContentSetting::DEFAULTS as $section => $keys) {
            foreach ($keys as $key => $default) {
                $this->settings[$section][$key] = ContentSetting::getValue("{$section}.{$key}", $default);
            }
        }
    }

    public function save(): void
    {
        $this->validate();

        $originalPrefix = ContentSetting::getValue('short_link.prefix', 's');
        $newPrefix = $this->settings['short_link']['prefix'];

        foreach ($this->settings as $section => $keys) {
            foreach ($keys as $key => $value) {
                // Ensure value is converted to string for storage compatibility
                $stringValue = is_bool($value) ? ($value ? 'true' : 'false') : (string)$value;
                ContentSetting::setValue("{$section}.{$key}", $stringValue);
            }
        }

        session()->flash('success', 'تنظیمات با موفقیت ذخیره شد.');

        if ($originalPrefix !== $newPrefix) {
            session()->flash('warning', 'توجه: پیشوند لینک کوتاه تغییر کرد. جهت اعمال صحیح، لطفاً کش بهینه‌سازی سیستم را پاک کنید.');
        }
    }

    public function render()
    {
        return view('contentforge::livewire.admin.content-settings')
            ->layout('layouts.user');
    }
}
