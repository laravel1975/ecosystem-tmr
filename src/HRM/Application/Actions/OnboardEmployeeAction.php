<?php

namespace TmrEcosystem\HRM\Application\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use TmrEcosystem\IAM\Infrastructure\Persistence\Eloquent\Models\User;
use TmrEcosystem\HRM\Infrastructure\Persistence\Eloquent\Models\Employee;
use TmrEcosystem\HRM\Application\DTOs\EmployeeData;
// สมมติว่ามี Inventory Location Model
// use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryLocation;

class OnboardEmployeeAction
{
    public function execute(EmployeeData $data, string $password, array $roleNames = []): Employee
    {
        return DB::transaction(function () use ($data, $password, $roleNames) {

            // 1. Create User (IAM) if email provided
            $user = null;
            if ($data->email instanceof \Spatie\LaravelData\Optional === false && $data->email) {
                $user = User::create([
                    'name' => "{$data->first_name} {$data->last_name}",
                    'email' => $data->email,
                    'password' => Hash::make($password),
                ]);

                // Assign Roles
                if (!empty($roleNames)) {
                    $user->syncRoles($roleNames);
                }
            }

            // 2. (Optional) Create Virtual Location for Technician/Sales (Mobile Stock)
            $personalLocationId = null;
            if ($data->is_technician || $data->is_salesperson) {
                // Logic สร้าง Location ใหม่ใน Inventory Module จะถูกเรียกตรงนี้
                // $location = InventoryLocation::create([...]);
                // $personalLocationId = $location->id;
                // เพื่อลด Dependency ตอนนี้เราข้ามไปก่อน หรือรับค่ามาจาก DTO
            }

            // 3. Create Employee (HRM)
            $employee = Employee::create([
                'code' => $data->code,
                'first_name' => $data->first_name,
                'last_name' => $data->last_name,
                'email' => $data->email,
                'phone' => $data->phone,
                'user_id' => $user?->id,
                'department_id' => $data->department_id,
                'position_id' => $data->position_id,
                'is_salesperson' => $data->is_salesperson,
                'is_purchaser' => $data->is_purchaser,
                'is_technician' => $data->is_technician,
                'default_warehouse_id' => $data->default_warehouse_id,
                'inventory_location_id' => $data->inventory_location_id ?? $personalLocationId,
                'status' => 'active',
            ]);

            return $employee;
        });
    }
}
