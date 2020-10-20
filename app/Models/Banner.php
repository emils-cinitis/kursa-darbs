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
        'main_text',
        'sub_text',
        'created_by'
    ];

    protected $hidden = [
        'created_by'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    public function deleteAllInfo() {
        $this->delete();
    }
}
