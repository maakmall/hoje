@use('App\Helpers\Numeric')
@use('Illuminate\Support\Carbon')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pesanan</title>
</head>
<body>
    <h1>Laporan Pesanan</h1>
    <h3>Periode : {{ $start->format('d-m-Y') }} - {{ $end->format('d-m-Y') }}</h3>
    <h3>Total : {{ Numeric::rupiah($total, true) }}</h3>
    <h3>Total Item : {{ $total_items }} Pcs</h3>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>No</th>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                <tr>
                    <td align="center">{{ $loop->iteration }}</td>
                    <td align="center">{{ $order->id }}</td>
                    <td align="center">{{ Carbon::parse($order->waktu)->format('d-m-Y H:i') }}</td>
                    <td align="center">{{ $order->order_menus_sum_jumlah }}</td>
                    <td align="right">
                        {{ Numeric::rupiah($order->order_menus_sum_subtotal_harga, true) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>