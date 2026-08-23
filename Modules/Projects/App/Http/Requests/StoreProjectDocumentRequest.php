<?php

namespace Modules\Projects\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Projects\App\Http\Models\ProjectDocument;

class StoreProjectDocumentRequest extends FormRequest
{
    public function rules(): array
    {
        $maxMb = ProjectDocument::getMaxFileSizeMb();
        $maxKb = max(1024, $maxMb * 1024);

        $cleanExts = implode(',', ProjectDocument::getAllowedExtensions());

        $fileRules = ['required_if:type,file', 'nullable', 'file', "max:{$maxKb}"];
        if (!empty($cleanExts)) {
            $fileRules[] = "mimes:{$cleanExts}";
        }

        return [
            'type' => 'required|in:file,link',
            'category' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'file' => $fileRules,
            'link_url' => 'required_if:type,link|nullable|url|max:2000',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $project = $this->route('project');
            if (!$project) {
                return;
            }

            $maxFiles = ProjectDocument::getMaxFilesCount();
            if ($maxFiles > 0 && $project->documents()->count() >= $maxFiles) {
                $validator->errors()->add('file', "سقف مجاز تعداد اسناد این پروژه ({$maxFiles} سند) تکمیل شده است.");
            }

            if ($this->hasFile('file')) {
                $quotaMb = ProjectDocument::getProjectQuotaMb();
                $quotaBytes = $quotaMb * 1024 * 1024;
                $currentTotalBytes = (int) $project->documents()->where('type', 'file')->sum('file_size');
                $newFileSize = $this->file('file')->getSize();

                if ($currentTotalBytes + $newFileSize > $quotaBytes) {
                    $validator->errors()->add('file', "سقف فضای ذخیره‌سازی اسناد این پروژه ({$quotaMb} مگابایت) تکمیل شده است.");
                }
            }
        });
    }

    public function authorize(): bool
    {
        return true;
    }
}
