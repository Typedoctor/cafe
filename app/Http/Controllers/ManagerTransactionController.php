<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ManagerTransactionController extends Controller
{
    public function index() {
        return view('manager.transactions');
    }
}
