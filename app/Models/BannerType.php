<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerType extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'width',
        'height'
    ];
    
}