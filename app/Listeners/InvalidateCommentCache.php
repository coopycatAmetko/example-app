<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

class InvalidateCommentCache
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CommentCreated $event): void
    {
        $paren_comment = $event->comment->parent;
        if ($paren_comment) {
            $pages_list_key = 'coment_children_pages_' . $paren_comment->getKey();

            $pages = Cache::get($pages_list_key, []);
            foreach ($pages as $page) {
                $cache_key = 'coment_children_' . $paren_comment->getKey() . '_page_' . $page;
                Cache::forget($cache_key);
            }

            Cache::forget($pages_list_key);
        }
    }
}
