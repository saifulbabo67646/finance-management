<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // User Management
            ['name' => 'manage_users', 'description' => 'Create, update, delete users'],
            ['name' => 'view_users', 'description' => 'View user list and details'],
            
            // Role & Permission Management
            ['name' => 'manage_roles', 'description' => 'Create, update, delete roles'],
            ['name' => 'assign_permissions', 'description' => 'Assign permissions to roles'],
            
            // Cashbook & Transactions
            ['name' => 'create_transactions', 'description' => 'Create new transactions'],
            ['name' => 'view_transactions', 'description' => 'View transaction history'],
            ['name' => 'edit_transactions', 'description' => 'Edit existing transactions'],
            ['name' => 'delete_transactions', 'description' => 'Delete transactions'],
            
            // Accounts Management
            ['name' => 'manage_accounts', 'description' => 'Create, update, delete accounts'],
            ['name' => 'view_accounts', 'description' => 'View account list and balances'],
            
            // Reports & Dashboard
            ['name' => 'view_dashboard', 'description' => 'Access main dashboard'],
            ['name' => 'view_reports', 'description' => 'View financial reports'],
            ['name' => 'export_reports', 'description' => 'Export reports to PDF/Excel'],
            
            // Ledger Management
            ['name' => 'view_ledger', 'description' => 'View ledger entries'],
            ['name' => 'manage_ledger', 'description' => 'Manage ledger entries'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }

        // Create roles
        $roles = [
            [
                'name' => 'super_admin',
                'description' => 'Full system access with all permissions',
                'permissions' => Permission::all()->pluck('name')->toArray()
            ],
            [
                'name' => 'company_admin',
                'description' => 'Company-level administrator',
                'permissions' => [
                    'view_users', 'manage_users',
                    'create_transactions', 'view_transactions', 'edit_transactions',
                    'manage_accounts', 'view_accounts',
                    'view_dashboard', 'view_reports', 'export_reports',
                    'view_ledger', 'manage_ledger'
                ]
            ],
            [
                'name' => 'branch_manager',
                'description' => 'Branch-level manager',
                'permissions' => [
                    'view_users',
                    'create_transactions', 'view_transactions', 'edit_transactions',
                    'view_accounts',
                    'view_dashboard', 'view_reports',
                    'view_ledger'
                ]
            ],
            [
                'name' => 'accountant',
                'description' => 'Accountant with transaction and reporting access',
                'permissions' => [
                    'create_transactions', 'view_transactions', 'edit_transactions',
                    'view_accounts',
                    'view_dashboard', 'view_reports',
                    'view_ledger', 'manage_ledger'
                ]
            ],
            [
                'name' => 'viewer',
                'description' => 'Read-only access to financial data',
                'permissions' => [
                    'view_transactions',
                    'view_accounts',
                    'view_dashboard', 'view_reports',
                    'view_ledger'
                ]
            ]
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                [
                    'description' => $roleData['description'],
                    'is_active' => true
                ]
            );

            // Assign permissions to role
            $permissions = Permission::whereIn('name', $roleData['permissions'])->get();
            $role->permissions()->sync($permissions);
        }

        // Create default super admin user
        $superAdminRole = Role::where('name', 'super_admin')->first();
        
        User::firstOrCreate(
            ['email' => 'admin@finance.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role_id' => $superAdminRole->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
