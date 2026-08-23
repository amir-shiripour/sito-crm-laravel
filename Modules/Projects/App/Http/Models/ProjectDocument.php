<?php

namespace Modules\Projects\App\Http\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ProjectDocument extends Model
{
    use SoftDeletes;

    protected $table = 'projects_documents';

    protected $fillable = [
        'project_id',
        'type',
        'category',
        'title',
        'description',
        'file_path',
        'file_original_name',
        'file_mime',
        'file_size',
        'link_url',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    public function isLink(): bool
    {
        return $this->type === 'link';
    }

    public function getFileUrlAttribute(): ?string
    {
        if ($this->type !== 'file' || !$this->file_path) {
            return null;
        }
        return Storage::disk('public')->exists($this->file_path)
            ? Storage::disk('public')->url($this->file_path)
            : null;
    }

    public function getHumanFileSizeAttribute(): ?string
    {
        if (!$this->file_size) {
            return null;
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 1) . ' ' . $units[$i];
    }

    public static function getAllowedExtensions(): array
    {
        $raw = ProjectSetting::get('projects_document_allowed_extensions', 'pdf,doc,docx,xls,xlsx,zip,rar,png,jpg,jpeg,webp,txt');

        if (empty($raw)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', str_replace('.', '', $raw)))));
    }

    public static function getCategories(): array
    {
        $raw = ProjectSetting::get('projects_document_categories', 'قراردادها,طراحی و UI/UX,مستندات فنی,صورت‌جلسات,خروجی نهایی');

        if (empty($raw)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    public static function getMaxFileSizeMb(): int
    {
        return max(1, ProjectSetting::getInt('projects_document_max_size_mb', 20));
    }

    public static function getProjectQuotaMb(): int
    {
        return max(10, ProjectSetting::getInt('projects_document_project_quota_mb', 500));
    }

    public static function getMaxFilesCount(): int
    {
        return max(1, ProjectSetting::getInt('projects_document_max_files_count', 50));
    }
}
