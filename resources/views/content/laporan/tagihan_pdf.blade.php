<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
        .summary { margin-top: 20px; width: 300px; float: right; }
        .summary table { border: none; }
        .summary td { border: none; padding: 4px; }
        .footer { margin-top: 50px; text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 4px 8px; border-radius: 4px; color: white; }
        .bg-success { background-color: #28a745; }
        .bg-warning { background-color: #ffc107; color: black; }
    </style>
</head>
<body>
    <div class="header">
        <h2>ROZITECH NETWORK (RTRW NET)</h2>
        <h3>LAPORAN PEMBAYARAN TAGIHAN</h3>
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
        @if(isset($filter['month']) && $filter['month'])
            <span>Bulan: {{ date('F', mktime(0, 0, 0, $filter['month'], 10)) }}</span>
        @endif
        @if(isset($filter['year']) && $filter['year'])
            <span> Tahun: {{ $filter['year'] }}</span>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Pelanggan</th>
                <th>Periode</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th>Tgl Bayar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tagihan as $t)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $t->pelanggan->kode_pelanggan }}</td>
                <td>{{ $t->pelanggan->nama_pelanggan }}</td>
                <td>{{ date('M', mktime(0, 0, 0, $t->bulan, 10)) }} {{ $t->tahun }}</td>
                <td>Rp {{ number_format($t->jumlah) }}</td>
                <td class="text-center">
                    {{ $t->status == 'paid' ? 'LUNAS' : 'BELUM BAYAR' }}
                </td>
                <td>{{ $t->paid_at ? date('d/m/Y', strtotime($t->paid_at)) : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td><strong>Total Tagihan:</strong></td>
                <td><strong>Rp {{ number_format($total_jumlah) }}</strong></td>
            </tr>
            <tr>
                <td style="color: green;">Total Terbayar:</td>
                <td style="color: green;">Rp {{ number_format($total_lunas) }}</td>
            </tr>
            <tr>
                <td style="color: orange;">Total Piutang:</td>
                <td style="color: orange;">Rp {{ number_format($total_piutang) }}</td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <div class="footer">
        <p>Hormat Kami,</p>
        <br><br><br>
        <p><strong>Rozitech</strong></p>
    </div>
</body>
</html>
