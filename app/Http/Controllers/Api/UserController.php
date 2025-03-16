<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Получить список всех пользователей.
     */
    public function index()
    {
        $users = User::all();

        return response()->json($users);
    }

    /**
     * Получить данные текущего авторизованного пользователя.
     */
    public function showMe()
    {
        return response()->json(Auth::user());
    }

    /**
     * Получить данные конкретного пользователя.
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    /**
     * Обновить данные пользователя.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:15',
            'avatar' => 'nullable|string',
            'password' => 'nullable|string|min:8',
        ]);

        if ($request->has('name')) $user->name = $validated['name'];
        if ($request->has('email')) $user->email = $validated['email'];
        if ($request->has('phone')) $user->phone = $validated['phone'];
        if ($request->has('avatar')) $user->avatar = $validated['avatar'];
        if ($request->has('password')) $user->password = Hash::make($validated['password']);

        $user->save();

        return response()->json($user);
    }

    /**
     * Удалить пользователя.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }
}
