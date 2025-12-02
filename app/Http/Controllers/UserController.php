<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Module;
use App\Models\Vehicle;
use App\Models\UserModulePermission;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', User::class);

        $users = User::orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        Gate::authorize('create', User::class);

        $modules = Module::where('slug', '!=', 'users')->orderBy('name')->get();
        $vehicles = Vehicle::where('active', true)->orderBy('name')->get();

        // Carregar módulos padrão para condutores
        $defaultDriverModulesJson = SystemSetting::get('driver_default_modules', '[]');
        $defaultDriverModulesData = json_decode($defaultDriverModulesJson, true) ?? [];
        
        $defaultDriverModules = [];
        $defaultDriverModulePermissions = [];
        
        foreach ($defaultDriverModulesData as $moduleId => $permissions) {
            $defaultDriverModules[] = $moduleId;
            $defaultDriverModulePermissions[$moduleId] = $permissions;
        }

        return view('users.create', compact('modules', 'vehicles', 'defaultDriverModules', 'defaultDriverModulePermissions'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_full' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,condutor',
            'active' => 'boolean',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'avatar_base64' => 'nullable|string',
            'modules' => 'nullable|array',
            'modules.*.can_view' => 'boolean',
            'modules.*.can_create' => 'boolean',
            'modules.*.can_edit' => 'boolean',
            'modules.*.can_delete' => 'boolean',
            'vehicles' => 'nullable|array',
            'vehicles.*' => 'exists:vehicles,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // Processar avatar
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarPath = 'avatars/' . Str::uuid() . '.' . $avatar->getClientOriginalExtension();
            Storage::disk('public')->makeDirectory('avatars');
            $avatar->storeAs('', $avatarPath, 'public');
        } elseif ($request->filled('avatar_base64')) {
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

        $user = User::create([
            'name' => $validated['name'],
            'name_full' => $validated['name_full'] ?? null,
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'active' => $request->has('active'),
            'avatar' => $avatarPath,
        ]);

        // Salvar permissões de módulos (apenas se não for admin)
        if ($validated['role'] !== 'admin' && $request->has('modules')) {
            foreach ($request->input('modules', []) as $moduleId => $permissions) {
                if (isset($permissions['enabled']) && $permissions['enabled']) {
                    UserModulePermission::create([
                        'user_id' => $user->id,
                        'module_id' => $moduleId,
                        'can_view' => isset($permissions['can_view']),
                        'can_create' => isset($permissions['can_create']),
                        'can_edit' => isset($permissions['can_edit']),
                        'can_delete' => isset($permissions['can_delete']),
                    ]);
                }
            }
        }

        // Salvar veículos responsáveis
        if ($request->has('vehicles')) {
            $user->vehicles()->sync($request->input('vehicles', []));
        } else {
            $user->vehicles()->sync([]);
        }

        return redirect()->route('users.index')
            ->with('success', 'Usuário cadastrado com sucesso!');
    }

    public function show(User $user)
    {
        Gate::authorize('view', $user);

        $modules = Module::where('slug', '!=', 'users')->orderBy('name')->get();
        $userPermissions = $user->modulePermissions()->with('module')->get()->keyBy('module_id');
        $userVehicles = $user->vehicles()->orderBy('name')->get();

        return view('users.show', compact('user', 'modules', 'userPermissions', 'userVehicles'));
    }

    public function edit(User $user)
    {
        Gate::authorize('update', $user);

        $modules = Module::where('slug', '!=', 'users')->orderBy('name')->get();
        $userPermissions = $user->modulePermissions()->get()->keyBy('module_id');
        $vehicles = Vehicle::where('active', true)->orderBy('name')->get();
        $userVehicles = $user->vehicles()->pluck('vehicles.id')->toArray();

        return view('users.edit', compact('user', 'modules', 'userPermissions', 'vehicles', 'userVehicles'));
    }

    public function update(Request $request, User $user)
    {
        Gate::authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_full' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,condutor',
            'active' => 'boolean',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'avatar_base64' => 'nullable|string',
            'remove_avatar' => 'nullable|boolean',
            'modules' => 'nullable|array',
            'modules.*.can_view' => 'boolean',
            'modules.*.can_create' => 'boolean',
            'modules.*.can_edit' => 'boolean',
            'modules.*.can_delete' => 'boolean',
            'vehicles' => 'nullable|array',
            'vehicles.*' => 'exists:vehicles,id',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

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

        // Impedir que admin desative sua própria conta ou mude seu próprio role
        $currentUser = Auth::user();
        $isCurrentUser = $user->id === $currentUser->id;
        $isAdmin = $user->role === 'admin' || $validated['role'] === 'admin';
        
        // Impedir que admin mude seu próprio role para condutor
        if ($isCurrentUser && $user->role === 'admin' && $validated['role'] === 'condutor') {
            return redirect()->back()
                ->with('error', 'Você não pode alterar seu próprio perfil de administrador para condutor.');
        }
        
        // Impedir que admin desative sua própria conta
        if ($isCurrentUser && $isAdmin && !$request->has('active')) {
            return redirect()->back()
                ->with('error', 'Você não pode desativar sua própria conta enquanto for administrador.');
        }

        $updateData = [
            'name' => $validated['name'],
            'name_full' => $validated['name_full'] ?? null,
            'email' => $validated['email'],
            'role' => $validated['role'],
            'avatar' => $avatarPath,
        ];

        // Apenas atualizar 'active' se não for o próprio admin tentando desativar
        if (!($isCurrentUser && $isAdmin)) {
            $updateData['active'] = $request->has('active');
        } else {
            // Forçar ativo para o próprio admin
            $updateData['active'] = true;
        }

        if (isset($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        $user->update($updateData);

        // Remover todas as permissões existentes
        $user->modulePermissions()->delete();

        // Salvar novas permissões de módulos (apenas se não for admin)
        if ($validated['role'] !== 'admin' && $request->has('modules')) {
            foreach ($request->input('modules', []) as $moduleId => $permissions) {
                if (isset($permissions['enabled']) && $permissions['enabled']) {
                    UserModulePermission::create([
                        'user_id' => $user->id,
                        'module_id' => $moduleId,
                        'can_view' => isset($permissions['can_view']),
                        'can_create' => isset($permissions['can_create']),
                        'can_edit' => isset($permissions['can_edit']),
                        'can_delete' => isset($permissions['can_delete']),
                    ]);
                }
            }
        }

        // Salvar veículos responsáveis
        if ($request->has('vehicles')) {
            $user->vehicles()->sync($request->input('vehicles', []));
        } else {
            $user->vehicles()->sync([]);
        }

        return redirect()->route('users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);

        // Impedir que admin exclua sua própria conta
        $currentUser = Auth::user();
        if ($user->id === $currentUser->id && $user->role === 'admin') {
            return redirect()->route('users.index')
                ->with('error', 'Você não pode excluir sua própria conta enquanto for administrador.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuário removido com sucesso!');
    }
}
