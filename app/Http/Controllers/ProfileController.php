<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Processar avatar
        $avatarPath = $user->avatar;
        if ($request->has('remove_avatar') && $request->input('remove_avatar')) {
            // Remover avatar existente
            if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
            }
            $avatarPath = null;
        } elseif ($request->hasFile('avatar')) {
            // Remover avatar anterior se existir
            if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
            }
            $avatar = $request->file('avatar');
            $avatarPath = 'avatars/' . Str::uuid() . '.' . $avatar->getClientOriginalExtension();
            Storage::disk('public')->makeDirectory('avatars');
            $avatar->storeAs('', $avatarPath, 'public');
        } elseif ($request->filled('avatar_base64')) {
            // Remover avatar anterior se existir
            if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
            }
            // Processar imagem da webcam (base64)
            $imageData = $request->input('avatar_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                $imageType = $matches[1];
                $imageData = base64_decode(substr($imageData, strpos($imageData, ',') + 1));
                $avatarPath = 'avatars/' . Str::uuid() . '.' . $imageType;
                Storage::disk('public')->makeDirectory('avatars');
                Storage::disk('public')->put($avatarPath, $imageData);
            }
        }

        // Atualizar dados do usuário (apenas se foram enviados)
        $validated = $request->validated();
        
        // Se apenas avatar está sendo atualizado, não atualizar name e email
        $isAvatarOnly = $request->has('avatar') || $request->has('avatar_base64') || $request->has('remove_avatar');
        
        if (!$isAvatarOnly) {
            $user->fill($validated);
        }
        
        // Atualizar avatar se foi modificado
        if (isset($avatarPath)) {
            $user->avatar = $avatarPath;
        }

        if (!$isAvatarOnly && $user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
