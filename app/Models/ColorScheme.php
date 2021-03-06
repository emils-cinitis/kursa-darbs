<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ColorScheme extends Model {
    use HasFactory;

    protected $fillable = [
        'title',
        'background_color',
        'text_color',
        'cta_color',
        'user_uuid'
    ];

    protected $hidden = [
        'user_uuid',
        'created_at',
        'updated_at'
    ];

    public function banners() {
        return $this->hasMany('App\Models\Banner');
    }
    
}