<?php

namespace Modules\Projects\App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectSetting extends Model
{
    protected $table = 'projects_settings';
    protected $primaryKey = 'id';
    protected $fillable = ['key', 'value'];

    public const DEFAULTS = [
        'projects_code_prefix' => 'PRJ-',
        'projects_code_middle' => '',
        'projects_code_suffix' => '',
        'projects_code_padding' => 4,
        'projects_code_auto' => '1',
        'projects_default_category_id' => null,
        'projects_default_status_id' => null,
        'projects_auto_assign_creator' => '1',
        'projects_default_creator_role' => 'manager',
        'projects_default_member_role' => 'viewer',
        'projects_require_client' => '0',
        'projects_require_dates' => '0',
        'projects_default_task_status_id' => null,
        'projects_progress_mode' => 'auto_tasks',
        'projects_strict_task_due_dates' => '0',
        'projects_allow_members_to_create_tasks' => '1',
        'projects_document_max_size_mb' => 20,
        'projects_document_allowed_extensions' => 'pdf,doc,docx,xls,xlsx,zip,rar,png,jpg,jpeg,webp,txt',
        'projects_document_project_quota_mb' => 500,
        'projects_document_max_files_count' => 50,
        'projects_document_categories' => 'قراردادها,طراحی و UI/UX,مستندات فنی,صورت‌جلسات,خروجی نهایی',
        'projects_notify_assigned_member' => '1',
        'projects_notify_task_assignment' => '1',
        'projects_notify_overdue_project' => '1',
        'projects_role_viewer_can_chat' => '0',
        'projects_role_viewer_can_comment' => '0',
        'projects_role_viewer_can_upload_doc' => '0',
        'projects_role_viewer_see_assigned_only' => '0',
        'projects_role_editor_see_assigned_only' => '0',
        'projects_only_show_assigned_tasks_to_members' => '0',
        'projects_role_editor_can_pin_messages' => '0',
        'projects_role_editor_can_delete_messages' => '0',
        'projects_role_editor_can_manage_phases' => '0',
        'projects_role_editor_can_delete_tasks' => '0',
    ];

    /**
     * Get a setting value by key with fallback to defaults.
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if ($setting && $setting->value !== null && $setting->value !== '') {
            return $setting->value;
        }

        if ($default !== null) {
            return $default;
        }

        return self::DEFAULTS[$key] ?? null;
    }

    /**
     * Get a boolean setting value.
     */
    public static function getBool(string $key, ?bool $default = null): bool
    {
        $val = self::get($key, $default !== null ? ($default ? '1' : '0') : null);
        return in_array((string)$val, ['1', 'true', 'on', 'yes'], true);
    }

    /**
     * Get an integer setting value.
     */
    public static function getInt(string $key, ?int $default = null): int
    {
        $val = self::get($key, $default);
        return (int) $val;
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, $value): self
    {
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        return self::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );
    }

    /**
     * Retrieve all project settings as key-value array with defaults populated.
     */
    public static function allValues(): array
    {
        $keys = array_keys(self::DEFAULTS);
        $db = self::whereIn('key', $keys)->pluck('value', 'key')->toArray();

        $result = [];
        foreach (self::DEFAULTS as $k => $def) {
            $result[$k] = (isset($db[$k]) && $db[$k] !== null && $db[$k] !== '') ? $db[$k] : $def;
        }

        return $result;
    }
}
