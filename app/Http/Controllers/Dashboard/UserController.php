<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('users.index'); // Create this view
    }

    public function create()
    {
        return view('admin.users.create'); // Create this view
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'inn' => 'required|integer',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);

        return redirect()->route('admin', [app()->getlocale()])->with('success', 'User created successfully.');
    }

    public function edit($locale, $id)
    {

        $user = User::findOrFail($id); // Retrieve the user or throw a 404 if not found
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $locale, User $user)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        'inn' => 'required|integer',
        'password' => 'nullable|string|min:8|confirmed',
    ]);

    if (!empty($validated['password'])) {
        $validated['password'] = Hash::make($validated['password']);
    } else {
        unset($validated['password']); // Do not update the password if it's not provided
    }

    $user->update($validated);

    return redirect()->route('admin', [app()->getlocale()])->with('success', 'User updated successfully.');
}

    public function destroy(Request $request)
    {
        $userId = $request->input('id');
        $user = User::findOrFail($userId);

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }


}
