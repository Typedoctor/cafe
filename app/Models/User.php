<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {
    use HasFactory;

    protected $fillable = ['name', 'password', 'privilege'];
    protected $hidden = ['password'];

    public function isManager() {
        return $this->privilege === 'manager';
    }
    public function isCashier() {
        return $this->privilege === 'cashier';
    }
}
