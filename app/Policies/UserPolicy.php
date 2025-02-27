<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;


class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-any User') || $user->roles('super admin');
    }
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo('view User') || $user->id === $model->id || $user->roles('super admin');
    }
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create User') || $user->roles('super admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // return $user->hasPermissionTo('update User') || $user->id === $model->id || $user->roles('super admin');
        // يسمح للمستخدم السوبر أدمن بتعديل نفسه فقط
        if ($user->hasRole('super admin') && $model->id === $user->id) {
            return true;
        }

        if ($model->hasRole('super admin')) {
            return false;
        }

        return $user->can('update User');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if ($model->hasRole('super admin')) {
            return false;
        }
        return $user->hasPermissionTo('delete User');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasPermissionTo('restore User')  || $user->roles('super admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasPermissionTo('force-delete User')  || $user->roles('super admin');
    }
}