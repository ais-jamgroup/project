<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User; 

class FreedomWellController extends Controller
{
    public function index()
{
    // Fetch posts with user information
    $posts = Post::with('user:id,name')->get();

    return response()->json($posts);
}

public function store(Request $request)
{
   
    $request->validate([
        'content' => 'required|string',
        'encrypted' => 'required|boolean',
        'encryption_key' => 'nullable|string',
    ]);

    try {
        $post = Post::create([
            'content' => $request->content,
            'encrypted' => $request->encrypted,
            'encryption_key' => $request->encryption_key,
            'user_id' => auth()->id(), // Associate the logged-in user with the post
        ]);

        \Log::info($post);

        return response()->json($post, 201);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Unable to save the post.'], 500);
    }
}

}
