<?php

require 'koneksi.php';
$query = "SELECT transaksi.*, transaksi.tgl, pelanggan.nama_pelanggan, detail_transaksi.total_harga, detail_transaksi.total_bayar, outlet.nama_outlet, transaksi.biaya_tambahan, transaksi.pajak, transaksi.diskon 
FROM transaksi 
INNER JOIN pelanggan ON pelanggan.id_pelanggan = transaksi.id_pelanggan 
INNER JOIN detail_transaksi ON detail_transaksi.id_transaksi = transaksi.id_transaksi 
INNER JOIN outlet ON outlet.id_outlet = transaksi.outlet_id";
$data = mysqli_query($conn, $query);

// Set timezone to Asia/Jakarta (Indonesian time)
date_default_timezone_set('Asia/Jakarta');

// Array untuk nama hari dalam bahasa Indonesia
$nama_hari = array(
    'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Cetak Laporan</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
</head>

<body>

    <center>
        <h2>DATA LAPORAN TRANSAKSI LAUNDRY</h2>
        <?php
        // Mendapatkan waktu sekarang
        $tanggal_sekarang = date('d F Y, H:i:s');

        // Mendapatkan nama hari dari waktu sekarang
        $index_hari_sekarang = date('w');
        $nama_hari_sekarang = $nama_hari[$index_hari_sekarang];

        // Tampilkan tanggal dengan format yang diinginkan
        ?>
        <h6><?= $nama_hari_sekarang . ', ' . $tanggal_sekarang . ' WIB'; ?></h6>
        <h6 class="mr-auto">Oleh : <?= $_SESSION['username']; ?></h6>
        <br>
    </center>
    <table class="table table-bordered" style="width: 100%;">
        <thead>
            <tr>
                <th style="width: 3%">#</th>
                <th>Kode</th>
                <th>Nama Pelanggan</th>
                <th>Tanggal Pesanan</th>
                <th>Status</th>
                <th>Pembayaran</th>
                <th>Total</th>
                <th>Outlet Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            if (mysqli_num_rows($data) > 0) {
                while ($trans = mysqli_fetch_assoc($data)) {
                    $total_harga = $trans['total_harga'];
                    $biaya_tambahan = $trans['biaya_tambahan'];
                    $pajak = $trans['pajak'];
                    $diskon = $trans['diskon'];
                    $total = ($total_harga + $biaya_tambahan + $pajak) * (1 - $diskon);
                    // Mendapatkan nama hari berdasarkan index hari dari tanggal
                    $index_hari = date('w', strtotime($trans['tgl']));
            ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= $trans['kode_invoice']; ?></td>
                        <td><?= $trans['nama_pelanggan']; ?></td>
                        <td><?= $nama_hari[$index_hari] . ', ' . date('d F Y, H:i:s', strtotime($trans['tgl'])); ?> WIB</td>
                        <td><?= $trans['status']; ?></td>
                        <td><?= $trans['status_bayar']; ?></td>
                        <td><?= 'Rp ' . number_format($total); ?></td>
                        <td><?= $trans['nama_outlet']; ?></td>
                    </tr>
            <?php }
            }
            ?>
        </tbody>
    </table>

    <script>
        window.print();
    </script>

</body>

</html>
