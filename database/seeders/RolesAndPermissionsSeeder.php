<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /** resource => abilities */
    public const MATRIX = [
        'businesses' => ['view', 'create', 'update', 'delete'],
        'services' => ['view', 'create', 'update', 'delete'],
        'products' => ['view', 'create', 'update', 'delete'],
        'inventory' => ['view', 'update'],
        'orders' => ['view', 'update'],
        'bookings' => ['view', 'update'],
        'enquiries' => ['view', 'update', 'delete'],
        'staff' => ['view', 'create', 'update', 'delete'],
        'schedule' => ['view', 'create', 'update', 'delete'],
        'events' => ['view', 'create', 'update', 'delete'],
        'properties' => ['view', 'create', 'update', 'delete'],
        'customers' => ['view'],
        'reports' => ['view'],
        'cms' => ['view', 'create', 'update', 'delete'],
        'media' => ['view', 'create', 'delete'],
        'logs' => ['view'],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::MATRIX as $resource => $abilities) {
            foreach ($abilities as $a) {
                Permission::findOrCreate("$resource.$a", 'web');
            }
        }

        Role::findOrCreate('Super Admin', 'web'); // also granted everything via Gate::before

        Role::findOrCreate('Business Manager', 'web')->syncPermissions(
            collect(self::MATRIX)->except(['businesses', 'cms', 'logs'])->flatMap(fn ($abilities, $r) => collect($abilities)->map(fn ($a) => "$r.$a"))
                ->reject(fn ($p) => in_array($p, ['customers.view']))->push('customers.view', 'media.view', 'media.create')->unique()->values()->all()
        );

        Role::findOrCreate('Staff', 'web')->syncPermissions(['bookings.view', 'bookings.update', 'orders.view', 'inventory.view', 'enquiries.view']);

        Role::findOrCreate('Customer', 'web');
    }
}
