<?php

namespace Modules\Accounting\App\Services;

class NumberToWords
{
    private static $units = ['', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه'];
    private static $tens = ['', 'ده', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'];
    private static $hundreds = ['', 'صد', 'دویست', 'سیصد', 'چهارصد', 'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'];
    private static $teens = ['ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده'];
    private static $magnitudes = ['', 'هزار', 'میلیون', 'میلیارد', 'تریلیون'];

    public static function convert($number)
    {
        if (!is_numeric($number)) {
            return '';
        }

        $number = (string) $number;
        $number = str_replace(',', '', $number); // Remove commas if any

        if ($number == 0) {
            return 'صفر';
        }

        $parts = explode('.', $number);
        $integerPart = $parts[0];
        $decimalPart = isset($parts[1]) ? $parts[1] : '';

        $words = self::convertInteger($integerPart);

        if (!empty($decimalPart)) {
            $words .= ' ممیز ' . self::convertInteger($decimalPart);
        }

        return trim($words);
    }

    private static function convertInteger($number)
    {
        $number = ltrim($number, '0'); // Remove leading zeros
        if (empty($number)) {
            return '';
        }

        $length = strlen($number);
        $words = [];
        $magnitudeIndex = 0;

        // Pad the number with leading zeros to be a multiple of 3
        $padding = $length % 3;
        if ($padding > 0) {
            $number = str_pad($number, $length + (3 - $padding), '0', STR_PAD_LEFT);
            $length = strlen($number);
        }

        for ($i = $length; $i > 0; $i -= 3) {
            $chunk = (int) substr($number, $i - 3, 3);

            if ($chunk == 0) {
                $magnitudeIndex++;
                continue;
            }

            $chunkWords = [];
            $hundred = floor($chunk / 100);
            $remainder = $chunk % 100;

            if ($hundred > 0) {
                $chunkWords[] = self::$hundreds[$hundred];
            }

            if ($remainder > 0) {
                if ($remainder < 10) {
                    $chunkWords[] = self::$units[$remainder];
                } elseif ($remainder >= 10 && $remainder < 20) {
                    $chunkWords[] = self::$teens[$remainder - 10];
                } else {
                    $ten = floor($remainder / 10);
                    $unit = $remainder % 10;
                    $chunkWords[] = self::$tens[$ten];
                    if ($unit > 0) {
                        $chunkWords[] = self::$units[$unit];
                    }
                }
            }

            $currentWords = implode(' و ', $chunkWords);

            if ($magnitudeIndex > 0) {
                $currentWords .= ' ' . self::$magnitudes[$magnitudeIndex];
            }

            array_unshift($words, $currentWords);
            $magnitudeIndex++;
        }

        return implode(' و ', $words);
    }
}
