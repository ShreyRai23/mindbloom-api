<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth('api')->user();
        $profile = $user->role === 'child' ? $user->childProfile : $user->parentProfile;
        return response()->json(['user' => $user, 'profile' => $profile]);
    }

    public function update(Request $request)
    {
        $user = auth('api')->user();
        $request->validate([
            'name'         => 'sometimes|string|max:255',
            'avatar_emoji' => 'sometimes|string|max:10',
            'hero_name'    => 'sometimes|string|max:50',
            'age'          => 'sometimes|integer|min:5|max:20',
            'grade'        => 'sometimes|string|max:20',
            'profile_image'=> 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->has('name')) $user->update(['name' => $request->name]);
        if ($request->has('avatar_emoji')) $user->update(['avatar_emoji' => $request->avatar_emoji]);

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('avatars', 'public');
            $user->update(['profile_image_path' => $path]);
        }

        if ($user->role === 'child') {
            $child = $user->childProfile;
            $child->update($request->only(['hero_name', 'age', 'grade', 'avatar_emoji']));
        }

        return response()->json(['message' => 'Profile updated! ✨', 'user' => $user->fresh()]);
    }
}
