<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\UserModulePermission;
use App\Models\SystemSetting;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SettingsController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', \App\Models\User::class); // Apenas admin pode acessar
        
        // Carregar configurações do sistema
        $settings = [
            'general' => SystemSetting::getGroup('general'),
            'vehicles' => SystemSetting::getGroup('vehicles'),
            'fuelings' => SystemSetting::getGroup('fuelings'),
            'maintenances' => SystemSetting::getGroup('maintenances'),
            'locations' => SystemSetting::getGroup('locations'),
            'email' => SystemSetting::getGroup('email'),
        ];

        // Valores padrão se não existirem
        $defaults = [
            'app_name' => config('app.name', 'Road Master'),
            'timezone' => config('app.timezone', 'America/Sao_Paulo'),
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'currency' => 'BRL',
            'currency_symbol' => 'R$',
        ];

        // Mesclar com valores salvos
        foreach ($defaults as $key => $value) {
            $group = $this->getSettingGroup($key);
            if (!isset($settings[$group][$key])) {
                $settings[$group][$key] = $value;
            }
        }

        $vehicles = Vehicle::orderBy('name')->get();
        $modules = Module::where('slug', '!=', 'users')->orderBy('name')->get();

        // Carregar preferências do dashboard do usuário
        $user = Auth::user();
        $userPreferences = $user->preferences ?? [];
        $dashboardPreferences = [
            'start_date' => $userPreferences['dashboard_start_date'] ?? Carbon::now()->startOfMonth()->format('Y-m-d'),
            'end_date' => $userPreferences['dashboard_end_date'] ?? Carbon::now()->endOfMonth()->format('Y-m-d'),
            'vehicle_id' => $userPreferences['dashboard_vehicle_id'] ?? null,
        ];

        // Carregar módulos padrão para condutores
        $defaultDriverModulesJson = SystemSetting::get('driver_default_modules', '[]');
        $defaultDriverModulesData = json_decode($defaultDriverModulesJson, true) ?? [];
        
        $defaultDriverModules = [];
        $defaultDriverModulePermissions = [];
        
        foreach ($defaultDriverModulesData as $moduleId => $permissions) {
            $defaultDriverModules[] = $moduleId;
            $defaultDriverModulePermissions[$moduleId] = $permissions;
        }

        return view('settings.index', compact('settings', 'vehicles', 'dashboardPreferences', 'modules', 'defaultDriverModules', 'defaultDriverModulePermissions'));
    }

    private function getSettingGroup($key)
    {
        return 'general';
    }

    // ========== MÓDULOS ==========

    public function createModule()
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        return view('settings.modules.create');
    }

    public function storeModule(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:modules,slug|regex:/^[a-z0-9-]+$/',
        ], [
            'slug.regex' => 'O slug deve conter apenas letras minúsculas, números e hífens.',
        ]);

        Module::create($validated);

        return redirect()->route('settings.index')
            ->with('success', 'Módulo criado com sucesso!');
    }

    public function editModule(Module $module)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        return view('settings.modules.edit', compact('module'));
    }

    public function updateModule(Request $request, Module $module)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:modules,slug,' . $module->id . '|regex:/^[a-z0-9-]+$/',
        ], [
            'slug.regex' => 'O slug deve conter apenas letras minúsculas, números e hífens.',
        ]);

        $module->update($validated);

        return redirect()->route('settings.index')
            ->with('success', 'Módulo atualizado com sucesso!');
    }

    public function destroyModule(Module $module)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        // Verificar se há permissões associadas
        $hasPermissions = UserModulePermission::where('module_id', $module->id)->exists();

        if ($hasPermissions) {
            return redirect()->route('settings.index')
                ->with('error', 'Não é possível excluir o módulo pois existem permissões associadas a ele.');
        }

        $module->delete();

        return redirect()->route('settings.index')
            ->with('success', 'Módulo excluído com sucesso!');
    }

    // ========== CONFIGURAÇÕES DO SISTEMA ==========

    public function updateSettings(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $validated = $request->validate([
            // Configurações Gerais
            'app_name' => 'required|string|max:255',
            'timezone' => 'required|string|max:255',
            'date_format' => 'required|string|max:20',
            'time_format' => 'required|string|max:20',
            'currency' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:10',
            
        ]);

        // Salvar configurações gerais
        SystemSetting::set('app_name', $validated['app_name'], 'string', 'general', 'Nome da aplicação');
        SystemSetting::set('timezone', $validated['timezone'], 'string', 'general', 'Fuso horário');
        SystemSetting::set('date_format', $validated['date_format'], 'string', 'general', 'Formato de data');
        SystemSetting::set('time_format', $validated['time_format'], 'string', 'general', 'Formato de hora');
        SystemSetting::set('currency', $validated['currency'], 'string', 'general', 'Moeda padrão');
        SystemSetting::set('currency_symbol', $validated['currency_symbol'], 'string', 'general', 'Símbolo da moeda');


        return redirect()->route('settings.index')
            ->with('success', 'Configurações atualizadas com sucesso!');
    }

    // ========== CONFIGURAÇÕES DE APARÊNCIA ==========

    public function updateAppearance(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $validated = $request->validate([
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'favicon' => 'nullable|file|mimes:png,ico,svg|max:1024',
        ], [
            'logo.image' => 'O arquivo de logo deve ser uma imagem válida.',
            'logo.mimes' => 'O logo deve ser um arquivo PNG, JPG, JPEG ou SVG.',
            'logo.max' => 'O logo não pode ser maior que 2MB.',
            'favicon.file' => 'O arquivo de favicon deve ser válido.',
            'favicon.mimes' => 'O favicon deve ser um arquivo PNG, ICO ou SVG.',
            'favicon.max' => 'O favicon não pode ser maior que 1MB.',
        ]);

        // Processar upload do logo
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $extension = $logo->getClientOriginalExtension();
            $logoName = 'logos/' . Str::uuid() . '.' . $extension;
            
            // Deletar logo anterior se existir
            $oldLogo = SystemSetting::get('system_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            
            // Garantir que o diretório existe
            Storage::disk('public')->makeDirectory('logos');
            
            // Salvar novo logo
            $logo->storeAs('', $logoName, 'public');
            SystemSetting::set('system_logo', $logoName, 'string', 'appearance', 'Logo do sistema');
        }

        // Processar upload do favicon
        if ($request->hasFile('favicon')) {
            $favicon = $request->file('favicon');
            $extension = $favicon->getClientOriginalExtension();
            $faviconName = 'favicons/' . Str::uuid() . '.' . $extension;
            
            // Deletar favicon anterior se existir
            $oldFavicon = SystemSetting::get('system_favicon');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }
            
            // Garantir que o diretório existe
            Storage::disk('public')->makeDirectory('favicons');
            
            // Salvar novo favicon
            $favicon->storeAs('', $faviconName, 'public');
            SystemSetting::set('system_favicon', $faviconName, 'string', 'appearance', 'Favicon do sistema');
        }

        return redirect()->route('settings.index')
            ->with('success', 'Configurações de aparência atualizadas com sucesso!');
    }

    public function resetAppearance(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        // Deletar logo se existir
        $logo = SystemSetting::get('system_logo');
        if ($logo && Storage::disk('public')->exists($logo)) {
            Storage::disk('public')->delete($logo);
        }
        SystemSetting::where('key', 'system_logo')->delete();

        // Deletar favicon se existir
        $favicon = SystemSetting::get('system_favicon');
        if ($favicon && Storage::disk('public')->exists($favicon)) {
            Storage::disk('public')->delete($favicon);
        }
        SystemSetting::where('key', 'system_favicon')->delete();

        return redirect()->route('settings.index')
            ->with('success', 'Logos redefinidas para o padrão do Laravel com sucesso!');
    }

    // ========== PREFERÊNCIAS DO DASHBOARD ==========

    public function updateDashboardPreferences(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $validated = $request->validate([
            'dashboard_start_date' => 'required|date',
            'dashboard_end_date' => 'required|date|after_or_equal:dashboard_start_date',
            'dashboard_vehicle_id' => 'nullable|exists:vehicles,id',
        ], [
            'dashboard_end_date.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
        ]);

        $user = Auth::user();
        $user->setPreferences([
            'dashboard_start_date' => $validated['dashboard_start_date'],
            'dashboard_end_date' => $validated['dashboard_end_date'],
            'dashboard_vehicle_id' => $validated['dashboard_vehicle_id'] ?? null,
        ]);

        return redirect()->route('settings.index')
            ->with('success', 'Preferências do dashboard salvas com sucesso!');
    }

    // ========== CONFIGURAÇÕES DE PERFIS ==========

    public function updateDriverDefaultModules(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $validated = $request->validate([
            'modules' => 'nullable|array',
            'modules.*.enabled' => 'boolean',
            'modules.*.can_view' => 'boolean',
            'modules.*.can_create' => 'boolean',
            'modules.*.can_edit' => 'boolean',
            'modules.*.can_delete' => 'boolean',
        ]);

        $defaultModules = [];
        
        if ($request->has('modules')) {
            foreach ($request->input('modules', []) as $moduleId => $permissions) {
                if (isset($permissions['enabled']) && $permissions['enabled']) {
                    $defaultModules[$moduleId] = [
                        'can_view' => isset($permissions['can_view']) && $permissions['can_view'],
                        'can_create' => isset($permissions['can_create']) && $permissions['can_create'],
                        'can_edit' => isset($permissions['can_edit']) && $permissions['can_edit'],
                        'can_delete' => isset($permissions['can_delete']) && $permissions['can_delete'],
                    ];
                }
            }
        }

        SystemSetting::set('driver_default_modules', json_encode($defaultModules), 'json', 'profiles', 'Módulos padrão para perfil de condutor');

        return redirect()->route('settings.index', ['activeTab' => 'profiles'])
            ->with('success', 'Módulos padrão para condutores atualizados com sucesso!');
    }

    // ========== CONFIGURAÇÕES DE EMAIL ==========

    public function updateEmailSettings(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $validated = $request->validate([
            'email_notifications_enabled' => 'nullable|boolean',
            'email_from_address' => 'required|email',
            'email_from_name' => 'required|string|max:255',
        ]);

        // Salvar configurações de email
        SystemSetting::set('email_notifications_enabled', $validated['email_notifications_enabled'] ? '1' : '0', 'boolean', 'email', 'Habilitar notificações por email');
        SystemSetting::set('email_from_address', $validated['email_from_address'], 'string', 'email', 'Endereço de email remetente');
        SystemSetting::set('email_from_name', $validated['email_from_name'], 'string', 'email', 'Nome do remetente');

        return redirect()->route('settings.index', ['activeTab' => 'email'])
            ->with('success', 'Configurações de email atualizadas com sucesso!');
    }
}

