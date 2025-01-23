<?php

namespace App\Http\Controllers;
use App\Models\Message;

use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request){

        $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);

        $messages = Message::with('sender')
            ->where(function ($query) use ($request) {
                $query->where('sender_id', auth()->id())
                    ->where('receiver_id', $request->receiver_id);
            })
            ->orWhere(function ($query) use ($request) {
                $query->where('sender_id', $request->receiver_id)
                    ->where('receiver_id', auth()->id());
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function store(Request $request){

        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
            'is_encrypted' => 'required|boolean',
        ]);

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'is_encrypted' => $request->is_encrypted,
        ]);

        $message->load('sender');
        return response()->json($message);
    }
}