<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creazione dei ruoli
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $authorRole = Role::firstOrCreate(['name' => 'author']);

        // Creazione dei permessi
        $editUsersPermission = Permission::firstOrCreate(['name' => 'edit users']);
        $createPostsPermission = Permission::firstOrCreate(['name' => 'create posts']);
        $editPostsPermission = Permission::firstOrCreate(['name' => 'edit posts']);
        $deletePostsPermission = Permission::firstOrCreate(['name' => 'delete posts']);

        // Assegnazione permessi ai ruoli
        $adminRole->givePermissionTo($editUsersPermission);
        $adminRole->givePermissionTo($createPostsPermission);
        $adminRole->givePermissionTo($editPostsPermission);
        $adminRole->givePermissionTo($deletePostsPermission);

        $authorRole->givePermissionTo($createPostsPermission);
        $authorRole->givePermissionTo($editPostsPermission);

        // Assegnazione ruolo admin al primo utente
        $user = User::find(1);
        if ($user) {
            $user->assignRole('admin');
        }
    }
}
