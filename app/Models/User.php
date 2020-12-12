<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject {

    use HasFactory, Notifiable;

    protected $primaryKey = 'uuid';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'uuid',
        'user_role'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'uuid',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function banners() {
        return $this->hasMany('App\Models\Banner', 'created_by');
    }

    public function colorSchemes() {
        return $this->hasMany('App\Models\ColorScheme', 'user_uuid');
    }

    public function colorSchemesSelect() {
        return $this->hasMany('App\Models\ColorScheme', 'user_uuid')->select(array('id', 'title'));
    }

    public function templatesSelect() {
        return $this->hasMany('App\Models\Template', 'user_uuid')->select(array('id', 'title'));
    }

    public function templates() {
        return $this->hasMany('App\Models\Template', 'user_uuid');
    }

    public function deleteAllBanners() {
        $banners = $this->banners;
        foreach($banners as $banner) {
            $banner->deleteAllInfo();
        }
    }

    public function getJWTIdentifier() {
        return $this->getKey();
    }
    public function getJWTCustomClaims() {
        return [];
    }
}
