<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppDatabase extends Model
{
    protected $table = 'app_databases';

    protected $fillable = [
        'user_id','name','engine','username','password','host','port','metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
