<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = collect(PermissionsEnum::values())
        ->map(fn($permission) => ['name' => $permission])->toArray();

        Permission::upsert($permissions,['name']);
        $ownerRole = Role::firstOrCreate(['name' => 'owner']);
        $ownerRole->permissions()->sync(Permission::pluck('id')->toArray());
    }
}
