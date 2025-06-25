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

                            <div class="form-group" style="margin-bottom: 5px;" id="tableNumberGroup"></div>
                            {{-- <div class="form-group">
                                <label for="paymentMethod" class="control-label">
                                    Payment Method
                                </label>
                                <p id="paymentMethodName" style="display: none;"></p>
                                <select name="payment_method" class="form-control" id="paymentMethod" required>
                                    <option value="">-- Select Payment Method --</option>
                                    <option value="qris">QRIS</option>
                                    <option value="transfer">Transfer BCA</option>
                                </select>
                            </div> --}}
                            <div id="payment"></div>

                            <button type="button" class="btn btn-primary btn-block" id="pay">Order</button>
                        </div>
                    </div>
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

        $('#pay').click(async function() {
            $(this).attr('disabled', true).text('Processing...')
            // const paymentMethod = $('#paymentMethod').val()
            const table = new URLSearchParams(window.location.search).get('table')
            const cart = JSON.parse(localStorage.getItem('hoje_cart') || '[]')

            if (cart.length === 0) {
                Toastify({
                    text: "Payment method & cart must be filled!",
                    position: "center",
                    style: {
                        background: "linear-gradient(to right, #FF5F6D, #FFC371)",
                    },
                    duration: 3000
                }).showToast();

                $(this).attr('disabled', false).text('Order')

                return
            }

            const payload = {
                _token: '{{ csrf_token() }}',
                table,
                // payment_method: paymentMethod,
                items: cart.map(item => ({
                    menu_id: item.menu_id,
                    qty: item.qty,
                    variant: item.variant
                }))
            }

            try {
                const res = await $.ajax({
                    url: '/orders',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(payload)
                })

                // const payment = res.payment
                
                // localStorage.setItem('hoje_order', JSON.stringify({
                //     order_id: res.order_id,
                //     payment_method: paymentMethod,
                //     payment: payment
                // }))

                // Hide select dropdown & show text label
                // $('#paymentMethod').hide()
                // $('#paymentMethodName')
                //     .show()
                //     .text(paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1))

                // Ganti konten #payment sesuai metode
                // if (paymentMethod === 'qris') {
                //     $('#payment').html(`
                //         <div class="text-center">
                //             <p style="margin: 0;">Scan QRIS to make payment</p>
                //             <img src="${payment.qr_url}" width="100%">
                //         </div>
                //     `)
                // } else if (paymentMethod === 'transfer') {
                //     $('#payment').html(`
                //         <div>
                //             <p style="margin-bottom: 20px;">Please transfer to the following virtual account number:</p>
                //             <div class="text-center" style="margin: 10px 0;">
                //                 <h4 style="display: inline;" id="vaNumber">${payment.va_number}</h4>
                //                 <button id="copyVaBtn" style="border: none; background: none; cursor: pointer; margin-left: 5px;">
                //                     <i class="icon-copy"></i>
                //                 </button>
                //             </div>
                //             <small id="copySuccess" style="display: none; color: green; text-align: center;">Copied!</small>
                //         </div>
                //     `)
                // }

                // Sembunyiin tombol dan ganti dengan input file buat upload bukti pembayaran
                // $('#pay').hide().after(`
                //     <form id="uploadForm" enctype="multipart/form-data">
                //         <input type="hidden" name="order_id" value="${res.order_id}">
                //         <div class="form-group" style="margin-top: 15px;">
                //             <label for="paymentProof">Upload Bukti Bayar</label>
                //             <input type="file" class="form-control" name="payment_proof" accept="image/*" required>
                //             <button type="submit" class="btn btn-success btn-block" style="margin-top:10px;">Upload</button>
                //         </div>
                //     </form>
                // `)

                Toastify({
                    text: "Order created successfully!",
                    position: "center",
                    style: {
                        background: "linear-gradient(to right, #FF5F6D, #FFC371)",
                    },
                    duration: 3000
                }).showToast();

                $(this).hide()

                localStorage.removeItem(cartKey)
                localStorage.removeItem('hoje_order')

            } catch (error) {
                $(this).attr('disabled', false).text('Order')

                Toastify({
                    text: "Failed to create order!",
                    position: "center",
                    style: {
                        background: "linear-gradient(to right, #FF5F6D, #FFC371)",
                    },
                    duration: 3000
                }).showToast();

                console.error(error)
            }
        })

        // Upload bukti bayar
        $(document).on('submit', '#uploadForm', function(e) {
            e.preventDefault()
            $('#uploadForm button[type="submit"]').attr('disabled', true).text('Uploading...')

            const formData = new FormData(this)
            formData.set('_token', '{{ csrf_token() }}')

            $.ajax({
                url: '/orders/upload-proof',
                method: 'POST',
                processData: false,
                contentType: false,
                data: formData,
                success: () => {
                    Toastify({
                        text: "Payment proof uploaded successfully!",
                        position: "center",
                        style: {
                            background: "linear-gradient(to right, #FF5F6D, #FFC371)",
                        },
                        duration: 3000
                    }).showToast();

                    // Kosongin cart dan data order
                    localStorage.removeItem(cartKey)
                    localStorage.removeItem('hoje_order')

                    $(this).remove()
                },
                error: () => {
                    Toastify({
                        text: "Failed to upload payment proof!",
                        position: "center",
                        style: {
                            background: "linear-gradient(to right, #FF5F6D, #FFC371)",
                        },
                        duration: 3000
                    }).showToast();

                    $('#uploadForm button[type="submit"]').attr('disabled', false).text('Upload')
                }
            })
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

        function setTableNumber() {
            const urlParams = new URLSearchParams(window.location.search);
            const tableNumber = urlParams.get('table');
            if (!tableNumber) return

            $('#tableNumberGroup').html(`
                <label for="name" class="control-label">
                    No. Meja
                </label>
                <p id="tableNumber" style="margin-bottom: 0;">#${tableNumber}</p>
            `)
        }

        $(document).ready(function() {
            const savedOrder = JSON.parse(localStorage.getItem('hoje_order'))

            if (savedOrder) {
                const {
                    order_id,
                    payment_method,
                    payment
                } = savedOrder

                $('#paymentMethod').hide()
                $('#paymentMethodName')
                    .show()
                    .text(payment_method.charAt(0).toUpperCase() + payment_method.slice(1))

                if (payment_method === 'qris') {
                    $('#payment').html(`
                        <div class="text-center">
                            <p style="margin: 0;">Scan QRIS to make payment</p>
                            <img src="${payment.qr_url}" width="100%">
                        </div>
                    `)
                } else if (payment_method === 'transfer') {
                    $('#payment').html(`
                        <div>
                            <p style="margin-bottom: 20px;">Please transfer to the following virtual account number:</p>
                            <div class="text-center" style="margin: 10px 0;">
                                <h4 style="display: inline;" id="vaNumber">${payment.va_number}</h4>
                                <button id="copyVaBtn" style="border: none; background: none; cursor: pointer; margin-left: 5px;">
                                    <i class="icon-copy"></i>
                                </button>
                            </div>
                            <small id="copySuccess" style="display: none; color: green; text-align: center;">Copied!</small>
                        </div>
                    `)
                }

                $('#pay').hide().after(`
                    <form id="uploadForm" enctype="multipart/form-data">
                        <input type="hidden" name="order_id" value="${order_id}">
                        <div class="form-group" style="margin-top: 15px;">
                            <label for="paymentProof">Upload Bukti Bayar</label>
                            <input type="file" class="form-control" name="payment_proof" accept="image/*" required>
                            <button type="submit" class="btn btn-success btn-block" style="margin-top:10px;">Upload</button>
                        </div>
                    </form>
                `)
            }

            loadCart()
            updateTotalAmount()
            setTableNumber()
        })
    </script>
@endpush
