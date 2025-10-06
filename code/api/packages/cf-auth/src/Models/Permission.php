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
}
