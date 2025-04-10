<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ManageUserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('manager.manage_users', compact('users'));
    }

    public function store(Request $request)
    {
        \Log::info('Store method called', $request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'privilege' => 'required|in:cashier,manager',
        ]);

        User::create([
            'name' => $request->name,
            'password' => bcrypt($request->password),
            'privilege' => $request->privilege,
        ]);

        return redirect()->route('manage_users.index')->with('success', 'User added successfully');
    }

    public function update(Request $request, $id)
    {
        \Log::info('Update method called', ['id' => $id, 'input' => $request->all()]);
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:6',
            'privilege' => 'required|in:cashier,manager',
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->privilege = $request->privilege;
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
        $user->save();

        return redirect()->route('manage_users.index')->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        \Log::info('Destroy method called', ['id' => $id]);
        $user = User::find($id);
        if (!$user) {
            return redirect()->route('manage_users.index')->with('error', 'User not found');
        }
        $user->delete();
        return redirect()->route('manage_users.index')->with('success', 'User deleted successfully');
    }
}