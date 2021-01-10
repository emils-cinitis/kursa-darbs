<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model {
    use HasFactory;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'created_at',
        'user_uuid'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User', 'user_uuid');
    }
}
