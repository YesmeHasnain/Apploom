<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Build extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id','project_id','status','progress','artifacts','logs'];
    protected $casts = ['artifacts'=>'array'];

    protected static function booted() {
        static::creating(function ($m) { if (!$m->id) $m->id = (string) Str::uuid(); });
    }

    public function project(){ return $this->belongsTo(Project::class); }
}
