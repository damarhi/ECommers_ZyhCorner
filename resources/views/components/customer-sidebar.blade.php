@props(['active' => 'dashboard'])

<div class="col-lg-3 col-md-4 mb-4 customer-sidebar-col">
    <div class="sidebar-navigation">
        <div class="sidebar-title">Navigation</div>
        <ul class="nav-menu-list">
            <li class="nav-menu-item {{ $active === 'dashboard' ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}">
                    <i class="fa-solid fa-table-cells-large"></i> Dashboard
                </a>
            </li>
            <li class="nav-menu-item {{ in_array($active, ['order.history', 'orders', 'order_history', 'order_detail', 'return_detail']) ? 'active' : '' }}">
                <a href="{{ route('order.history') }}">
                    <i class="fa-solid fa-clock-rotate-left"></i> Order History
                </a>
            </li>
            <li class="nav-menu-item {{ in_array($active, ['cart', 'userChart']) ? 'active' : '' }}">
                <a href="{{ route('userChart') }}">
                    <i class="fa-solid fa-bag-shopping"></i> Shopping Cart
                </a>
            </li>
            <li class="nav-menu-item {{ $active === 'settings' ? 'active' : '' }}">
                <a href="{{ route('settings') }}">
                    <i class="fa-solid fa-gear"></i> Settings
                </a>
            </li>
            <li class="nav-menu-item">
                <a href="#" onclick="event.preventDefault(); confirmLogout();">
                    <i class="fa-solid fa-right-from-bracket"></i> Log-out
                </a>
            </li>
        </ul>
    </div>
</div>
