import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import Dropdown from '@/Components/Dropdown';

export default function SaleNavigation() {
    return (
        <>
            {/* สำหรับ Desktop จะเรียงแนวนอน | สำหรับ Mobile จะเรียงแนวตั้งโดยอัตโนมัติจาก Layout */}
            <NavLink href={route('sales.orders.index')} active={route().current('sales.orders.index')}>
                Overview
            </NavLink>

            <div className="inline-flex items-center">
                <Dropdown>
                    <Dropdown.Trigger>
                        <button className="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                            Orders
                        </button>
                    </Dropdown.Trigger>
                    <Dropdown.Content>
                        <Dropdown.Link href={route('sales.orders.index')}>Quotation</Dropdown.Link>
                        <Dropdown.Link href={route('sales.orders.index')}>Sale Order</Dropdown.Link>
                    </Dropdown.Content>
                </Dropdown>
            </div>

            <NavLink href={route('sales.catalog.gallery')}  active={route().current('sales.catalog.gallery')}>Gallery</NavLink>
            <NavLink href={route('sales.catalog.prices.index')}  active={route().current('sales.catalog.prices.index')}>Price List</NavLink>
        </>
    );
}
