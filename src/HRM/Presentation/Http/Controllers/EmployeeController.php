<?php

namespace TmrEcosystem\HRM\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use TmrEcosystem\HRM\Infrastructure\Persistence\Eloquent\Models\Department;
use TmrEcosystem\HRM\Infrastructure\Persistence\Eloquent\Models\Position;

class EmployeeController extends Controller
{
    public function create()
    {
        return Inertia::render('HRM/Employees/Create', [
            // ส่งข้อมูล Master Data ไปให้ Dropdown
            'departments' => Department::select('id', 'name')->get(),
            'positions' => Position::select('id', 'name')->get(),
        ]);
    }
}
