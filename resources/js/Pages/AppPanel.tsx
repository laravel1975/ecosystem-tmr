import React from 'react';
import AppPanelLayout from '@/Layouts/AppPanelLayout';
import { Card } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Head, Link } from '@inertiajs/react';
import * as Icons from 'lucide-react';
import { cn } from '@/lib/utils';

// --- Types & Interfaces ---
interface Module {
    id: string;
    name: string;
    description: string;
    icon: string;
    route: string;
    color: string;
    badge_count: number;
    is_active: boolean;
}

interface AppPanelProps {
    panel: {
        data?: {
            modules: Module[];
            system_message: string;
            user_summary: { name: string };
        };
        modules?: Module[];
        system_message?: string;
        user_summary?: { name: string };
    };
}

export default function AppPanel({ panel }: AppPanelProps) {
    const modules = panel?.data?.modules ?? panel?.modules ?? [];
    const user = panel?.data?.user_summary ?? panel?.user_summary;
    const systemMessage = panel?.data?.system_message ?? panel?.system_message ?? 'System Synchronized';

    return (
        <AppPanelLayout>
            <Head title="Main Menu | EcoSystem TMR" />

            {/* ส่วน Header ต้อนรับแบบ Modern Minimalist */}
            {/* <div className="relative mb-12 animate-in fade-in slide-in-from-top-4 duration-1000">
                <div className="flex items-center space-x-2 text-primary font-bold tracking-tight text-sm uppercase mb-3">
                    <div className="h-[2px] w-8 bg-primary rounded-full" />
                    <span>Control Center</span>
                </div>
                <h1 className="text-5xl font-black text-slate-900 tracking-tighter leading-none">
                    สวัสดี, <span className="text-transparent bg-clip-text bg-gradient-to-br from-blue-600 via-indigo-500 to-violet-500">{user?.name?.split(' ')[0] ?? 'User'}</span>
                </h1>
                <p className="text-slate-500 mt-4 text-lg max-w-xl font-medium leading-relaxed">
                    ระบบบริหารจัดการแบบบูรณาการ พร้อมเชื่อมโยงข้อมูลทุกภาคส่วนให้เป็นหนึ่งเดียว
                </p>
            </div> */}

            {/* Grid Modules: ใช้ Bento Grid Style */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 animate-in fade-in zoom-in-95 duration-1000 delay-200">
                {modules.length > 0 ? (
                    modules.map((module) => {
                        const IconComponent = (Icons as any)[module.icon] ?? Icons.LayoutGrid;

                        return (
                            <Link
                                href={module.is_active ? module.route : '#'}
                                key={module.id}
                                className={cn(
                                    "group relative block h-full",
                                    !module.is_active && "opacity-40 cursor-not-allowed pointer-events-none"
                                )}
                            >
                                {/* เอฟเฟกต์แสงเงาด้านหลัง (Glow Effect) */}
                                <div className={cn(
                                    "absolute -inset-0.5 rounded-[2.5rem] opacity-0 group-hover:opacity-20 blur transition duration-500",
                                    "bg-gradient-to-br from-primary to-blue-500"
                                )} />

                                <Card className="relative h-full border-none shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] group-hover:shadow-[0_20px_40px_-15px_rgba(0,0,0,0.1)] transition-all duration-500 bg-white/80 backdrop-blur-xl flex flex-col p-8 rounded-[2.5rem] overflow-hidden ring-1 ring-slate-200/50 group-hover:ring-primary/20">

                                    {/* Decorative Watermark Icon */}
                                    <IconComponent
                                        className="absolute -right-6 -bottom-6 text-slate-100/50 group-hover:text-primary/10 transition-all duration-700 group-hover:scale-110 group-hover:-rotate-12"
                                        size={180}
                                        strokeWidth={0.5}
                                    />

                                    <div className="relative z-10 flex flex-col h-full">
                                        {/* Icon Box */}
                                        <div className={cn(
                                            "w-16 h-16 rounded-[1.25rem] flex items-center justify-center mb-8 transition-all duration-500 group-hover:scale-110 group-hover:rotate-6 shadow-xl shadow-slate-200 group-hover:shadow-primary/20",
                                            "bg-gradient-to-br from-white to-slate-50 text-slate-700 group-hover:from-primary group-hover:to-blue-600 group-hover:text-white"
                                        )}>
                                            <IconComponent size={30} strokeWidth={2.2} />
                                        </div>

                                        {/* Content Area - ปรับให้ยืดหยุ่นเต็มการ์ดเพราะไม่มี Interaction Bar แล้ว */}
                                        <div className="flex-1 flex flex-col">
                                            <div className="flex items-center justify-between mb-3">
                                                <h3 className="text-xl font-bold text-slate-900 group-hover:text-primary transition-colors tracking-tight">
                                                    {module.name}
                                                </h3>

                                                {module.badge_count > 0 && (
                                                    <div className="relative flex h-6 w-6">
                                                        <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                                        <Badge className="relative bg-red-500 text-white border-none h-6 min-w-[24px] px-1.5 flex justify-center items-center rounded-full text-[10px] font-black">
                                                            {module.badge_count > 99 ? '99+' : module.badge_count}
                                                        </Badge>
                                                    </div>
                                                )}
                                            </div>
                                            <p className="text-sm text-slate-500 leading-relaxed font-medium group-hover:text-slate-600 transition-colors">
                                                {module.description}
                                            </p>
                                        </div>
                                    </div>
                                </Card>
                            </Link>
                        );
                    })
                ) : (
                    <div className="col-span-full flex flex-col items-center justify-center py-32 bg-white rounded-[3rem] border border-slate-100 shadow-inner">
                        <div className="relative mb-6">
                            <div className="absolute inset-0 bg-primary/20 blur-3xl rounded-full" />
                            <Icons.Orbit size={80} className="text-slate-200 animate-[spin_10s_linear_infinite]" />
                        </div>
                        <p className="text-2xl font-black text-slate-900 tracking-tighter">ไม่มีโมดูลที่พร้อมใช้งาน</p>
                        <p className="text-slate-400 mt-2 font-medium">สิทธิ์การเข้าถึงของคุณกำลังอยู่ในการตรวจสอบ</p>
                    </div>
                )}
            </div>

            {/* Bottom System Bar */}
            {/* <div className="mt-20 flex flex-col md:flex-row items-center justify-between p-6 bg-slate-900/5 rounded-[2rem] border border-white/50 backdrop-blur-sm">
                <div className="flex items-center space-x-6 mb-4 md:mb-0">
                    <div className="flex items-center px-4 py-2 bg-white rounded-full shadow-sm border border-slate-100 text-[10px] font-black uppercase tracking-widest text-green-600">
                        <span className="w-2 h-2 rounded-full bg-green-500 mr-2 shadow-[0_0_8px_rgba(34,197,94,0.6)] animate-pulse" />
                        {systemMessage}
                    </div>
                    <div className="text-[11px] font-bold text-slate-400 flex items-center">
                        <Icons.Clock size={14} className="mr-2" />
                        Last Sync: {new Date().toLocaleTimeString()}
                    </div>
                </div>

                <div className="flex items-center space-x-8">
                    <div className="text-right">
                        <p className="text-[9px] font-black text-slate-400 uppercase tracking-widest">Platform</p>
                        <p className="text-xs font-bold text-slate-800">ECOSYSTEM TMR v12.4</p>
                    </div>
                    <div className="h-8 w-px bg-slate-200" />
                    <div className="text-right font-black text-xs text-slate-900">
                        <span className="bg-primary/10 text-primary px-3 py-1.5 rounded-lg border border-primary/10">
                            DDD ENGINE ACTIVE
                        </span>
                    </div>
                </div>
            </div> */}
        </AppPanelLayout>
    );
}
