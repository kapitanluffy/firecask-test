<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Post;

class PostController extends BaseController
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $posts = Post::paginate($limit);

        return response()->json($posts);
    }
}
