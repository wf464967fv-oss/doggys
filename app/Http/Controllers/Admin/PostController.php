<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAction;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Guides: list and unpublish (content is written in the seeders for now).
 */
class PostController extends Controller
{
    public function index(): View
    {
        return view('admin.posts.index', [
            'posts' => Post::query()->orderBy('translation_key')->orderBy('locale')->paginate(50),
        ]);
    }

    public function unpublish(Post $post): RedirectResponse
    {
        $post->forceFill(['published_at' => null])->save();
        AdminAction::record('post.unpublish', $post, $post->locale.' / '.$post->slug);

        return back()->with('status', __('admin.posts.unpublished'));
    }
}
