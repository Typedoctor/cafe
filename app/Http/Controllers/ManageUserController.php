<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ManageUserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::all();


        return view('manager.manage_users', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'privilege' => 'required|in:cashier,manager',
            'is_active' => 'required|boolean'
        ]);

        User::create([
            'name' => $request->name,
            'password' => bcrypt($request->password),
            'privilege' => $request->privilege,
            'is_active' => $request->is_active
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
            'is_active' => 'required|boolean'
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->privilege = $request->privilege;
        $user->is_active = $request->is_active;
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
        $user->save();

        return redirect()->route('manage_users.index')->with('success', 'User updated successfully');
    }

    public function updateStatus(Request $request, $id)
    {
        \Log::info('Update status method called', ['id' => $id, 'input' => $request->all()]);
        
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $user = User::findOrFail($id);
        $user->is_active = $request->is_active;
        $user->save();
        
        $status = $user->is_active ? 'activated' : 'deactivated';
        return redirect()->route('manage_users.index')->with('success', "User {$status} successfully");
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