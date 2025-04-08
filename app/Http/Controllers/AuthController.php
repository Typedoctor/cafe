<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller {
    // Show login form
    public function showLogin() {
        return view('auth.login');
    }

    // Process login
    public function login(Request $request) {
        $credentials = $request->validate([
            'name' => 'required',
            'password' => 'required'
        ]);

        // Check if the username exists
        $user = User::where('name', $request->name)->first();

        if (!$user) {
            return back()->withErrors(['error' => 'Username does not exist']);
        }

        // Attempt login only if the user exists
        if (!Auth::attempt(['name' => $request->name, 'password' => $request->password])) {
            return back()->withErrors(['error' => 'Incorrect password']);
        }

        // Redirect user based on role
        if ($user->isManager()) {
            return redirect()->route('manager.dashboard');
        } elseif ($user->isCashier()) {
            return redirect()->route('cashier.dashboard');
        }

        return back()->withErrors(['error' => 'Invalid credentials']);
    }

    // Logout function
    public function logout() {
        Auth::logout();
        return redirect()->route('login');
    }
}
