<?php

namespace CF\CE\Auth\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get translated description using name as key
     * Frontend can use trans('permissions.' . $this->name) for internationalization
     */
    public function getTranslatedDescriptionAttribute(): string
    {
        return trans('permissions.' . $this->name, [], null, false) ?: $this->description ?: $this->name;
    }

    /**
     * Get permission by name or create if doesn't exist
     * 
     * This method is used by the RoleSeeder to ensure idempotent seeding
     * of permissions. It provides compatibility for both mono-tenant (CE)
     * and multi-tenant (SaaS) implementations.
     *
     * @param array $attributes Must include 'name', optionally 'guard_name', 'description'
     * @return static
     * @throws \InvalidArgumentException If name is not provided
     */
    public static function getByCodeOrCreate(array $attributes)
    {
        $name = $attributes['name'] ?? $attributes['code'] ?? null;
        
        if (!$name) {
            throw new \InvalidArgumentException('Name is required for getByCodeOrCreate');
        }

        $guardName = $attributes['guard_name'] ?? config('auth.defaults.guard');

        // Try to find by name and guard
        $permission = static::where('name', $name)
            ->where('guard_name', $guardName)
            ->first();

        if ($permission) {
            return $permission;
        }

        // Create new permission
        $attributes['name'] = $name;
        $attributes['guard_name'] = $guardName;
        unset($attributes['code']); // Remove code if present
        
        return static::create($attributes);
    }
}
