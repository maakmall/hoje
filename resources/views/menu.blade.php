@extends('layouts.main')

@section('content')
    @include('partials.navbar')
    <div id="fh5co-menus">
        <div class="container">
            <div class="row text-center fh5co-heading row-padded">
                <div class="col-md-8 col-md-offset-2 col-md-offset-2">
                    <h2 class="heading to-animate">Menu</h2>
                    <p class="sub-heading to-animate">
                        Our delicious menu is made with the freshest ingredients and prepared with care. We take pride in
                        offering a wide variety of dishes to satisfy.
                    </p>
                </div>
            </div>
            <div class="row row-padded">
                <div class="col-md-8 col-md-offset-2">
                    <div class="fh5co-food-menu to-animate-2">
                        <h2 class="fh5co-drinks">Beverage</h2>
                        <ul>
                            @foreach ($beverages as $beverage)
                                <li style="cursor: pointer;" data-menu="{{ json_encode($beverage) }}">
                                    <div class="fh5co-food-desc">
                                        <figure>
                                            <img src="{{ $beverage->image }}" class="img-responsive">
                                        </figure>
                                        <div>
                                            <h3>{{ $beverage->name }}</h3>
                                            <p>{{ $beverage->description ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="fh5co-food-pricing">
                                        @foreach ($beverage->prices as $price)
                                            <div>
                                                {{ $price->variant_beverage ? "({$price->variant_beverage->name})" : '' }}
                                                {{ App\Helpers\Numeric::rupiah($price->price) }}
                                            </div>
                                        @endforeach
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-md-8 col-md-offset-2">
                    <div class="fh5co-food-menu to-animate-2">
                        <h2 class="fh5co-dishes">Food</h2>
                        <ul>
                            @foreach ($foods as $food)
                                <li style="cursor: pointer;" data-menu="{{ json_encode($food) }}">
                                    <div class="fh5co-food-desc">
                                        <figure>
                                            <img src="{{ $food->image }}" class="img-responsive">
                                        </figure>
                                        <div>
                                            <h3>{{ $food->name }}</h3>
                                            <p>{{ $food->description ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="fh5co-food-pricing">
                                        {{ App\Helpers\Numeric::rupiah($food->prices->first()->price) }}
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
@endpush

@push('scripts')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        const cartKey = 'hoje_cart'

        function addToCart(item) {
            let cart = JSON.parse(localStorage.getItem(cartKey) || '[]')

            const existingIndex = cart.findIndex(cartItem =>
                cartItem.menu_id == item.menu_id && cartItem.variant == item.variant
            )

            if (existingIndex !== -1) {
                // Kalo udah ada, update qty & subtotal
                cart[existingIndex].qty += item.qty
                cart[existingIndex].subtotal_price = cart[existingIndex].qty * cart[existingIndex].price
            } else {
                // Kalo belum ada, push item baru
                cart.push(item)
            }

            localStorage.setItem(cartKey, JSON.stringify(cart))
        }


        $(document).ready(function() {
            updateCartCount()
            // Open Modal in menu list
            $('li[data-menu]').click(function() {
                const menu = JSON.parse($(this).attr('data-menu'))
                $('#menuName').text(menu.name)
                $('#menuName').attr('data-price', JSON.stringify(menu.prices))
                $('#menuId').val(menu.id)
                $('#modalQty').val(1)
                $('#modalVariant').val(null)

                if (menu.prices.length == 1) {
                    $('#modalVariantWrapper').hide()
                    $('#modalQtyWrapper').removeClass('col-xs-6').addClass('col-xs-12')
                } else {
                    $('#modalVariantWrapper').show()
                    $('#modalQtyWrapper').removeClass('col-xs-12').addClass('col-xs-6')
                }

                $('#menuModal').modal('show')
            })

            $('#addToCart').click(function() {
                const menuId = $('#menuId').val()
                const menuName = $('#menuName').text()
                const menuPrices = JSON.parse($('#menuName').attr('data-price'))
                const qty = parseInt($('#modalQty').val())
                const variant = $('#modalVariant').is(':visible') ? $('#modalVariant').val() : null
                const price = menuPrices.length > 1 ?
                    menuPrices.find(p => p.variant_beverage == variant).price :
                    menuPrices[0].price
                console.log(menuPrices, variant, price);

                addToCart({
                    menu_id: menuId,
                    menu_name: menuName,
                    price,
                    qty,
                    subtotal_price: price * qty,
                    variant
                })

                $('#menuModal').modal('hide')
                updateCartCount()

                Toastify({
                    text: "Added to cart",
                    position: "center",
                    style: {
                        background: "linear-gradient(to right, #00b09b, #96c93d)"
                    },
                    duration: 3000
                }).showToast();
            })
        })

        function updateCartCount() {
            let cart = JSON.parse(localStorage.getItem(cartKey) || '[]')
            let total = cart.reduce((sum, item) => sum + item.qty, 0)
            $('.checkout span').text(total > 0 ? total : '')
        }
    </script>
@endpush
