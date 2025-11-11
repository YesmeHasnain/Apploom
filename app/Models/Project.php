<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id','user_id','name','type','status','live_url','project_json'];

    protected static function booted() {
        static::creating(function ($m) { if (!$m->id) $m->id = (string) Str::uuid(); });
    }

    public function builds() { return $this->hasMany(Build::class); }
    public function apiRequirements() { return $this->hasMany(ApiRequirement::class); }
}
