<?php

use Illuminate\Support\Facades\Route;
use Modules\ContentForge\App\Http\Controllers\ContentFrontController;
use Modules\ContentForge\App\Http\Controllers\ShortLinkController;
use Modules\ContentForge\App\Http\Controllers\User\ContentPanelController;
use Modules\ContentForge\Entities\ContentSetting;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Short Link Redirect Route ---
Route::middleware(['web'])->group(function() {
    $shortLinkPrefix = ContentSetting::getValue('short_link.prefix', 's');
    Route::get("/{$shortLinkPrefix}/{code}", [ShortLinkController::class, 'redirect'])
         ->name('content.short-link');
});

// --- Public Frontend Routes (Flat & Clean SEO Structure) ---
Route::middleware(['web'])->group(function() {
    
    // 1. Core Blog and Feed Routes
    Route::get('/blog', [ContentFrontController::class, 'archiveDefault'])->name('content.archive.default');
    Route::get('/blog/category/{categorySlug}', [ContentFrontController::class, 'categoryDefault'])->name('content.category.default');
    Route::get('/blog/tag/{tagSlug}', [ContentFrontController::class, 'tagDefault'])->name('content.tag.default');
    Route::get('/sitemap.xml', [ContentFrontController::class, 'sitemapDefault'])->name('content.sitemap.default');
    Route::get('/feed.xml', [ContentFrontController::class, 'feedDefault'])->name('content.feed.default');

    // 2. Fallback direct root route for posts, pages, and entity archives
    // Constrained to avoid matching system keywords
    Route::match(['get', 'post'], '/{slug}', [ContentFrontController::class, 'showDefault'])
         ->where('slug', '^(?!(user|admin|api|login|logout|register|storage|js|css|fonts|images|sitemap\.xml|feed\.xml|blog|s/|livewire)).*')
         ->name('content.show.default');
});

// --- Authenticated User Panel Routes (/user/content) ---
Route::group(['prefix' => 'user', 'as' => 'user.', 'middleware' => ['web', 'auth']], function () {
    Route::prefix('content')->name('content.')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [ContentPanelController::class, 'dashboard'])
             ->middleware('can:content.dashboard.view')
             ->name('dashboard');

        // Pages
        Route::get('/pages', [ContentPanelController::class, 'pages'])
             ->middleware('can:content.posts.view')
             ->name('pages.index');
        Route::get('/pages/create', [ContentPanelController::class, 'createPage'])
             ->middleware('can:content.posts.create')
             ->name('pages.create');
        Route::get('/pages/{post}/edit', [ContentPanelController::class, 'editPage'])
             ->middleware('can:content.posts.edit')
             ->name('pages.edit');

        // Blog Posts
        Route::get('/posts', [ContentPanelController::class, 'posts'])
             ->middleware('can:content.posts.view')
             ->name('posts.index');
        Route::get('/posts/create', [ContentPanelController::class, 'createPost'])
             ->middleware('can:content.posts.create')
             ->name('posts.create');
        Route::get('/posts/{post}/edit', [ContentPanelController::class, 'editPost'])
             ->middleware('can:content.posts.edit')
             ->name('posts.edit');

        // Categories
        Route::get('/categories', [ContentPanelController::class, 'categories'])
             ->middleware('can:content.categories.manage')
             ->name('categories.index');

        // Tags
        Route::get('/tags', [ContentPanelController::class, 'tags'])
             ->middleware('can:content.tags.manage')
             ->name('tags.index');

        // Comments
        Route::get('/comments', [ContentPanelController::class, 'comments'])
             ->middleware('can:content.comments.manage')
             ->name('comments.index');

        // Short Links
        Route::get('/short-links', [ContentPanelController::class, 'shortLinks'])
             ->middleware('can:content.shortlinks.manage')
             ->name('short-links.index');

        // Redirects
        Route::get('/redirects', [ContentPanelController::class, 'redirects'])
             ->middleware('can:content.redirects.manage')
             ->name('redirects.index');

        // Content Entities
        Route::get('/entities', [ContentPanelController::class, 'entities'])
             ->middleware('can:content.entities.manage')
             ->name('entities.index');

        // Module Settings
        Route::get('/settings', [ContentPanelController::class, 'settings'])
             ->middleware('can:content.settings.manage')
             ->name('settings');
    });
});
