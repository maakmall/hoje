@extends('layouts.main')

@section('content')
    @include('partials.navbar')

    <div id="fh5co-menus">
        <div class="container">
            <div class="row text-center fh5co-heading row-padded">
                <div class="col-md-8 col-md-offset-2 col-md-offset-2">
                    <h2 class="heading to-animate">Checkout</h2>
                </div>
            </div>
            <div class="row row-padded">
                <div class="col-md-8">
                    <div class="fh5co-food-menu to-animate-2">
                        <h2 class="fh5co-drinks">Menu</h2>
                        <ul id="cartList"></ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <h2 style="margin-top: 10px; margin-bottom: 20px;">Total</h2>
                            <h3 style="margin-top: 10px; margin-bottom: 20px;" id="totalAmount"></h3>

                            <div class="form-group">
                                <label for="paymentMethod" class="control-label">
                                    Payment Method
                                </label>
                                <p id="paymentMethodName" style="display: none;"></p>
                                <select name="payment_method" class="form-control" id="paymentMethod" required>
                                    <option value="">-- Select Payment Method --</option>
                                    <option value="qris">QRIS</option>
                                    <option value="transfer">Transfer BCA</option>
                                </select>
                            </div>
                            <div id="payment"></div>

                            <button type="button" class="btn btn-primary btn-block" id="pay">Pay</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script>
        const cartKey = 'hoje_cart'

        function formatRupiah(angka) {
            return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
        }

        function loadCart() {
            const cart = JSON.parse(localStorage.getItem(cartKey) || '[]')

            if (cart.length === 0) {
                location.href = '/menus'
                return
            }

            const $cartList = $('#cartList')
            $cartList.empty()

            cart.forEach((item, index) => {
                const variantText = item.variant ?
                    ` (${item.variant.charAt(0).toUpperCase() + item.variant.slice(1)})` : ''

                const itemHTML = `
            <li data-index="${index}" style="cursor: default;">
                <div class="fh5co-food-desc">
                    <div>
                        <h3>${item.menu_name}${variantText}</h3>
                        <p>
                            <button class="btn btn-xs btn-default btn-decrease">-</button>
                            <span class="cart-qty" style="margin: 0 10px;">${item.qty}</span>
                            <button class="btn btn-xs btn-default btn-increase">+</button>
                            x ${formatRupiah(item.price)}
                        </p>
                    </div>
                </div>
                <div class="fh5co-food-pricing">
                    <div>${formatRupiah(item.subtotal_price)}</div>
                </div>
            </li>
        `
                $cartList.append(itemHTML)
            })
        }

        function updateQty(index, change) {
            let cart = JSON.parse(localStorage.getItem(cartKey) || '[]')

            if (!cart[index]) return

            cart[index].qty += change
            cart[index].subtotal_price = cart[index].price * cart[index].qty

            if (cart[index].qty <= 0) {
                cart.splice(index, 1)
            }

            localStorage.setItem(cartKey, JSON.stringify(cart))
            loadCart()
            updateTotalAmount()
        }

        $(document).on('click', '.btn-decrease', function() {
            const index = $(this).closest('li').data('index')
            updateQty(index, -1)
        })

        $(document).on('click', '.btn-increase', function() {
            const index = $(this).closest('li').data('index')
            updateQty(index, 1)
        })

        function updateTotalAmount() {
            const cart = JSON.parse(localStorage.getItem(cartKey) || '[]')
            const total = cart.reduce((sum, item) => sum + item.subtotal_price, 0)
            $('#totalAmount').text(`Rp. ${formatRupiah(total)}`)
        }

        $('#pay').click(function() {
            const paymentMethod = $('#paymentMethod').val()

            if (!paymentMethod) {
                alert('Please select a payment method')
                return
            }

            // Hide select dropdown & show text label
            $('#paymentMethod').hide()
            $('#paymentMethodName')
                .show()
                .text(paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1))

            // Ganti konten #payment sesuai metode
            if (paymentMethod === 'qris') {
                $('#payment').html(`
                    <div class="text-center">
                        <p style="margin: 0;">Scan QRIS to make payment</p>
                        <img src="https://api.sandbox.midtrans.com/v2/gopay/8824517e-4fa7-4731-a213-80ce823836e6/qr-code" width="100%">
                    </div>
                `)
            } else if (paymentMethod === 'transfer') {
                $('#payment').html(`
                    <div>
                        <p style="margin-bottom: 20px;">Please transfer to the following virtual account number:</p>
                        <div class="text-center" style="margin: 10px 0;">
                            <h4 style="display: inline;" id="vaNumber">1234567890</h4>
                            <button id="copyVaBtn" style="border: none; background: none; cursor: pointer; margin-left: 5px;">
                                <i class="icon-copy"></i>
                            </button>
                        </div>
                        <small id="copySuccess" style="display: none; color: green; text-align: center;">Copied!</small>
                    </div>
                `)
            }

            // Sembunyiin tombol dan ganti dengan input file buat upload bukti pembayaran
            $('#pay').hide().after(`
                <div class="form-group" style="margin-top: 15px;">
                    <label for="paymentProof">Upload Proof Payment</label>
                    <input type="file" class="form-control" id="paymentProof" name="payment_proof" accept="image/*">
                </div>
            `)
        })

        // Delegate karena elemen baru muncul setelah klik "Pay"
        $(document).on('click', '#copyVaBtn', function() {
            const vaNumber = $('#vaNumber').text()

            // Buat textarea sementara buat copy
            const tempInput = $('<input>')
            $('body').append(tempInput)
            tempInput.val(vaNumber).select()
            document.execCommand('copy')
            tempInput.remove()

            // Tampilkan pesan copied
            $('#copySuccess').fadeIn(200).delay(1000).fadeOut(200)
        })


        $(document).ready(function() {
            loadCart()
            updateTotalAmount()
        })
    </script>
@endpush
