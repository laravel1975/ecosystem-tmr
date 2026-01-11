<?php

namespace TmrEcosystem\Shared\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use TmrEcosystem\Shared\Application\DTOs\AppModuleData;
use TmrEcosystem\Shared\Application\DTOs\AppPanelResponseData;
use Spatie\LaravelData\DataCollection; // <--- เพิ่มการนำเข้าคลาสนี้
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AppPanelController extends Controller
{
    public function index()
    {
        $modules = [
            new AppModuleData(
                id: 'inventory',
                name: 'Inventory Management',
                description: 'Double-Entry Warehouse Control',
                icon: 'Package',
                route: '/inventory/dashboard',
                color: 'text-blue-600',
                badge_count: 0
            ),
        ];

        // แก้ไขบรรทัดนี้: v4 ใช้การ new DataCollection(ชื่อคลาส DTO, ข้อมูล)
        $panelData = new AppPanelResponseData(
            modules: new DataCollection(AppModuleData::class, $modules),
            system_message: 'EcoSystem TMR Online',
            user_summary: ['name' => Auth::user()->name]
        );

        return Inertia::render('AppPanel', [
            'panel' => $panelData,
        ]);
    }
}
