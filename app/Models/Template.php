<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model {
    use HasFactory;

    protected $fillable = [
        'title',
        'user_uuid'
    ];

    public function blocks() {
        return $this->hasMany('App\Models\BlockPosition', 'template_id');
    }
}