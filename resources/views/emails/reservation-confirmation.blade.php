<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Reservasi</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 30px;
            color: #333;
        }

        .container {
            max-width: 600px;
            background-color: #fff;
            margin: 0 auto;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border-top: 6px solid #446a5f;
        }

        .header {
            background-color: #446a5f;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .content {
            padding: 25px;
        }

        h2 {
            margin-top: 0;
            color: #446a5f;
        }

        ul {
            padding-left: 20px;
        }

        li {
            margin-bottom: 5px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 30px;
            margin-bottom: 10px;
            color: #446a5f;
        }

        .footer {
            text-align: center;
            font-size: 13px;
            color: #999;
            padding: 20px;
        }

        .highlight {
            font-weight: bold;
            color: #446a5f;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Konfirmasi Reservasi</h1>
        </div>
        <div class="content">
            <h2>Halo {{ $reservation->nama_pelanggan }},</h2>

            <p>Terima kasih telah melakukan reservasi di tempat kami.</p>

            <div class="section-title">Detail Reservasi</div>
            <ul>
                <li><strong>Tanggal & Waktu:</strong> {{ $reservation->waktu->format('d M Y, H:i') }}</li>
                <li><strong>Lokasi:</strong> {{ $reservation->location->nama }}</li>
                <li><strong>Jumlah Orang:</strong> {{ $reservation->jumlah_orang }}</li>
            </ul>

            <div class="section-title">Menu yang Dipesan</div>
            <ul>
                @foreach ($orderMenus as $menu)
                    <li>{{ $menu->menu->nama }} {{ $menu->variasi_minuman ? "($menu->variasi_minuman)" : '' }} - {{ $menu->jumlah }}x - Rp {{ \App\Helpers\Numeric::rupiah($menu->subtotal_harga) }}</li>
                @endforeach
            </ul>

            <div class="section-title">Informasi Pembayaran</div>
            <p>Total: <span class="highlight">Rp {{ \App\Helpers\Numeric::rupiah($orderMenus->sum('subtotal_harga')) }}</span></p>

            <ul>
                <li><strong>DP (50%):</strong> Rp {{ \App\Helpers\Numeric::rupiah($payment->jumlah) }}</li>
                <li><strong>Metode Pembayaran:</strong> {{ $payment->metode->label() }}</li>
            </ul>

            <p>Mohon selesaikan sisa pembayaran paling lambat <strong>{{ $reservation->waktu->format('d M Y') }}</strong>.</p>

            <p>Salam hangat,<br><strong>Admin Reservasi</strong></p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }} - Semua hak dilindungi.
        </div>
    </div>
</body>
</html>
