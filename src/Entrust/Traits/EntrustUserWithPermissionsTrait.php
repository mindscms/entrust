<?php
namespace Mindscms\Entrust\Traits;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Mindscms\Entrust
 */
use Illuminate\Support\Facades\Config;

trait EntrustUserWithPermissionsTrait
{
    use EntrustUserTrait {
        can as canEntrust;
    }

    /**
     * Many-to-Many relations with Permission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Config::get('entrust.permission'), Config::get('entrust.user_permission_table'), 'user_id', 'permission_id');
    }

    /**
     * Check if user has a permission by its name.
     *
     * @param string|array $permission Permission string or array of permissions.
     * @param bool         $requireAll All permissions in the array are required.
     *
     * @return bool
     */
    public function can($permission, $requireAll = false)
    {
        // Check specific permissions first because permissions override roles
        $permFound = false;

        $permissionArray = is_array($permission) ? $permission : [$permission];
        $getUserPermissions = $this->permissions->pluck('name');
        foreach($getUserPermissions as $userPerm)
        {
            // if permission IS found
            if(in_array($userPerm, $permissionArray))
            {
                $permFound = true;

                // if we DON'T require all, bail
                if(!$requireAll)
                {
                    break;
                }
            }
            // if permission is NOT found
            else
            {
                $permFound = false;

                // if we DO require all, bail
                if($requireAll)
                {
                    break;
                }
            }
        }

        // User permission override found
        if($permFound)
        {
            return $permFound;
        }

        // User permission not granted, check roles via entrust
        return $this->canEntrust($permission, $requireAll);
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $permission
     */
    public function attachPermission($permission)
    {
        if(is_object($permission)) {
            $permission = $permission->getKey();
        }

        if(is_array($permission)) {
            $permission = $permission['id'];
        }

        $this->permissions()->attach($permission);
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $permission
     */
    public function detachPermission($permission)
    {
        if (is_object($permission)) {
            $permission = $permission->getKey();
        }

        if (is_array($permission)) {
            $permission = $permission['id'];
        }

        $this->permissions()->detach($permission);
    }

    /**
     * Attach multiple permissions to a user
     *
     * @param mixed $permissions
     */
    public function attachPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            $this->attachPermission($permission);
        }
    }

    /**
     * Detach multiple permissions from a user
     *
     * @param mixed $permissions
     */
    public function detachPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            $this->detachPermission($permission);
        }
    }

}
