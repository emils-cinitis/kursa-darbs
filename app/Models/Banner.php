<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'main_text',
        'created_by'
    ];

    public function createdUser() {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    public function updatedUser() {
        return $this->belongsTo('App\Models\User', 'updated_by');
    }
}
