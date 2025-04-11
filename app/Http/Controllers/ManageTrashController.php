<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ManageTrashController extends Controller
{
    public function index()
    {
        return view('cashier.manage_trash');
    }
}
