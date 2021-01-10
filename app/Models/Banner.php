<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model {
    use HasFactory;

    protected $primaryKey = 'uuid';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'name',
        'link_url',
        'main_text',
        'sub_text',
        'image',
        'call_to_action',
        'color_scheme_id',
        'template_id',
        'created_by'
    ];

    protected $hidden = [
        'created_by'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    public function deleteAllInfo() {
        if(!empty($this->image)) {
            if(file_exists(public_path() . $this->image)){
                unlink(public_path() . $this->image);
            }
            if(file_exists(public_path() . '/banners/' . $this->uuid ))
            rmdir(public_path() . '/banners/' . $this->uuid);
        }
        $this->delete();
    }

    public function template() {
        return $this->belongsTo('App\Models\Template');
    }

    public function colorScheme() {
        return $this->belongsTo('App\Models\ColorScheme');
    }
}
