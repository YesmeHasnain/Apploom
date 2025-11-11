<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiRequirement extends Model
{
    protected $fillable = ['project_id','api_name','auth_type','status'];
    public function project(){ return $this->belongsTo(Project::class); }
}
