<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'is_encrypted',
        'encryption_type',
        'encryption_key',
    ];

    public function sender()
{
    return $this->belongsTo(User::class, 'sender_id');
}

public function receiver()
{
    return $this->belongsTo(User::class, 'receiver_id');
}


//     // Encrypt the message before saving
//     public function setMessageAttribute($value)
//     {
//         $this->attributes['message'] = $this->is_encrypted ? Crypt::encrypt($value) : $value;
//     }

//     // Decrypt the message when retrieved
//     public function getMessageAttribute($value)
// {
//     try {
//         return $this->is_encrypted ? decrypt($value) : $value;
//     } catch (\Exception $e) {
//         \Log::error('Decryption failed for message ID ' . $this->id . ': ' . $e->getMessage());
//         return 'Invalid encrypted message';
//     }
// }
}
