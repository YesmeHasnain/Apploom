<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserIntegration extends Model
{
    protected $fillable = ['user_id','api_name','token_enc','refresh_token_enc','connected_at'];
}
