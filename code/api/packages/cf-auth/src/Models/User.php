<?php

namespace CF\CE\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The guard used by this model for Spatie Permission
     */
    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'description',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
        ];
    }

    /**
     * Check if user has a specific role by code
     */
    public function hasRoleByCode(string $code): bool
    {
        return $this->roles()->where('code', $code)->exists();
    }

    /**
     * Get user's role codes
     */
    public function getRoleCodes(): array
    {
        return $this->roles()->pluck('code')->toArray();
    }

    /**
     * Assign role by code
     */
    public function assignRoleByCode(string $code): self
    {
        $role = \CF\CE\Auth\Models\Role::where('code', $code)->first();
        
        if ($role) {
            $this->assignRole($role);
        }
        
        return $this;
    }

    /**
     * Remove role by code
     */
    public function removeRoleByCode(string $code): self
    {
        $role = \CF\CE\Auth\Models\Role::where('code', $code)->first();
        
        if ($role) {
            $this->removeRole($role);
        }
        
        return $this;
    }
}
