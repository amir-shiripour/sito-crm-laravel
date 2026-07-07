<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Services;

use Modules\ContentForge\App\Models\ContentPost;
use Modules\ContentForge\App\Models\ContentShortLink;
use Modules\ContentForge\Entities\ContentSetting;

final class ShortLinkService
{
    private const CHARSET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    public static function generate(ContentPost $post): ContentShortLink
    {
        $length = (int) ContentSetting::getValue('short_link.code_length', 6);

        do {
            $code = self::randomCode($length);
        } while (ContentShortLink::where('code', $code)->exists());

        return ContentShortLink::create([
            'post_id' => $post->id,
            'code'    => $code,
        ]);
    }

    private static function randomCode(int $length): string
    {
        $result = '';
        $max = strlen(self::CHARSET) - 1;
        for ($i = 0; $i < $length; $i++) {
            $result .= self::CHARSET[random_int(0, $max)];
        }
        return $result;
    }

    public static function getFullUrl(ContentShortLink $link): string
    {
        $prefix = ContentSetting::getValue('short_link.prefix', 's');
        return url("/{$prefix}/{$link->code}");
    }
}
