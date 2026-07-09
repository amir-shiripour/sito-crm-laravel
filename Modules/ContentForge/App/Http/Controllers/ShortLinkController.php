<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\ContentForge\App\Models\ContentShortLink;
use Modules\ContentForge\App\Enums\PostStatus;
use Illuminate\Http\RedirectResponse;

final class ShortLinkController extends Controller
{
    public function redirect(string $code): RedirectResponse
    {
        // 1. Check content_short_links table
        $shortLink = ContentShortLink::where('code', $code)
            ->orWhere('custom_code', $code)
            ->with(['post.entity'])
            ->first();

        if ($shortLink && $shortLink->post) {
            $post = $shortLink->post;
            
            // Check expiry
            if ($shortLink->expires_at && $shortLink->expires_at->isPast()) {
                abort(404, 'این لینک کوتاه منقضی شده است.');
            }

            if ($post->status === PostStatus::Published) {
                // Increment click count
                $shortLink->increment('click_count');
                
                // Redirect to full post URL
                $entitySlug = $post->entity->slug ?? 'main';
                $url = url("/{$entitySlug}/{$post->slug}");
                return redirect()->away($url);
            }
        }

        // 2. Fallback to content_redirects table (custom 301/302 redirects)
        $redirect = \Modules\ContentForge\App\Models\ContentRedirect::where('from_url', $code)
            ->orWhere('from_url', '/' . $code)
            ->orWhere('from_url', url()->current())
            ->first();

        if ($redirect) {
            return redirect($redirect->to_url, (int) $redirect->type);
        }

        abort(404, 'لینک کوتاه یافت نشد.');
    }
}
