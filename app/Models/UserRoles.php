<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRoles extends Model {
    use HasFactory;

    /**
     * Indicates that model isn't timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function getBasicRole(){
        return 1;
    }
}
