<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsController extends Controller
{
    /**
     * Restituisce tutti i ruoli con i relativi permessi e l'elenco di tutti i permessi.
     */
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Sincronizza i permessi di un ruolo.
     */
    public function updateRolePermissions(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'permissions'   => ['present', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->syncPermissions($validated['permissions']);

        return response()->json([
            'message' => 'Permessi aggiornati con successo',
            'role'    => $role->load('permissions'),
        ]);
    }

    /**
     * Crea un nuovo permesso.
     */
    public function storePermission(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
        ]);

        $permission = Permission::create(['name' => $validated['name']]);

        return response()->json([
            'message'    => 'Permesso creato con successo',
            'permission' => $permission,
        ], 201);
    }

    /**
     * Elimina un permesso.
     */
    public function destroyPermission(Permission $permission): JsonResponse
    {
        $permission->delete();

        return response()->json(['message' => 'Permesso eliminato con successo']);
    }

    /**
     * Crea un nuovo ruolo.
     */
    public function storeRole(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
        ]);

        $role = Role::create(['name' => $validated['name']]);

        return response()->json([
            'message' => 'Ruolo creato con successo',
            'role'    => $role->load('permissions'),
        ], 201);
    }

    /**
     * Elimina un ruolo.
     */
    public function destroyRole(Role $role): JsonResponse
    {
        $role->delete();

        return response()->json(['message' => 'Ruolo eliminato con successo']);
    }
}
