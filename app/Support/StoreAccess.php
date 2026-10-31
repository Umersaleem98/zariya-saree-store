<?php

namespace App\Support;

class StoreAccess
{
    public const PERMISSIONS = ['products.view', 'products.create', 'products.edit', 'products.delete', 'sales.manage', 'stock.manage', 'taxonomy.manage', 'orders.view', 'orders.update', 'customers.manage', 'users.manage', 'roles.manage', 'settings.manage', 'analytics.view', 'costs.manage'];
    public const ROLES = ['super_admin', 'admin', 'order_manager', 'product_manager', 'staff'];

    public static function permissions($user): array
    {
        if (! $user || ! $user->active || ! in_array($user->role, self::ROLES)) return [];
        if ($user->role === 'super_admin') return self::PERMISSIONS;
        $defaults = match ($user->role) {
            'admin' => array_diff(self::PERMISSIONS, ['users.manage', 'roles.manage', 'analytics.view', 'costs.manage']),
            'order_manager' => ['orders.view', 'orders.update', 'customers.manage'],
            'product_manager' => ['products.view', 'products.create', 'products.edit', 'products.delete', 'sales.manage', 'stock.manage', 'taxonomy.manage'],
            default => ['products.view', 'orders.view'],
        };
        $custom = is_string($user->permissions) ? json_decode($user->permissions, true) : $user->permissions;
        return array_values(array_intersect($custom ?? $defaults, array_diff(self::PERMISSIONS, ['users.manage', 'roles.manage'])));
    }

    public static function allows($user, string $permission): bool
    {
        return in_array($permission, self::permissions($user));
    }
}
