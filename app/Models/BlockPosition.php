<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockPosition extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'width',
        'height',
        'left_offset',
        'top_offset',
        'z_index',
        'template_id',
        'banner_type_id',
        'block_type_id'
    ];

    public function template() {
        return $this->belongsTo('App\Models\Template');
    }

    public function banner_type() {
        return $this->belongsTo('App\Models\BannerType');
    }

    public function block_type() {
        return $this->belongsTo('App\Models\BlockType');
    }
    
}