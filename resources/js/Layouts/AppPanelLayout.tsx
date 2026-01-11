import React, { PropsWithChildren, ReactNode } from 'react';
import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link, usePage } from '@inertiajs/react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger
} from '@/Components/ui/dropdown-menu';
import { Avatar, AvatarFallback } from '@/Components/ui/avatar';
import { LogOut, User, Settings, LayoutGrid } from 'lucide-react';

export default function AppPanelLayout({ children }: PropsWithChildren) {
    const user = usePage().props.auth.user;

    return (
        <div className="min-h-screen bg-slate-50/50 flex flex-col">
            {/* Top Navigation Bar */}
            <nav className="bg-white border-b border-slate-200 sticky top-0 z-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex items-center space-x-4">
                            <Link href="/" className="flex items-center space-x-2 group">
                                <div className="p-1.5 bg-primary rounded-lg group-hover:rotate-12 transition-transform">
                                    <ApplicationLogo className="block h-6 w-auto fill-current text-white" />
                                </div>
                                <span className="text-xl font-bold tracking-tight text-slate-900">
                                    EcoSystem <span className="text-primary">TMR</span>
                                </span>
                            </Link>
                        </div>

                        <div className="flex items-center space-x-4">
                            {/* Profile Dropdown */}
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <button className="flex items-center space-x-3 p-1 pr-3 rounded-full hover:bg-slate-100 transition-colors focus:outline-none">
                                        <Avatar className="h-8 w-8 border border-slate-200">
                                            <AvatarFallback className="bg-primary/10 text-primary text-xs">
                                                {user.name.charAt(0).toUpperCase()}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div className="hidden md:block text-left">
                                            <p className="text-sm font-medium text-slate-700 leading-none">{user.name}</p>
                                            <p className="text-[10px] text-slate-500 uppercase tracking-wider mt-1">Administrator</p>
                                        </div>
                                    </button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" className="w-56">
                                    <DropdownMenuLabel>บัญชีผู้ใช้</DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem asChild>
                                        <Link href={route('profile.edit')} className="cursor-pointer w-full flex items-center">
                                            <User className="mr-2 h-4 w-4" /> โปรไฟล์
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem>
                                        <Settings className="mr-2 h-4 w-4" /> ตั้งค่าระบบ
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem asChild className="text-destructive focus:text-destructive">
                                        <Link href={route('logout')} method="post" as="button" className="w-full flex items-center">
                                            <LogOut className="mr-2 h-4 w-4" /> ออกจากระบบ
                                        </Link>
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </div>
            </nav>

            {/* Main Content Area */}
            <main className="flex-1 overflow-y-auto">
                <div className="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                    {children}
                </div>
            </main>

            {/* Footer */}
            <footer className="py-6 text-center text-slate-400 text-xs border-t border-slate-100">
                &copy; {new Date().getFullYear()} TMR EcoSystem. All rights reserved.
            </footer>
        </div>
    );
}
