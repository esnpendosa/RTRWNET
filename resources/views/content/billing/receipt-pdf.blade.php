<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .receipt-box {
            max-width: 600px;
            margin: auto;
            border: 1px solid #eee;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .logo-section h1 {
            margin: 0;
            font-size: 28px;
            color: #000;
        }
        .company-info {
            text-align: right;
            font-size: 12px;
        }
        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .details-table th, .details-table td {
            border: 1px solid #333;
            padding: 12px;
            text-align: left;
        }
        .details-table th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-style: italic;
            font-size: 12px;
            border-top: 1px dashed #999;
            padding-top: 20px;
        }
        .contact-info {
            margin-top: 10px;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="receipt-box">
        <div class="header">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%;">
                        <div style="font-size: 26px; font-weight: bold; color: #1e3a8a; font-family: 'Helvetica', 'Arial', sans-serif; letter-spacing: 2px; margin-bottom: 5px;">RMI</div>
                        <div style="font-size: 14px; font-weight: bold; color: #333;">Rozitech Multimedia Indonesia</div>
                    </td>
                    <td style="text-align: right; font-size: 11px;">
                        CS: 0856-0411-8932<br>
                        IG: @rozitechgresik<br>
                        www.rozitech.co.id
                    </td>
                </tr>
            </table>
        </div>

        <div class="title">Nota Pembayaran WiFi</div>

        <table style="width: 100%; margin-bottom: 20px;">
            <tr>
                <td><strong>No. Invoice:</strong> INV-{{ $tagihan->id_tagihan }}-{{ $tagihan->tahun }}</td>
                <td style="text-align: right;"><strong>Tanggal:</strong> {{ $tagihan->paid_at ? $tagihan->paid_at->format('d/m/Y') : now()->format('d/m/Y') }}</td>
            </tr>
        </table>

        <table class="details-table">
            <thead>
                <tr>
                    <th>ID PELANGGAN</th>
                    <th style="text-align: right;">JUMLAH</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $tagihan->pelanggan->kode_pelanggan }}</strong><br>
                        <span style="font-size: 12px;">{{ $tagihan->pelanggan->nama_pelanggan }}</span><br>
                        <span style="font-size: 11px; color: #666;">Periode: {{ date('F', mktime(0, 0, 0, $tagihan->bulan, 10)) }} {{ $tagihan->tahun }}</span>
                    </td>
                    <td style="text-align: right; vertical-align: top; font-size: 18px; font-weight: bold;">
                        Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="background: #fafafa; font-size: 11px;">
                        <strong>Alamat:</strong> {{ $tagihan->pelanggan->alamat }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            Terima kasih atas pembayaran,<br>
            semoga dipermudah segala urusan juga rezekinya.
            <div class="contact-info">
                Simpan nota ini sebagai bukti pembayaran yang sah.
            </div>
        </div>
    </div>
</body>
</html>
