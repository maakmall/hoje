@extends('layouts.main')

@section('content')
    @include('partials.navbar')
    <div id="fh5co-contact" data-section="reservation">
        <div class="container">
            <div class="row text-center fh5co-heading row-padded">
                <div class="col-md-8 col-md-offset-2">
                    <h2 class="heading to-animate">Reserve a Table</h2>
                    <p class="sub-heading to-animate">
                        We are here to serve you the best food and drinks. Please fill out the form below to reserve a
                        table.
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8 to-animate-2 col-md-offset-2">
                    <h3>Reservation Form</h3>
                    <div class="form-group">
                        <label for="name" class="sr-only">Name</label>
                        <input id="name" class="form-control" placeholder="Name" type="text" required>
                    </div>
                    <div class="row">
                        <div class="col-xs-6" style="padding-right: 7.5px;">
                            <div class="form-group">
                                <label for="date" class="sr-only">Date</label>
                                <input id="date" class="form-control" placeholder="Date &amp; Time" type="text"
                                    required>
                            </div>
                        </div>
                        <div class="col-xs-6" style="padding-left: 7.5px;">
                            <div class="form-group">
                                <label for="numberOfPeople" class="sr-only">Number of People</label>
                                <input id="numberOfPeople" class="form-control" placeholder="Number of People"
                                    type="number" min="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="location" class="sr-only">Location</label>
                        <select class="form-control" id="location" name="location_id" required>
                            <option>Select a Location</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group ">
                        <label for="message" class="sr-only">Note</label>
                        <textarea id="message" cols="30" rows="4" class="form-control" placeholder="Note"></textarea>
                    </div>
                </div>

                <div class="col-md-8 col-md-offset-2" id="fh5co-menus" style="padding: 0; padding-inline: 15px;">
                    <div class="fh5co-food-menu to-animate-2" style="margin-bottom: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h3 class="fh5co-drinks">Menu</h3>
                            <button class="btn btn-primary btn-outline" id="openMenuModal">
                                Select Menu
                            </button>
                        </div>
                        <ul id="cartList">
                            <div class="text-center" style="margin-top: 30px;">
                                Menu is empty
                            </div>
                        </ul>
                        <h3 style="font-style: normal; font-size: 30px; margin-bottom: 0;" class="text-right"
                            id="totalAmount">
                            Rp 0
                        </h3>
                        <small>Note: Payment at the time of reservation is a down payment (50%) of the total</small>
                    </div>
                </div>

                <div class="col-md-8 col-md-offset-2 to-animate-2">
                    <h3>Payment</h3>
                    <div class="form-group">
                        <label for="method" class="sr-only">Method</label>
                        <p id="totalDownPayment"></p>
                        <p id="paymentMethodName" style="display: none;"></p>
                        <select class="form-control" id="method" required>
                            <option value="">Select a Payment Method</option>
                            <option value="qris">QRIS</option>
                            <option value="transfer">Transfer BCA</option>
                        </select>
                    </div>
                    <div id="payment"></div>
                </div>

                <div class="col-md-4 col-md-offset-4 text-center to-animate-2 fadeIn animated" style="margin-top: 30px;">
                    <p style="margin-bottom: 0;">
                        <button class="btn btn-primary" id="makeReservation">Submit Reservation</button>
                    </p>
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
        $(document).ready(function() {
            // Buka modal
            $('#openMenuModal').click(function() {
                $('#menuId').val(null)
                $('#modalQty').val(1)
                $('#modalVariant').val(null)

                $('#reservationMenuModal').modal('show')

                updateVariantVisibility()
            })

            // Ketika menu diganti di select, cek varian
            $('#menuId').change(function() {
                updateVariantVisibility()
            })

            function updateVariantVisibility() {
                const selectedOption = $('#menuId option:selected')
                const prices = JSON.parse(selectedOption.attr('data-price') || '[]')

                if (prices.length <= 1) {
                    $('#modalVariantWrapper').hide()
                    $('#modalQtyWrapper').removeClass('col-xs-6').addClass('col-xs-12')
                } else {
                    $('#modalVariantWrapper').show()
                    $('#modalQtyWrapper').removeClass('col-xs-12').addClass('col-xs-6')
                }
            }

            // Klik tombol "Add Menu" di modal
            $('#addToCart').click(function() {
                const menuId = $('#menuId').val()
                const menuText = $('#menuId option:selected').text()
                const rawName = menuText.split(') ')[1] || menuText // Buat ngambil nama menu doang
                const prices = JSON.parse($('#menuId option:selected').attr('data-price') || '[]')
                const variant = prices.length > 1 ? $('#modalVariant').val() : null
                const qty = parseInt($('#modalQty').val()) || 1

                if (!menuId || qty < 1 || (prices.length > 1 && !variant)) {
                    Toastify({
                        text: "Lengkapi data menu sebelum menambah!",
                        position: "center",
                        style: {
                            background: "linear-gradient(to right, #FF5F6D, #FFC371)",
                        },
                        duration: 3000
                    }).showToast()
                    return
                }

                const cart = JSON.parse(localStorage.getItem('hoje_reservation_menu') || '[]')

                const price = prices.length > 1 ?
                    prices.find(p => p.variant_beverage == variant).price :
                    prices[0].price

                // Cek apakah menu dengan ID + variant udah ada
                const existingIndex = cart.findIndex(item =>
                    item.menu_id === menuId && item.variant === variant
                )

                if (existingIndex > -1) {
                    // Kalau udah ada, update qty & subtotal
                    cart[existingIndex].qty += qty
                    cart[existingIndex].subtotal_price = cart[existingIndex].qty * price
                } else {
                    // Kalau belum ada, push baru
                    cart.push({
                        menu_id: menuId,
                        menu_name: rawName,
                        price,
                        qty,
                        subtotal_price: price * qty,
                        variant
                    })
                }

                localStorage.setItem('hoje_reservation_menu', JSON.stringify(cart))

                renderCart()
                $('#reservationMenuModal').modal('hide')
            })

            $(document).on('click', '.btn-decrease', function() {
                const index = $(this).closest('li').data('index')
                updateQty(index, -1)
            })

            $(document).on('click', '.btn-increase', function() {
                const index = $(this).closest('li').data('index')
                updateQty(index, 1)
            })

            function updateQty(index, diff) {
                const cart = JSON.parse(localStorage.getItem('hoje_reservation_menu') || '[]')
                if (cart[index]) {
                    cart[index].qty += diff

                    // Kalau qty jadi 0 atau kurang, hapus item
                    if (cart[index].qty <= 0) {
                        cart.splice(index, 1)
                    } else {
                        cart[index].subtotal_price = cart[index].qty * cart[index].price
                    }

                    localStorage.setItem('hoje_reservation_menu', JSON.stringify(cart))
                    renderCart()
                }
            }

            function updateTotalAmount() {
                const cart = JSON.parse(localStorage.getItem('hoje_reservation_menu') || '[]')
                const total = cart.reduce((sum, item) => sum + item.subtotal_price, 0)
                $('#totalAmount').text(`Rp. ${formatRupiah(total)}`)
                $('#totalDownPayment').text(`Down Payment: Rp ${formatRupiah(total / 2)}`)
            }

            function renderCart() {
                const cart = JSON.parse(localStorage.getItem('hoje_reservation_menu') || '[]')
                const $cartList = $('#cartList')
                $cartList.empty()

                if (cart.length === 0) {
                    $cartList.html('<div class="text-center" style="margin-top: 30px;">Menu is empty</div>')
                    return
                }

                cart.forEach((item, index) => {
                    const variantText = item.variant ?
                        ` (${item.variant.charAt(0).toUpperCase() + item.variant.slice(1)})` : ''
                    $cartList.append(`
                        <li data-index="${index}" style="cursor: default;">
                            <div class="fh5co-food-desc">
                                <div>
                                    <h3 style="font-style: normal;">${item.menu_name}${variantText}</h3>
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
                    `)
                })

                updateTotalAmount()
            }

            $('#makeReservation').click(function() {
                $(this).attr('disabled', true).text('Processing...')

                const name = $('#name').val()
                const date = $('#date').val()
                const numberOfPeople = $('#numberOfPeople').val()
                const locationId = $('#location').val()
                const note = $('#message').val()
                const paymentMethod = $('#method').val()

                if (!name || !date || !numberOfPeople || !locationId || !paymentMethod) {
                    Toastify({
                        text: "Please complete all data before submitting!",
                        position: "center",
                        style: {
                            background: "linear-gradient(to right, #FF5F6D, #FFC371)",
                        },
                        duration: 3000
                    }).showToast()

                    $(this).attr('disabled', false).text('Submit Reservation')

                    return
                }

                const cart = JSON.parse(localStorage.getItem('hoje_reservation_menu') || '[]')

                if (cart.length === 0) {
                    Toastify({
                        text: "Please select at least one menu!",
                        position: "center",
                        style: {
                            background: "linear-gradient(to right, #FF5F6D, #FFC371)",
                        },
                        duration: 3000
                    }).showToast()

                    $(this).attr('disabled', false).text('Submit Reservation')

                    return
                }

                // Kirim data ke server (ganti URL sesuai kebutuhan)
                $.ajax({
                    url: '/reservations',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        customer_name: name,
                        date,
                        number_of_people: numberOfPeople,
                        location_id: locationId,
                        note,
                        payment_method: paymentMethod,
                        cart
                    },
                    success: function(response) {
                        $('#method').hide()
                        $('#paymentMethodName')
                            .show()
                            .text($('#method option:selected').text())

                        $('.btn-decrease').remove();
                        $('.btn-increase').remove();
                        $('#openMenuModal').remove();

                        const payment = response.payment

                        localStorage.setItem('hoje_reservation', JSON.stringify({
                            customer_name: name,
                            date: date,
                            number_of_people: numberOfPeople,
                            location_id: locationId,
                            note: note,
                            order_id: response.order_id,
                            payment: payment,
                            payment_method: paymentMethod,
                        }))

                        // Ganti konten #payment sesuai metode
                        if (paymentMethod === 'qris') {
                            $('#payment').html(`
                                <div class="text-center">
                                    <p style="margin: 0;">Scan QRIS to make payment</p>
                                    <img src="${payment.qr_url}" width="400">
                                </div>
                            `)
                        } else if (paymentMethod === 'transfer') {
                            $('#payment').html(`
                                <div>
                                    <p style="margin-bottom: 20px;">Please transfer to the following virtual account number:</p>
                                    <div class="text-center" style="margin: 10px 0;">
                                        <h4 style="display: inline; font-size: 30px" id="vaNumber">${payment.va_number}</h4>
                                        <button id="copyVaBtn" style="border: none; background: none; cursor: pointer; margin-left: 5px;">
                                            <i class="icon-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            `)
                        }

                        // Sembunyiin tombol dan ganti dengan input file buat upload bukti pembayaran
                        $('#makeReservation').hide().after(`
                            <form id="uploadForm" enctype="multipart/form-data">
                                <input type="hidden" name="order_id" value="${response.order_id}">
                                <div class="form-group" style="margin: 0;">
                                    <label for="paymentProof">Upload Payment Proof</label>
                                    <input type="file" class="form-control" name="payment_proof" accept="image/*" required>
                                    <button type="submit" class="btn btn-success btn-block" style="margin-top:10px;">Upload</button>
                                </div>
                            </form>
                        `)
                    },
                    error: function(error) {
                        Toastify({
                            text: error.responseJSON.message ||
                                "Failed to make reservation!",
                            position: "center",
                            style: {
                                background: "linear-gradient(to right, #FF5F6D, #FFC371)",
                            },
                            duration: 3000
                        }).showToast()

                        console.error(error)

                        $('#makeReservation').attr('disabled', false).text('Submit Reservation')
                    }
                })
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
                        localStorage.removeItem('hoje_reservation_menu')
                        localStorage.removeItem('hoje_reservation')

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

                        $('#uploadForm button[type="submit"]').attr('disabled', false).text(
                            'Upload')
                    }
                })
            })

            $(document).on('click', '#copyVaBtn', function() {
                const vaNumber = $('#vaNumber').text()
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(vaNumber).then(() => {
                        Toastify({
                            text: "Copied to clipboard!",
                            position: "center",
                            style: {
                                background: "linear-gradient(to right, #FF5F6D, #FFC371)",
                            },
                            duration: 3000
                        }).showToast();
                    });
                } else {
                    // Fallback
                    const tempInput = document.createElement('input');
                    tempInput.value = vaNumber;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);

                    Toastify({
                        text: "Copied to clipboard!",
                        position: "center",
                        style: {
                            background: "linear-gradient(to right, #FF5F6D, #FFC371)",
                        },
                        duration: 3000
                    }).showToast();
                }
            })

            function formatRupiah(angka) {
                return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
            }

            // Inisialisasi cart pas page load
            renderCart()

            const savedReservation = JSON.parse(localStorage.getItem('hoje_reservation') || 'null')
            if (savedReservation) {
                $('#name').val(savedReservation.customer_name)
                $('#date').val(savedReservation.date)
                $('#numberOfPeople').val(savedReservation.number_of_people)
                $('#location').val(savedReservation.location_id)
                $('#message').val(savedReservation.note)
                $('#makeReservation').hide()
                $('.btn-decrease').remove();
                $('.btn-increase').remove();
                $('#openMenuModal').remove();
                $('#method').hide()
                $('#paymentMethodName')
                    .show()
                    .text($('#method option[value="' + savedReservation.payment_method + '"]').text())

                const payment = savedReservation.payment

                if (savedReservation.payment_method === 'qris') {
                    $('#payment').html(`
                        <div class="text-center">
                            <p style="margin: 0;">Scan QRIS to make payment</p>
                            <img src="${payment.qr_url}" width="400">
                        </div>
                    `)
                } else if (savedReservation.payment_method === 'transfer') {
                    $('#payment').html(`
                        <div>
                            <p style="margin-bottom: 20px;">Please transfer to the following virtual account number:</p>
                            <div class="text-center" style="margin: 10px 0;">
                                <h4 style="display: inline; font-size: 30px" id="vaNumber">${payment.va_number}</h4>
                                <button id="copyVaBtn" style="border: none; background: none; cursor: pointer; margin-left: 5px;">
                                    <i class="icon-copy"></i>
                                </button>
                            </div>
                        </div>
                    `)
                }

                $('#makeReservation').after(`
                    <form id="uploadForm" enctype="multipart/form-data">
                        <input type="hidden" name="order_id" value="${savedReservation.order_id}">
                        <div class="form-group" style="margin: 0;">
                            <label for="paymentProof">Upload Payment Proof</label>
                            <input type="file" class="form-control" name="payment_proof" accept="image/*" required>
                            <button type="submit" class="btn btn-success btn-block" style="margin-top:10px;">Upload</button>
                        </div>
                    </form>
                `)
            }
        })
    </script>
@endpush
