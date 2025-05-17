<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\Auditable;

class User extends Authenticatable {
    use HasFactory, Auditable;

    protected $fillable = ['name', 'password', 'privilege', 'is_active'];
    protected $hidden = ['password'];

    public function isManager() {
        return $this->privilege === 'manager';
    }
    
    public function isCashier() {
        return $this->privilege === 'cashier';
    }
    
    public function isActive() {
        return $this->is_active === 1;
    }
}