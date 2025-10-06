<?php

namespace App\Models;

use CF\CE\Auth\Models\User as BaseUser;

/**
 * Extended User model with CF Auth capabilities
 * 
 * This model extends the CF Auth User model to provide
 * authentication and role management capabilities.
 */
class User extends BaseUser
{
    // All functionality is inherited from CF\CE\Auth\Models\User
    // which includes HasApiTokens and HasRoles traits
}
