<?php
$title = 'Data Transaksi';
require 'koneksi.php';

// Check if user has outlet_id
session_start();
if (!isset($_SESSION['outlet_id'])) {
    header("Location: ./"); // Ganti dengan halaman yang sesuai
    exit();
}

$query = "SELECT transaksi.*, pelanggan.nama_pelanggan, pelanggan.telp_pelanggan, detail_transaksi.total_harga,
                 CONCAT(
                     CASE DAYOFWEEK(transaksi.tgl_pembayaran)
                         WHEN 1 THEN 'Minggu'
                         WHEN 2 THEN 'Senin'
                         WHEN 3 THEN 'Selasa'
                         WHEN 4 THEN 'Rabu'
                         WHEN 5 THEN 'Kamis'
                         WHEN 6 THEN 'Jumat'
                         WHEN 7 THEN 'Sabtu'
                     END,
                     ', ',
                     DATE_FORMAT(transaksi.tgl_pembayaran, '%d-%m-%Y [%H:%i]')
                 ) AS tgl_pembayaran_formatted
          FROM transaksi 
          INNER JOIN pelanggan ON pelanggan.id_pelanggan = transaksi.id_pelanggan 
          INNER JOIN detail_transaksi ON detail_transaksi.id_transaksi = transaksi.id_transaksi";
$data = mysqli_query($conn, $query);

require 'header.php';
?>

<div class="panel-header bg-primary-gradient">
    <div class="page-inner py-5">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
            <div>
                <h2 class="text-white pb-2 fw-bold">Dashboard</h2>
            </div>
        </div>
        <?php if (isset($_GET['msg'])) : ?>
            <div class="alert alert-success" id="msg"><?= $_GET['msg'] ?></div>
        <?php endif ?>
    </div>
</div>
<div class="page-inner mt--5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-wrap align-items-centers">
                        <h4 class="card-title"><?= $title; ?></h4>
                        <a href="cari.php" class="btn btn-primary btn-round mb-2 mb-md-0" style="margin-left: auto;">
                            <i class="fa fa-plus"></i> Tambah Transaksi
                        </a>
                        <a href="konfirmasi.php" class="btn btn-primary btn-round ml-0 ml-md-2" style="margin-left: auto;">
                            <i class="fas fa-user-check"></i> Konfirmasi Pembayaran
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="basic-datatables" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 7%">#</th>
                                    <th>Kode</th>
                                    <th>Nama Pelanggan</th>
                                    <th>Nomor Telepon</th>
                                    <th>Status</th>
                                    <th>Pembayaran</th>
                                    <th>Total</th>
                                    <th style="width: 15%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                if (mysqli_num_rows($data) > 0) {
                                    while ($trans = mysqli_fetch_assoc($data)) {
                                        $total_harga = $trans['total_harga'];
                                        $biaya_tambahan = $trans['biaya_tambahan']; // Assuming `biaya_tambahan` is a field in `transaksi` table
                                        $pajak = $trans['pajak']; // Assuming `pajak` is a field in `transaksi` table
                                        $diskon = $trans['diskon']; // Assuming `diskon` is a field in `transaksi` table (in percentage)
                                        
                                        $total = ($total_harga + $biaya_tambahan + $pajak) * (1 - $diskon);

                                        // Style the WhatsApp message text
                                        $whatsapp_message = "```Halo Kak ```" . $trans['nama_pelanggan'] . ",\n\n"
                                                         . "```Berikut detail transaksi Kaka:```\n\n"
                                                         . "```Kode Invoice:``` *" . $trans['kode_invoice'] . "*\n"
                                                         . "```Total:``` Rp *" . number_format($total) . "*\n";
                                        
                                        if ($trans['status_bayar'] == 'Dibayar') {
                                            $whatsapp_message .= "```Status:``` *Pesanan Anda telah " . $trans['status'] . " Dan Sudah bisa diambil.*\n"
                                                              . "```Pembayaran:``` *Sudah Dibayar*\n";
                                        } else {
                                            $whatsapp_message .= "```Status:``` *Pesanan Anda " . $trans['status'] . "*\n"
                                                              . "```Pembayaran:``` *Belum Dibayar*\n";
                                        }
                                        $whatsapp_message .= "```Tanggal Pembayaran:``` *" . $trans['tgl_pembayaran_formatted'] . "*";

                                        // Generate WhatsApp link
                                        $whatsapp_link = "https://api.whatsapp.com/send?phone={$trans['telp_pelanggan']}&text=" . urlencode($whatsapp_message);
                                ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= $trans['kode_invoice']; ?></td>
                                            <td><?= $trans['nama_pelanggan']; ?></td>
                                            <td><?= $trans['telp_pelanggan']; ?></td>
                                            <td><?= $trans['status']; ?></td>
                                            <td><?= $trans['tgl_pembayaran_formatted']; ?></td>
                                            <td><?= 'Rp ' . number_format($total); ?></td>
                                            <td>
                                                <div class="form-button-action">
                                                    <a href="detail.php?id=<?= $trans['id_transaksi']; ?>" type="button" data-toggle="tooltip" title="Detail" class="btn btn-primary mr-2">
                                                        <i class="far fa-eye"></i> Detail
                                                    </a>
                                                    <a href="<?= $whatsapp_link; ?>" target="_blank" class="btn btn-success mr-2" data-toggle="tooltip" title="Chat WhatsApp">
                                                        <i class="fab fa-whatsapp"></i> WhatsApp
                                                    </a>
                                                    <a href="print_invoice.php?id=<?= $trans['id_transaksi']; ?>" target="_blank" class="btn btn-info" data-toggle="tooltip" title="Print Invoice">
                                                        <i class="fas fa-print"></i> Print
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                <?php }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require 'footer.php';
?>
