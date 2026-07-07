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

// --- Public Frontend Routes (Multi-Tenant Entities) ---
Route::middleware(['web'])->group(function() {
    Route::prefix('{entitySlug}')->group(function () {
        Route::get('/', [ContentFrontController::class, 'archive'])->name('content.archive');
        Route::get('/category/{categorySlug}', [ContentFrontController::class, 'category'])->name('content.category');
        Route::get('/tag/{tagSlug}', [ContentFrontController::class, 'tag'])->name('content.tag');
        Route::get('/sitemap.xml', [ContentFrontController::class, 'sitemap'])->name('content.sitemap');
        Route::get('/feed.xml', [ContentFrontController::class, 'feed'])->name('content.feed');
        Route::match(['get', 'post'], '/{slug}', [ContentFrontController::class, 'show'])->name('content.show');
    });
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
