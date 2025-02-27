<?php

namespace App\Http\Controllers;
use App\Models\Message;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\Random;

class MessageController extends Controller
{
    public function index(Request $request)
{
    try {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);

        $messages = Message::with(['sender:id,name', 'receiver:id,name'])
            ->where(function ($query) use ($request) {
                $query->where('sender_id', auth()->id())
                    ->where('receiver_id', $request->receiver_id);
            })
            ->orWhere(function ($query) use ($request) {
                $query->where('sender_id', $request->receiver_id)
                    ->where('receiver_id', auth()->id());
            })
            ->get();

        return response()->json($messages);
    } catch (\Exception $e) {
        \Log::error('Error fetching messages: ' . $e->getMessage());
        return response()->json(['error' => 'Unable to fetch messages.'], 500);
    }
}

public function store(Request $request)
{
    $request->validate([
        'receiver_id' => 'required|exists:users,id',
        'message' => 'required|string', 
        'is_encrypted' => 'required|boolean',
        'encryption_type' => 'nullable|string|in:none,atbash,aes,advancedAtbash',
        'encryption_key' => 'nullable|string',
    ]);

    try {
        \Log::info('Incoming Store Request:', $request->all());

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'is_encrypted' => $request->is_encrypted,
            'encryption_type' => $request->encryption_type,
            'encryption_key' => $request->encryption_key,
        ]);

        $message->load('sender');
        \Log::info('Created Message:', $message->toArray());

        return response()->json($message);
    } catch (\Exception $e) {
        \Log::error('Error Saving Message: ' . $e->getMessage());
        return response()->json(['error' => 'Unable to save the message.'], 500);
    }
}


private function padKey($key)
{
    $validLengths = [16, 24, 32];
    while (!in_array(strlen($key), $validLengths)) {
        $key .= $key;
        if (strlen($key) > 32) {
            $key = substr($key, 0, 32);
            break;
        }
    }
    return $key;
}

public function validateKey(Request $request)
{
    $request->validate([
        'message_id' => 'required|exists:messages,id',
        'decryption_key' => 'required|string',
    ]);

    try {
        $message = Message::findOrFail($request->message_id);

        if ($message->encryption_type === 'aes') {
            // AES Decryption Logic
            $paddedKey = $this->padKey($request->decryption_key);

            $decodedMessage = base64_decode($message->message);

            $iv = substr($decodedMessage, 0, 16);
            $ciphertext = substr($decodedMessage, 16);

            $aes = new \phpseclib3\Crypt\AES('cbc');
            $aes->setKey($paddedKey);
            $aes->setIV($iv);
            $decryptedMessage = $aes->decrypt($ciphertext);

            if ($decryptedMessage === false) {
                return response()->json(['is_valid' => false, 'error' => 'Decryption failed.'], 400);
            }

            return response()->json(['is_valid' => true, 'decrypted_message' => $decryptedMessage]);
        } elseif ($message->encryption_type === 'atbash') {
            // Validate the decryption key for Atbash
            if ($message->encryption_key !== $request->decryption_key) {
                return response()->json(['is_valid' => false, 'error' => 'Invalid decryption key.'], 400);
            }

            // Decrypt the message using Atbash (symmetric decryption)
            $decryptedMessage = $this->atbashDecrypt($message->message);
            return response()->json(['is_valid' => true, 'decrypted_message' => $decryptedMessage]);
        }elseif ($message->encryption_type === 'advancedAtbash') {
            if ($message->encryption_key !== $request->decryption_key) {
                return response()->json(['is_valid' => false, 'error' => 'Invalid decryption key.'], 400);
            }

            // Advanced Atbash Decryption Logic
            $decryptedMessage = $this->advancedAtbashDecrypt($message->message);
            return response()->json(['is_valid' => true, 'decrypted_message' => $decryptedMessage]);
        }

        // For plaintext or unsupported encryption types
        return response()->json(['is_valid' => true, 'decrypted_message' => $message->message]);
    } catch (\Exception $e) {
        \Log::error('Decryption failed for message ID ' . $request->message_id . ': ' . $e->getMessage());
        return response()->json(['is_valid' => false, 'error' => 'Decryption failed.'], 500);
    }
}


private function atbashDecrypt($text){
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $reversedAlphabet = 'ZYXWVUTSRQPONMLKJIHGFEDCBA';

    return collect(str_split($text))->map(function ($char) use ($alphabet, $reversedAlphabet) {
        $isUpperCase = strtoupper($char) === $char;
        $baseChar = strtoupper($char);
        $index = strpos($alphabet, $baseChar);

        if ($index === false) {
            return $char;
        }

        $reversedChar = $reversedAlphabet[$index];
        return $isUpperCase ? $reversedChar : strtolower($reversedChar);
    })->implode('');
}

private function advancedAtbashDecrypt($text)
{
    $alphabet = 'ZYXWVUTSRQPONMLKJIHGFEDCBA~`/?><.,:;|}{][+_)(*&^%$#@!9876543210';
    $reversedAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+[]{}|;:,.<>?/`~0123456789';

    $decrypted = '';

    foreach (str_split($text) as $char) {
        if (ctype_alpha($char)) {
            $isUpperCase = ctype_upper($char);
            $baseChar = strtoupper($char);

            // Reverse using Atbash
            $index = strpos($alphabet, $baseChar);
            $reverseChar = $reversedAlphabet[$index];

            // Shift one position forward
            $forwardIndex = (strpos($alphabet, $reverseChar) + 1) % strlen($alphabet);
            $finalChar = $alphabet[$forwardIndex];

            $decrypted .= $isUpperCase ? $finalChar : strtolower($finalChar);
        } else {
            $decrypted .= $char;
        }
    }

    return $decrypted;
}
public function destroy($id)
{
    try {
        $message = Message::where('id', $id)
            ->where('sender_id', auth()->id())
            ->first();

        if (!$message) {
            return response()->json(['error' => 'Message not found or unauthorized.'], 404);
        }

        $message->delete();

        return response()->json(['success' => 'Message deleted successfully.']);
    } catch (\Exception $e) {
        \Log::error('Error deleting message: ' . $e->getMessage());
        return response()->json(['error' => 'Unable to delete the message.'], 500);
    }
}

}