<?php

namespace App\Services;

use App\Models\GoogleCalendarImportedEvent;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class GoogleCalendarImportService
{
    /**
     * ایمپورت فایل‌های .ics یا .ical یا فایل زیپ شامل فایلهای تقویم
     */
    public function importFile(UploadedFile $file, ?int $userId = null): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename  = $file->getClientOriginalName();
        $importedCount = 0;
        $updatedCount  = 0;

        if ($extension === 'zip') {
            return $this->importZipFile($file, $userId);
        }

        $content = file_get_contents($file->getRealPath());
        if (empty($content)) {
            return ['success' => false, 'message' => 'فایل انتخابی خالی است.'];
        }

        $res = $this->parseAndStoreIcalContent($content, $filename, $userId);
        return [
            'success'  => true,
            'imported' => $res['created'],
            'updated'  => $res['updated'],
            'total'    => $res['created'] + $res['updated'],
        ];
    }

    /**
     * پردازش فایل زیپ و ایمپورت تمام تک‌فایل‌های .ics داخل آن
     */
    protected function importZipFile(UploadedFile $file, ?int $userId): array
    {
        if (!class_exists(\ZipArchive::class)) {
            return [
                'success' => false, 
                'message' => 'افزونه php_zip روی این سرور فعال نیست. لطفاً فایل استخراج‌شده .ics یا .ical را به‌صورت مستقیم آپلود کنید.'
            ];
        }

        $zip = new \ZipArchive();
        if ($zip->open($file->getRealPath()) !== true) {
            return ['success' => false, 'message' => 'امکان باز کردن فایل زیپ وجود ندارد.'];
        }

        $createdTotal = 0;
        $updatedTotal = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, ['ics', 'ical'])) {
                $stream = $zip->getStream($filename);
                if ($stream) {
                    $content = stream_get_contents($stream);
                    fclose($stream);
                    if ($content) {
                        $res = $this->parseAndStoreIcalContent($content, basename($filename), $userId);
                        $createdTotal += $res['created'];
                        $updatedTotal += $res['updated'];
                    }
                }
            }
        }

        $zip->close();

        return [
            'success'  => true,
            'imported' => $createdTotal,
            'updated'  => $updatedTotal,
            'total'    => $createdTotal + $updatedTotal,
        ];
    }

    /**
     * پارس کردن رشته محتوای استاندارد RFC 5545 iCalendar
     */
    public function parseAndStoreIcalContent(string $content, string $filename, ?int $userId = null): array
    {
        // 1. Unfold lines (RFC 5545 specification)
        $unfolded = preg_replace("/\r?\n[ \t]/", "", $content);
        $lines    = preg_split("/\r?\n/", $unfolded);

        $created = 0;
        $updated = 0;
        $inEvent = false;
        $current = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if ($line === 'BEGIN:VEVENT') {
                $inEvent = true;
                $current = [];
                continue;
            }

            if ($line === 'END:VEVENT') {
                if ($inEvent && !empty($current)) {
                    $saved = $this->saveEventBlock($current, $filename, $userId);
                    if ($saved === 'created') $created++;
                    if ($saved === 'updated') $updated++;
                }
                $inEvent = false;
                $current = [];
                continue;
            }

            if ($inEvent) {
                $pos = strpos($line, ':');
                if ($pos !== false) {
                    $property = substr($line, 0, $pos);
                    $value    = substr($line, $pos + 1);

                    // جدا کردن نام پارامتر در صورت وجود (مثلاً DTSTART;VALUE=DATE)
                    $propParts = explode(';', $property);
                    $propName  = strtoupper($propParts[0]);

                    $current[$propName] = [
                        'raw_prop' => $property,
                        'value'    => $value,
                    ];
                }
            }
        }

        return ['created' => $created, 'updated' => $updated];
    }

    /**
     * ذخیره یک بلاک VEVENT در دیتابیس
     */
    protected function saveEventBlock(array $block, string $filename, ?int $userId = null): ?string
    {
        $uid = isset($block['UID']) ? trim($block['UID']['value']) : null;
        if (!$uid) {
            $uid = uniqid('ical_');
        }

        $summary     = isset($block['SUMMARY']) ? $this->cleanIcalText($block['SUMMARY']['value']) : 'بدون عنوان';
        $description = isset($block['DESCRIPTION']) ? $this->cleanIcalText($block['DESCRIPTION']['value']) : null;
        $location    = isset($block['LOCATION']) ? $this->cleanIcalText($block['LOCATION']['value']) : null;

        $dtstartRaw = $block['DTSTART'] ?? null;
        if (!$dtstartRaw) {
            return null; // رویداد بدون زمان شروع معتبر نیست
        }

        $startDate = $this->parseIcalDate($dtstartRaw['value'], $dtstartRaw['raw_prop']);
        if (!$startDate) {
            return null;
        }

        $endDate = null;
        if (isset($block['DTEND'])) {
            $endDate = $this->parseIcalDate($block['DTEND']['value'], $block['DTEND']['raw_prop']);
        }

        $isAllDay = (strpos($dtstartRaw['raw_prop'], 'VALUE=DATE') !== false) || (strlen(trim($dtstartRaw['value'])) === 8);

        $record = GoogleCalendarImportedEvent::where('uid', $uid)->first();
        $isNew  = !$record;

        GoogleCalendarImportedEvent::updateOrCreate(
            ['uid' => $uid],
            [
                'summary'         => $summary,
                'description'     => $description,
                'location'        => $location,
                'start_at'        => $startDate,
                'end_at'          => $endDate,
                'is_all_day'      => $isAllDay,
                'source_filename' => $filename,
                'imported_by'     => $userId ?? auth()->id(),
            ]
        );

        return $isNew ? 'created' : 'updated';
    }

    /**
     * پارس کردن تاریخ‌های استاندارد iCal
     */
    protected function parseIcalDate(string $val, string $prop): ?Carbon
    {
        $val = trim($val);
        if (empty($val)) return null;

        try {
            // حالت ۱: تاریخ بدون زمان (YYYYMMDD)
            if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $val, $m)) {
                return Carbon::createFromDate((int)$m[1], (int)$m[2], (int)$m[3], 'Asia/Tehran')->startOfDay();
            }

            // حالت ۲: تاریخ + زمان UTC (YYYYMMDDTHHISZ)
            if (preg_match('/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})(\d{2})Z$/i', $val, $m)) {
                return Carbon::createFromTimestampUtc(
                    gmmktime((int)$m[4], (int)$m[5], (int)$m[6], (int)$m[2], (int)$m[3], (int)$m[1])
                )->setTimezone(config('app.timezone', 'Asia/Tehran'));
            }

            // حالت ۳: تاریخ + زمان محلی (YYYYMMDDTHHIS)
            if (preg_match('/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})(\d{2})$/i', $val, $m)) {
                return Carbon::create((int)$m[1], (int)$m[2], (int)$m[3], (int)$m[4], (int)$m[5], (int)$m[6], config('app.timezone', 'Asia/Tehran'));
            }

            return Carbon::parse($val)->setTimezone(config('app.timezone', 'Asia/Tehran'));
        } catch (\Throwable $e) {
            Log::error("iCal date parse error for [{$val}]: " . $e->getMessage());
            return null;
        }
    }

    /**
     * تمیزکاری متون iCal (آن‌اسکیپ کردن کاراکترها)
     */
    protected function cleanIcalText(string $text): string
    {
        $text = str_replace(['\\n', '\\N'], "\n", $text);
        $text = str_replace(['\\,', '\\;', '\\\\'], [',', ';', '\\'], $text);
        return trim($text);
    }

    /**
     * پاکسازی تمام رویدادهای ایمپورت شده
     */
    public function clearAllImportedEvents(): int
    {
        return GoogleCalendarImportedEvent::query()->delete();
    }

    /**
     * آمار رویدادهای ایمپورت شده
     */
    public function getStats(): array
    {
        $total = GoogleCalendarImportedEvent::count();
        $files = GoogleCalendarImportedEvent::distinct('source_filename')->pluck('source_filename')->filter()->values()->toArray();
        $latest = GoogleCalendarImportedEvent::latest('updated_at')->first();

        return [
            'total_count' => $total,
            'filenames'   => $files,
            'last_import' => $latest ? $latest->updated_at->format('Y/m/d H:i') : null,
        ];
    }
}
