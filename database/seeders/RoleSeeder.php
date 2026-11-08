<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * The MVP roles are customer, seller and admin. Delivery and support roles
 * come later.
 */
class RoleSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private const PERMISSIONS = [
        'business.approve',
        'product.create',
        'product.update',
        'review.moderate',
        'order.manage',
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // The seeder runs with model events disabled, so the cache flush that
        // normally follows a new permission never happens.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $customer = Role::findOrCreate('customer', 'web');
        $seller = Role::findOrCreate('seller', 'web');
        $admin = Role::findOrCreate('admin', 'web');

        $seller->syncPermissions(['product.create', 'product.update', 'order.manage']);
        $admin->syncPermissions(Permission::all());
        $customer->syncPermissions([]);
    }
}
