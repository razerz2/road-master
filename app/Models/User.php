<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'name_full',
        'email',
        'password',
        'role',
        'active',
        'avatar',
        'preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'preferences' => 'array',
        ];
    }

    /**
     * Relacionamentos
     */
    public function trips()
    {
        return $this->hasMany(Trip::class, 'driver_id');
    }

    public function fuelings()
    {
        return $this->hasMany(Fueling::class);
    }

    public function modulePermissions()
    {
        return $this->hasMany(UserModulePermission::class);
    }

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'user_vehicle');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Verifica se o usuário tem permissão em um módulo
     */
    public function hasPermission($moduleSlug, $action = 'view')
    {
        if ($this->role === 'admin') {
            return true;
        }

        $module = Module::where('slug', $moduleSlug)->first();
        if (!$module) {
            return false;
        }

        $permission = $this->modulePermissions()
            ->where('module_id', $module->id)
            ->first();

        if (!$permission) {
            return false;
        }

        $actionField = 'can_' . $action;
        return $permission->$actionField ?? false;
    }

    /**
     * Obter preferência do usuário
     */
    public function getPreference($key, $default = null)
    {
        $preferences = $this->preferences ?? [];
        return $preferences[$key] ?? $default;
    }

    /**
     * Definir preferência do usuário
     */
    public function setPreference($key, $value)
    {
        $preferences = $this->preferences ?? [];
        $preferences[$key] = $value;
        $this->preferences = $preferences;
        $this->save();
    }

    /**
     * Definir múltiplas preferências
     */
    public function setPreferences(array $preferences)
    {
        $currentPreferences = $this->preferences ?? [];
        $this->preferences = array_merge($currentPreferences, $preferences);
        $this->save();
    }
}
