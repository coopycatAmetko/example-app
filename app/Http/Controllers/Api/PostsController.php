<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostsController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->paginate(25);
        return response()->json($posts, 200);
    }

    public function show(Post $post)
    {
        return response()->json($post, 200);
    }
}