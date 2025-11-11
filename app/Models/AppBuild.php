<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppBuild extends Model
{
    protected $table = 'app_builds';
    public $incrementing = false;       // uuid
    protected $keyType = 'string';

    protected $fillable = [
        'id','user_id','prompt','model','visibility','targets',
        'status','progress','artifacts','error'
    ];

    protected $casts = [
        'targets'   => 'array',
        'artifacts' => 'array',  // ✅ IMPORTANT
    ];
}
