<?php

namespace TmrEcosystem\Shared\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use TmrEcosystem\Shared\Application\DTOs\AppModuleData;
use TmrEcosystem\Shared\Application\DTOs\AppPanelResponseData;
use Spatie\LaravelData\DataCollection;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AppPanelController extends Controller
{
    public function index()
    {
        $modules = [
            new AppModuleData(
                id: 'inventory',
                name: 'Inventory',
                description: 'ระบบคลังสินค้าแบบบัญชีคู่ (Double-Entry)',
                icon: 'Package',
                route: '/inventory/dashboard',
                color: 'text-blue-600',
                badge_count: 0 // อนาคต: นับรายการ Stock Move ที่รอตรวจสอบ
            ),
            new AppModuleData(
                id: 'procurement',
                name: 'Procurement',
                description: 'จัดการการจัดซื้อสินค้าจากคู่ค้า',
                icon: 'ShoppingCart',
                route: '/purchase',
                color: 'text-orange-600',
                badge_count: 0 // อนาคต: นับ PO รอยืนยัน
            ),
            new AppModuleData(
                id: 'sales',
                name: 'Sales & CRM',
                description: 'บริหารจัดการคำสั่งซื้อและลูกค้าสัมพันธ์',
                icon: 'Users2',
                route: '/sales',
                color: 'text-green-600',
                badge_count: 0
            ),
            new AppModuleData(
                id: 'logistics',
                name: 'Logistics',
                description: 'จัดการการขนส่งและติดตามสินค้า',
                icon: 'Truck',
                route: '/logistics/shipments',
                color: 'text-indigo-600',
                badge_count: 0
            ),
            new AppModuleData(
                id: 'hrm',
                name: 'Human Resource',
                description: 'จัดการข้อมูลบุคลากรและการทำงาน',
                icon: 'Contact',
                route: '/hrm/employees',
                color: 'text-rose-600',
                badge_count: 0
            ),
            new AppModuleData(
                id: 'iam',
                name: 'System Settings',
                description: 'จัดการสิทธิ์ผู้ใช้งานและตั้งค่าระบบ',
                icon: 'ShieldCheck',
                route: '/iam/users',
                color: 'text-slate-600',
                badge_count: 0
            ),
        ];

        $panelData = new AppPanelResponseData(
            modules: new DataCollection(AppModuleData::class, $modules),
            system_message: 'EcoSystem TMR Core Engine Active',
            user_summary: ['name' => Auth::user()->name]
        );

        return Inertia::render('AppPanel', [
            'panel' => $panelData,
        ]);
    }
}
