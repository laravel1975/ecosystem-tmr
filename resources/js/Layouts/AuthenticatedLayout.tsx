import React, { useState, PropsWithChildren, ReactNode } from 'react';
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
import {
    LogOut,
    User as UserIcon,
    Settings,
    Menu,
    X,
    LayoutGrid,
    ChevronDown
} from 'lucide-react';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';

interface Props {
    header?: ReactNode;
    navigation?: ReactNode; // รับเมนูจากแต่ละ Bounded Context (BC)
}

export default function Authenticated({
    header,
    navigation,
    children,
}: PropsWithChildren<Props>) {
    const user = usePage().props.auth.user;
    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);

    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-900 flex flex-col">
            {/* Top Navigation Bar */}
            <nav className="bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 sticky top-0 z-50 shadow-sm">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex w-full">
                            {/* Logo */}
                            <div className="flex shrink-0 items-center">
                                <Link href={route('dashboard')} className="group flex items-center space-x-2">
                                    <div className="p-1.5 bg-primary rounded-lg group-hover:rotate-12 transition-transform shadow-sm">
                                        <ApplicationLogo className="block h-6 w-auto fill-current text-white" />
                                    </div>
                                    <span className="hidden md:block text-lg font-bold tracking-tight text-slate-900 dark:text-white">
                                        EcoSystem <span className="text-primary">TMR</span>
                                    </span>
                                </Link>
                            </div>

                            {/* Dynamic Navigation - Desktop (เมนูเปลี่ยนไปตาม BC) */}
                            <div className="hidden space-x-4 sm:-my-px sm:ms-10 sm:flex items-center">
                                {navigation}
                            </div>
                        </div>

                        <div className="flex items-center space-x-2 md:space-x-4">
                            {/* Profile Dropdown - Desktop (UI แบบเดียวกับ AppPanelLayout) */}
                            <div className="hidden sm:flex sm:items-center">
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <button className="flex items-center space-x-3 p-1 pr-3 rounded-full hover:bg-slate-100 dark:hover:bg-gray-700 transition-colors focus:outline-none border border-transparent hover:border-gray-200 dark:hover:border-gray-600">
                                            <Avatar className="h-8 w-8 border border-slate-200 dark:border-gray-600 shadow-sm">
                                                <AvatarFallback className="bg-primary/10 text-primary text-xs font-bold">
                                                    {user.name.charAt(0).toUpperCase()}
                                                </AvatarFallback>
                                            </Avatar>
                                            <div className="text-left">
                                                <p className="text-sm font-medium text-slate-700 dark:text-gray-200 leading-none">
                                                    {user.name}
                                                </p>
                                                <p className="text-[10px] text-slate-500 uppercase tracking-wider mt-1 font-semibold">Admin</p>
                                            </div>
                                            <ChevronDown size={14} className="text-slate-400" />
                                        </button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" className="w-56">
                                        <DropdownMenuLabel>การจัดการบัญชี</DropdownMenuLabel>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem asChild>
                                            <Link href={route('profile.edit')} className="cursor-pointer w-full flex items-center">
                                                <UserIcon className="mr-2 h-4 w-4" /> โปรไฟล์ส่วนตัว
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem asChild>
                                            <Link href={route('dashboard')} className="cursor-pointer w-full flex items-center">
                                                <LayoutGrid className="mr-2 h-4 w-4" /> แผงควบคุมหลัก (Launcher)
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem asChild className="text-destructive focus:text-destructive focus:bg-destructive/10 cursor-pointer">
                                            <Link href={route('logout')} method="post" as="button" className="w-full flex items-center text-left">
                                                <LogOut className="mr-2 h-4 w-4" /> ออกจากระบบ
                                            </Link>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>

                            {/* Mobile Menu Button - Hamburger */}
                            <div className="flex items-center sm:hidden">
                                <button
                                    onClick={() => setShowingNavigationDropdown((prev) => !prev)}
                                    className="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out dark:hover:bg-gray-700"
                                >
                                    {showingNavigationDropdown ? (
                                        <X className="block h-6 w-6 animate-in fade-in zoom-in" />
                                    ) : (
                                        <Menu className="block h-6 w-6 animate-in fade-in zoom-in" />
                                    )}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Mobile Navigation Menu */}
                <div className={(showingNavigationDropdown ? 'block' : 'hidden') + ' sm:hidden border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 animate-in slide-in-from-top-2 duration-300'}>
                    <div className="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink href={route('dashboard')} active={route().current('dashboard')}>
                            <div className="flex items-center font-bold">
                                <LayoutGrid size={18} className="mr-2 text-primary" /> แผงควบคุมหลัก
                            </div>
                        </ResponsiveNavLink>

                        <div className="px-4 py-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-t border-gray-200 dark:border-gray-700 mt-2">
                            Module Navigation
                        </div>
                        {/* แสดงผลเมนูของ BC ในเวอร์ชัน Mobile */}
                        <div className="px-2 space-y-1">
                             {navigation}
                        </div>
                    </div>

                    <div className="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700">
                        <div className="flex items-center px-4">
                            <Avatar className="h-10 w-10 border border-slate-200 shadow-sm">
                                <AvatarFallback className="bg-primary/10 text-primary font-bold">
                                    {user.name.charAt(0).toUpperCase()}
                                </AvatarFallback>
                            </Avatar>
                            <div className="ms-3">
                                <div className="text-base font-bold text-gray-800 dark:text-gray-200 leading-none mb-1">{user.name}</div>
                                <div className="text-xs font-medium text-gray-500">{user.email}</div>
                            </div>
                        </div>

                        <div className="mt-4 space-y-1">
                            <ResponsiveNavLink href={route('profile.edit')}>
                                <div className="flex items-center"><UserIcon size={18} className="mr-2" /> โปรไฟล์</div>
                            </ResponsiveNavLink>
                            <ResponsiveNavLink method="post" href={route('logout')} as="button">
                                <div className="flex items-center text-destructive font-semibold"><LogOut size={18} className="mr-2" /> ออกจากระบบ</div>
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            {/* Page Header Area */}
            {header && (
                <header className="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-100 dark:border-gray-700 relative z-40">
                    <div className="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}

            {/* Main Content Area */}
            <main className="flex-1 overflow-y-auto py-8 bg-slate-50/30 dark:bg-slate-900/30">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 animate-in fade-in duration-700">
                    {children}
                </div>
            </main>

            {/* Fixed Bottom Footer */}
            <footer className="py-4 text-center text-slate-400 text-[10px] border-t border-gray-200 dark:bg-gray-800 dark:border-gray-700 bg-white font-bold uppercase tracking-widest">
                &copy; {new Date().getFullYear()} TMR EcoSystem Engine v12.0. All rights reserved.
            </footer>
        </div>
    );
}
