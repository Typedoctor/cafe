<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CashierTransactionController extends Controller
{
    public function index()
    {
        return view('cashier.cashier_transaction');
    }
}
