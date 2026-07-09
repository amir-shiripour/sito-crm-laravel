<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Modules\ContentForge\App\Models\ContentPost;
use Modules\ContentForge\App\Enums\PostType;
use Illuminate\Http\Request;

final class ContentPanelController extends Controller
{
    public function dashboard()
    {
        return view('contentforge::user.dashboard');
    }

    public function posts()
    {
        return view('contentforge::user.posts.index');
    }

    public function pages()
    {
        return view('contentforge::user.pages.index');
    }

    public function createPost()
    {
        return view('contentforge::user.posts.create');
    }

    public function createPage()
    {
        return view('contentforge::user.pages.create');
    }

    public function editPost(ContentPost $post)
    {
        abort_if($post->type !== PostType::Post, 404);
        return view('contentforge::user.posts.edit', compact('post'));
    }

    public function editPage(ContentPost $post)
    {
        abort_if($post->type !== PostType::Page, 404);
        return view('contentforge::user.pages.edit', compact('post'));
    }

    public function categories()
    {
        return view('contentforge::user.categories.index');
    }

    public function tags()
    {
        return view('contentforge::user.tags.index');
    }

    public function comments()
    {
        return view('contentforge::user.comments.index');
    }

    public function shortLinks()
    {
        return view('contentforge::user.short_links.index');
    }

    public function redirects()
    {
        return view('contentforge::user.redirects.index');
    }

    public function entities()
    {
        return view('contentforge::user.entities.index');
    }

    public function settings()
    {
        return view('contentforge::user.settings');
    }
}
