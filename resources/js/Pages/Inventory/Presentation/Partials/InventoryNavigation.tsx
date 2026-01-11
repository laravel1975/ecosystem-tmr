import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import Dropdown from '@/Components/Dropdown';

export default function InventoryNavigation() {
    return (
        <>
            {/* สำหรับ Desktop จะเรียงแนวนอน | สำหรับ Mobile จะเรียงแนวตั้งโดยอัตโนมัติจาก Layout */}
            <NavLink href={route('inventory.dashboard')} active={route().current('inventory.dashboard')}>
                Overview
            </NavLink>

            <div className="inline-flex items-center">
                <Dropdown>
                    <Dropdown.Trigger>
                        <button className="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                            Operations
                        </button>
                    </Dropdown.Trigger>
                    <Dropdown.Content>
                        <Dropdown.Link href={route('inventory.ops.index', 'incoming')}>Receipts</Dropdown.Link>
                        <Dropdown.Link href={route('inventory.ops.index', 'outgoing')}>Delivery</Dropdown.Link>
                    </Dropdown.Content>
                </Dropdown>
            </div>
        </>
    );
}
