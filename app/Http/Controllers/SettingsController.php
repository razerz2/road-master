<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\UserModulePermission;
use App\Models\SystemSetting;
use App\Models\Vehicle;
use App\Helpers\EnvHelper;
use App\Mail\TestEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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
            'reviews' => SystemSetting::getGroup('reviews'),
            'mandatory_events' => SystemSetting::getGroup('mandatory_events'),
            'notifications' => SystemSetting::getGroup('notifications'),
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
            'button_color_from' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'button_color_to' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ], [
            'logo.image' => 'O arquivo de logo deve ser uma imagem válida.',
            'logo.mimes' => 'O logo deve ser um arquivo PNG, JPG, JPEG ou SVG.',
            'logo.max' => 'O logo não pode ser maior que 2MB.',
            'favicon.file' => 'O arquivo de favicon deve ser válido.',
            'favicon.mimes' => 'O favicon deve ser um arquivo PNG, ICO ou SVG.',
            'favicon.max' => 'O favicon não pode ser maior que 1MB.',
            'button_color_from.regex' => 'A cor inicial deve estar no formato hexadecimal (ex: #4F46E5).',
            'button_color_to.regex' => 'A cor final deve estar no formato hexadecimal (ex: #9333EA).',
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

        // Salvar cores dos botões
        if ($request->has('button_color_from')) {
            SystemSetting::set('button_color_from', $validated['button_color_from'], 'string', 'appearance', 'Cor inicial dos botões');
        }

        if ($request->has('button_color_to')) {
            SystemSetting::set('button_color_to', $validated['button_color_to'], 'string', 'appearance', 'Cor final dos botões');
        }

        return redirect()->route('settings.index', ['activeTab' => 'appearance'])
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

        // Redefinir cores dos botões para o padrão
        SystemSetting::where('key', 'button_color_from')->delete();
        SystemSetting::where('key', 'button_color_to')->delete();

        return redirect()->route('settings.index', ['activeTab' => 'appearance'])
            ->with('success', 'Aparência redefinida para o padrão com sucesso!');
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

        $emailEnabled = $request->has('email_notifications_enabled') && $request->email_notifications_enabled === '1';

        $validated = $request->validate([
            'email_notifications_enabled' => 'nullable|boolean',
            'email_from_address' => $emailEnabled ? 'required|email' : 'nullable|email',
            'email_from_name' => $emailEnabled ? 'required|string|max:255' : 'nullable|string|max:255',
            'mail_mailer' => $emailEnabled ? 'required|string|in:smtp,sendmail,log' : 'nullable',
            'mail_host' => $emailEnabled ? 'required|string|max:255' : 'nullable',
            'mail_port' => $emailEnabled ? 'required|integer|min:1|max:65535' : 'nullable',
            'mail_encryption' => 'nullable|string|in:tls,ssl',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
        ]);

        // Salvar configurações de email no banco
        SystemSetting::set('email_notifications_enabled', $emailEnabled ? '1' : '0', 'boolean', 'email', 'Habilitar notificações por email');
        SystemSetting::set('email_from_address', $validated['email_from_address'], 'string', 'email', 'Endereço de email remetente');
        SystemSetting::set('email_from_name', $validated['email_from_name'], 'string', 'email', 'Nome do remetente');
        
        // Salvar configurações SMTP no banco também para persistência
        SystemSetting::set('mail_mailer', $validated['mail_mailer'] ?? 'smtp', 'string', 'email', 'Tipo de mailer');
        SystemSetting::set('mail_host', $validated['mail_host'] ?? '', 'string', 'email', 'Servidor SMTP');
        SystemSetting::set('mail_port', (string)($validated['mail_port'] ?? '587'), 'string', 'email', 'Porta do servidor');
        SystemSetting::set('mail_encryption', $validated['mail_encryption'] ?? '', 'string', 'email', 'Criptografia');
        SystemSetting::set('mail_username', $validated['mail_username'] ?? '', 'string', 'email', 'Usuário SMTP');
        
        if (!empty($request->mail_password)) {
            SystemSetting::set('mail_password', $request->mail_password, 'string', 'email', 'Senha SMTP');
        }

        // Se notificações de email estão habilitadas, atualizar configurações SMTP no .env
        if ($emailEnabled) {
            $envUpdates = [
                'MAIL_MAILER' => $validated['mail_mailer'] ?? 'smtp',
                'MAIL_HOST' => $validated['mail_host'] ?? '',
                'MAIL_PORT' => (string)($validated['mail_port'] ?? '587'),
            ];

            // Adicionar criptografia se fornecida
            if (!empty($validated['mail_encryption'])) {
                $envUpdates['MAIL_ENCRYPTION'] = $validated['mail_encryption'];
            } else {
                // Remover MAIL_ENCRYPTION se não fornecida
                $envUpdates['MAIL_ENCRYPTION'] = '';
            }

            // Adicionar username se fornecido
            if (!empty($validated['mail_username'])) {
                $envUpdates['MAIL_USERNAME'] = $validated['mail_username'];
            }

            // Adicionar password apenas se fornecido (para não sobrescrever senha existente)
            // Não incluir no array se vazio para não remover a senha existente
            if (!empty($request->mail_password)) {
                $envUpdates['MAIL_PASSWORD'] = $request->mail_password;
            }

            // Atualizar MAIL_FROM_ADDRESS e MAIL_FROM_NAME no .env também
            $envUpdates['MAIL_FROM_ADDRESS'] = $validated['email_from_address'];
            $envUpdates['MAIL_FROM_NAME'] = $validated['email_from_name'];

            // Atualizar arquivo .env
            try {
                EnvHelper::updateMultipleEnv($envUpdates);
            } catch (\Exception $e) {
                return redirect()->route('settings.index', ['activeTab' => 'email'])
                    ->with('error', 'Erro ao atualizar arquivo .env: ' . $e->getMessage());
            }
        }

        return redirect()->route('settings.index', ['activeTab' => 'email'])
            ->with('success', 'Configurações de email atualizadas com sucesso!');
    }

    /**
     * Testar configurações de email
     */
    public function testEmailSettings(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        try {
            $user = Auth::user();
            
            if (!$user->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não possui um email cadastrado. Por favor, adicione um email no seu perfil antes de testar.',
                ], 400);
            }

            // Obter configurações do formulário ou do banco/env
            $fromAddress = $request->input('email_from_address') 
                ?? SystemSetting::get('email_from_address') 
                ?? config('mail.from.address', 'noreply@example.com');
            
            $fromName = $request->input('email_from_name') 
                ?? SystemSetting::get('email_from_name') 
                ?? config('mail.from.name', config('app.name', 'Road Master'));

            // Se o usuário forneceu configurações SMTP no formulário, atualizar temporariamente
            if ($request->has('mail_host') && !empty($request->input('mail_host'))) {
                // Atualizar configurações temporariamente usando Config
                config([
                    'mail.mailers.smtp.host' => $request->input('mail_host'),
                    'mail.mailers.smtp.port' => $request->input('mail_port', 587),
                    'mail.mailers.smtp.username' => $request->input('mail_username', ''),
                    'mail.mailers.smtp.password' => $request->input('mail_password', ''),
                    'mail.mailers.smtp.encryption' => $request->input('mail_encryption', 'tls'),
                    'mail.default' => $request->input('mail_mailer', 'smtp'),
                ]);
                
                // Limpar cache de configuração para garantir que as novas configurações sejam usadas
                if (app()->bound('config')) {
                    app('config')->set('mail.mailers.smtp.host', $request->input('mail_host'));
                    app('config')->set('mail.mailers.smtp.port', $request->input('mail_port', 587));
                    app('config')->set('mail.mailers.smtp.username', $request->input('mail_username', ''));
                    app('config')->set('mail.mailers.smtp.password', $request->input('mail_password', ''));
                    app('config')->set('mail.mailers.smtp.encryption', $request->input('mail_encryption', 'tls'));
                    app('config')->set('mail.default', $request->input('mail_mailer', 'smtp'));
                }
            }

            // Enviar email de teste usando o mailer padrão
            Mail::to($user->email)->send(new TestEmail($fromAddress, $fromName));

            return response()->json([
                'success' => true,
                'message' => 'Email de teste enviado com sucesso! Verifique sua caixa de entrada.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar email de teste: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ========== CONFIGURAÇÕES DE REVISÃO E OBRIGAÇÕES LEGAIS ==========

    public function updateReviewAndMandatoryEventSettings(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $validated = $request->validate([
            // Configurações de Revisão
            'review_notification_km_before' => 'required|integer|min:0|max:100000',
            'review_check_time' => 'required|date_format:H:i',
            'review_notify_only_admins' => 'nullable|boolean',
            
            // Configurações de Obrigações Legais
            'mandatory_event_days_before' => 'required|integer|min:1|max:365',
            'mandatory_event_check_time' => 'required|date_format:H:i',
            'mandatory_event_notify_only_admins' => 'nullable|boolean',
            
            // Configurações Gerais de Notificações
            'notifications_enabled' => 'nullable|boolean',
            'notification_check_frequency' => 'required|string|in:daily,weekly',
        ], [
            'review_check_time.date_format' => 'O horário deve estar no formato HH:mm (ex: 08:00)',
            'mandatory_event_check_time.date_format' => 'O horário deve estar no formato HH:mm (ex: 08:00)',
        ]);

        // Salvar configurações de revisão
        SystemSetting::set(
            'review_notification_km_before',
            (string)$validated['review_notification_km_before'],
            'integer',
            'reviews',
            'KM de antecedência para notificar revisões'
        );
        
        SystemSetting::set(
            'review_check_time',
            $validated['review_check_time'],
            'string',
            'reviews',
            'Horário para verificar revisões'
        );
        
        SystemSetting::set(
            'review_notify_only_admins',
            $request->has('review_notify_only_admins') ? '1' : '0',
            'boolean',
            'reviews',
            'Notificar apenas administradores'
        );

        // Salvar configurações de obrigações legais
        SystemSetting::set(
            'mandatory_event_days_before',
            (string)$validated['mandatory_event_days_before'],
            'integer',
            'mandatory_events',
            'Dias de antecedência para notificar obrigações legais'
        );
        
        SystemSetting::set(
            'mandatory_event_check_time',
            $validated['mandatory_event_check_time'],
            'string',
            'mandatory_events',
            'Horário para verificar obrigações legais'
        );
        
        SystemSetting::set(
            'mandatory_event_notify_only_admins',
            $request->has('mandatory_event_notify_only_admins') ? '1' : '0',
            'boolean',
            'mandatory_events',
            'Notificar apenas administradores'
        );

        // Salvar configurações gerais de notificações
        SystemSetting::set(
            'notifications_enabled',
            $request->has('notifications_enabled') ? '1' : '0',
            'boolean',
            'notifications',
            'Habilitar notificações automáticas'
        );
        
        SystemSetting::set(
            'notification_check_frequency',
            $validated['notification_check_frequency'],
            'string',
            'notifications',
            'Frequência de verificação'
        );

        return redirect()->route('settings.index', ['activeTab' => 'notifications'])
            ->with('success', 'Configurações de notificações atualizadas com sucesso!');
    }
}

