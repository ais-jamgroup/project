<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',          // The content of the post
        'encrypted',        // Whether the post is encrypted
        'encryption_key',   // The encryption key (optional)
    ];

    public function user()
{
    return $this->belongsTo(User::class);
}

}
