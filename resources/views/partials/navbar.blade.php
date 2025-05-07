<div class="js-sticky" @style([
    'position: sticky' => request()->is('menus') || request()->is('reservations'),
    'top: 0' => request()->is('menus') || request()->is('reservations'),
    'z-index: 9999' => request()->is('menus') || request()->is('reservations'),
])>
    <div class="fh5co-main-nav">
        <div class="container-fluid">
            <div class="fh5co-menu-1">
                @if (request()->is('menus') || request()->is('reservations'))
                    <a href="/" class="external">Home</a>
                    <a href="/" class="external">Gallery</a>
                @else
                    <a href="#" data-nav-section="home">Home</a>
                    <a href="#" data-nav-section="gallery">Gallery</a>
                @endif
            </div>
            <div class="fh5co-logo">
                <a href="/">hoje</a>
            </div>
            <div class="fh5co-menu-2">
                <a href="/menus" @class(['external', 'active' => request()->is('menus')])>Menu</a>
                <a href="/reservations" @class(['external', 'active' => request()->is('reservations')])>Reservation</a>
            </div>
        </div>
    </div>
</div>
