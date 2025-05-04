@extends('layouts.main')

@section('content')
    <div id="fh5co-home" class="js-fullheight" data-section="home">

        <div class="flexslider">

            <div class="fh5co-overlay"></div>
            <div class="fh5co-text">
                <div class="container">
                    <div class="row">
                        <h1 class="to-animate" style="margin-bottom: 20px;">hoje</h1>
                        <h2 class="to-animate">Coffee n Co-Working Space</h2>
                    </div>
                </div>
            </div>
            <ul class="slides">
                <li style="background-image: url(images/slider/slide1.jpg);" data-stellar-background-ratio="0.5">
                </li>
                <li style="background-image: url(images/slider/slide2.jpg);" data-stellar-background-ratio="0.5">
                </li>
                <li style="background-image: url(images/slider/slide3.jpg);" data-stellar-background-ratio="0.5">
                </li>
                <li style="background-image: url(images/slider/slide4.jpg);" data-stellar-background-ratio="0.5">
                </li>
                <li style="background-image: url(images/slider/slide5.jpg);" data-stellar-background-ratio="0.5">
                </li>
                <li style="background-image: url(images/slider/slide6.jpg);" data-stellar-background-ratio="0.5">
                </li>
            </ul>

        </div>

    </div>

    @include('partials.navbar')

    <div id="fh5co-about" data-section="philosophy">
        <div class="fh5co-2col fh5co-bg to-animate-2"
            style="background-image: url(images/philosophy.jpg); background-position: 0 -70px"></div>
        <div class="fh5co-2col fh5co-text">
            <h2 class="heading to-animate">Philosophy</h2>
            <p class="to-animate">
                <span class="firstcharacter">A</span>
                t HOJE Coffee, "today" is more than just a moment - it's a philosophy. We believe in savoring every
                sip, making the most of the present, and creating spaces where people can live in the moment.
                Whether you're here to work, collaborate, or simply enjoy the now, HOJE is your place to embrace
                today with great coffee and inspiring surroundings.
            </p>
            </p>
        </div>
    </div>

    <div id="fh5co-sayings">
        <div class="container">
            <div class="row to-animate">

                <div class="flexslider">
                    <ul class="slides">
                        <li>
                            <blockquote>
                                <p>&ldquo;Your Daily Dose of Today.&rdquo;</p>
                                <p class="quote-author">&mdash; Hoje</p>
                            </blockquote>
                        </li>
                        <li>
                            <blockquote>
                                <p>&ldquo;Let's enjoy today with a cup of coffee at HOJE Coffee.&rdquo;</p>
                                <p class="quote-author">&mdash; Fita</p>
                            </blockquote>
                        </li>
                        <li>
                            <blockquote>
                                <p>&ldquo;Coffee is not just a drink, it's a moment to savor today.&rdquo;</p>
                                <p class="quote-author">&mdash; Someone</p>
                            </blockquote>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <div id="fh5co-type" style="background-image: url(images/slider/slide5.jpg);" data-stellar-background-ratio="0.5">
        <div class="fh5co-overlay"></div>
        <div class="container">
            <div class="row">
                <div class="col-md-4 to-animate">
                    <div class="fh5co-type">
                        <h3 class="with-icon icon-drinks">Drinks</h3>
                        <p>
                            Espresso Based, Signature Coffee, Mocktail, Sparkling, Milk Based and Tea
                        </p>
                    </div>
                </div>
                <div class="col-md-4 to-animate">
                    <div class="fh5co-type">
                        <h3 class="with-icon icon-4">Appetizer</h3>
                        <p>
                            Fries, Onion Rings, Cheese Roll, Chicken Pop and others
                        </p>
                    </div>
                </div>
                <div class="col-md-4 to-animate">
                    <div class="fh5co-type">
                        <h3 class="with-icon icon-3">Main Course</h3>
                        <p>Ricebowl, Spaghetti and Fried Rice</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="fh5co-events" data-section="gallery" style="background-image: url(images/wood_1.png)">
        <div class="fh5co-overlay"></div>
        <div class="container">
            <div class="row text-center fh5co-heading row-padded">
                <div class="col-md-8 col-md-offset-2 to-animate">
                    <h2 class="heading">Gallery</h2>
                    <p class="sub-heading">
                        Our gallery showcases the vibrant energy of our space, the delicious drinks and food we serve, and
                        the wonderful people who make each day special.
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4" style="padding: 15px;">
                    <div class="to-animate-2 overflow-hidden">
                        <img src="/images/galleries/gallery1.jpg" style="width: 100%; height: auto;" class="zoom">
                    </div>
                </div>
                <div class="col-md-4" style="padding: 15px;">
                    <div class="to-animate-2 overflow-hidden">
                        <img src="/images/galleries/gallery2.jpg" style="width: 100%; height: auto;" class="zoom">
                    </div>
                </div>
                <div class="col-md-4" style="padding: 15px;">
                    <div class="to-animate-2 overflow-hidden">
                        <img src="/images/galleries/gallery3.jpg" style="width: 100%; height: auto;" class="zoom">
                    </div>
                </div>
                <div class="col-md-4" style="padding: 15px;">
                    <div class="to-animate-2 overflow-hidden">
                        <img src="/images/galleries/gallery4.jpg" style="width: 100%; height: auto;" class="zoom">
                    </div>
                </div>
                <div class="col-md-4" style="padding: 15px;">
                    <div class="to-animate-2 overflow-hidden">
                        <img src="/images/galleries/gallery5.jpg" style="width: 100%; height: auto;" class="zoom">
                    </div>
                </div>
                <div class="col-md-4" style="padding: 15px;">
                    <div class="to-animate-2 overflow-hidden">
                        <img src="/images/galleries/gallery6.jpg" style="width: 100%; height: auto;" class="zoom">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
