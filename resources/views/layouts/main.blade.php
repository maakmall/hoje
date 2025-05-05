<!DOCTYPE html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $title }} - Hoje Coffee</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link
        href='https://fonts.googleapis.com/css?family=Playfair+Display:400,700,400italic,700italic|Merriweather:300,400italic,300italic,400,700italic'
        rel='stylesheet' type='text/css'>

    <!-- Animate.css -->
    <link rel="stylesheet" href="css/animate.css">
    <!-- Icomoon Icon Fonts-->
    <link rel="stylesheet" href="css/icomoon.css">
    <!-- Simple Line Icons -->
    <link rel="stylesheet" href="css/simple-line-icons.css">
    <!-- Datetimepicker -->
    <link rel="stylesheet" href="css/bootstrap-datetimepicker.min.css">
    <!-- Flexslider -->
    <link rel="stylesheet" href="css/flexslider.css">
    <!-- Bootstrap  -->
    <link rel="stylesheet" href="css/bootstrap.css">

    <link rel="stylesheet" href="css/style.css">

    <style>
        h3.with-icon.icon-drinks::before {
            background: url(../images/0203-coffee-love.png) no-repeat center center;
        }

        h3.with-icon.icon-4::before {
            background: url(../images/0401-vegan.png) no-repeat center center !important;
        }

        h3.with-icon.icon-3::before {
            background: url(../images/0302-steak.png) no-repeat center center !important;
        }

        #fh5co-events .fh5co-heading .heading:before,
        #fh5co-events .fh5co-heading .heading::before {
            height: 64px;
            width: 49px;
            position: absolute;
            content: "";
            background: none no-repeat !important;
            top: 0;
            left: 50%;
            margin-top: -50px;
            margin-left: -24px;
        }

        .zoom {
            transition: transform .8s;
            /* Animation */
        }

        .zoom:hover {
            transform: scale(1.3);
        }

        .overflow-hidden {
            overflow: hidden;
        }

        .checkout {
            position: fixed !important;
            bottom: 20px;
            right: 20px;
            z-index: 999999;
        }
    </style>

    @stack('styles')

    <!-- Modernizr JS -->
    <script src="js/modernizr-2.6.2.min.js"></script>
    <!-- FOR IE9 below -->
    <!--[if lt IE 9]>
    <script src="js/respond.min.js"></script>
    <![endif]-->

</head>

<body>

    <div id="fh5co-container">
        @yield('content')
    </div>

    <div id="fh5co-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="to-animate" style="margin-bottom: 20px;">
                        &copy; 2025 Hoje Coffee. <br>
                        Designed by Fita Virginia
                    </p>
                    <p class="text-center to-animate" style="margin-bottom: 20px;">
                        <a href="#" class="js-gotop">Go To Top</a>
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-center">
                    <ul class="fh5co-social">
                        <li class="to-animate-2">
                            <a href="https://www.instagram.com/hoje.coffee" target="_blank"><i
                                    class="icon-instagram"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @if (request()->is('menus'))
        <div class="checkout" style="position: relative;">
            <a href="/checkout" class="btn btn-primary">
                <i class="icon-shopping-cart" style="font-size: 20px;"></i>
                <span style="position: absolute; top: 0; left: 8px;font-size: 20px;"></span>
            </a>
        </div>
    @endif

    <!-- Modal -->
    <div class="modal fade" id="menuModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="modalMenuTitle">Pilih Menu</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="menuId">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="form-group">
                                <label>Menu</label>
                                <p id="menuName"></p>
                            </div>
                        </div>
                        <div class="col-xs-6" id="modalVariantWrapper">
                            <div class="form-group">
                                <label for="modalVariant">Variant</label>
                                <select id="modalVariant" class="form-control">
                                    <option value="">-- Select Variant --</option>
                                    <option value="hot">Hot</option>
                                    <option value="cold">Cold</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xs-6" id="modalQtyWrapper">
                            <div class="form-group">
                                <label for="modalQty">Qty</label>
                                <input type="number" id="modalQty" class="form-control" value="1" min="1">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="addToCart">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="js/jquery.min.js"></script>
    <!-- jQuery Easing -->
    <script src="js/jquery.easing.1.3.js"></script>
    <!-- Bootstrap -->
    <script src="js/bootstrap.min.js"></script>
    <!-- Bootstrap DateTimePicker -->
    <script src="js/moment.js"></script>
    <script src="js/bootstrap-datetimepicker.min.js"></script>
    <!-- Waypoints -->
    <script src="js/jquery.waypoints.min.js"></script>
    <!-- Stellar Parallax -->
    <script src="js/jquery.stellar.min.js"></script>

    <!-- Flexslider -->
    <script src="js/jquery.flexslider-min.js"></script>
    <script>
        $(function() {
            $('#date').datetimepicker();

            const urlParams = new URLSearchParams(window.location.search)
            const table = urlParams.get('table')

            if (table) {
                $('.checkout a').attr('href', `/checkout?table=${table}`)
            }
        });
    </script>
    <!-- Main JS -->
    <script src="js/main.js"></script>
    @stack('scripts')

</body>

</html>
