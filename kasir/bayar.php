<?php
$title = 'Pembayaran';
require 'koneksi.php';

// Set timezone to Indonesia (WIB)
date_default_timezone_set('Asia/Jakarta');

// Fetch transaction details
$query = mysqli_query($conn, "SELECT transaksi.*, pelanggan.nama_pelanggan, detail_transaksi.total_harga, transaksi.pajak, transaksi.diskon FROM transaksi INNER JOIN pelanggan ON pelanggan.id_pelanggan = transaksi.id_pelanggan INNER JOIN detail_transaksi ON detail_transaksi.id_transaksi = transaksi.id_transaksi WHERE transaksi.id_transaksi = " . $_GET['id']);
$data = mysqli_fetch_assoc($query);

// Calculate total to be paid
$pajak = $data['pajak'];
$diskon = $data['diskon'];
$biaya_tambahan = $data['biaya_tambahan'];
$total_harga = $data['total_harga'];
$total_bayar = ($total_harga + $pajak + $biaya_tambahan) * (1 - $diskon);

// Process payment submission
if (isset($_POST['btn-simpan'])) {
    $jumlah_bayar = $_POST['total_bayar'];
    if ($jumlah_bayar >= $total_bayar) {
        // Update transactions
        $query = "UPDATE transaksi SET status_bayar = 'dibayar', tgl_pembayaran = '" . date('Y-m-d H:i:s') . "' WHERE id_transaksi = " . $_GET['id'];
        $query2 = "UPDATE detail_transaksi SET total_bayar = '$jumlah_bayar' WHERE id_transaksi = " . $_GET['id'];

        // Perform database updates
        $insert = mysqli_query($conn, $query);
        $insert2 = mysqli_query($conn, $query2);

        // Check if both updates were successful
        if ($insert && $insert2) {
            // Set success message
            $_SESSION['msg'] = 'Pembayaran berhasil.';
            header('location: ./transaksi_dibayar.php?id=' . $_GET['id']);
            exit(); // Ensure no further output after redirection
        } else {
            // Handle database error
            $_SESSION['msg'] = 'Gagal mengupdate data.';
            header('location: ./bayar.php?id=' . $_GET['id']);
            exit();
        }
    } else {
        // Insufficient payment amount
        $msg = "Jumlah Uang Pembayaran Kurang";
        header('location: ./bayar.php?id=' . $_GET['id'] . '&msg=' . $msg);
        exit();
    }
}

require 'header.php';
?>
<div class="content">
    <div class="page-inner">
        <div class="page-header">
            <h4 class="page-title">Forms</h4>
            <ul class="breadcrumbs">
                <li class="nav-home">
                    <a href="index.php">
                        <i class="flaticon-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="flaticon-right-arrow"></i>
                </li>
                <li class="nav-item">
                    <a href="transaksi.php">Transaksi</a>
                </li>
                <li class="separator">
                    <i class="flaticon-right-arrow"></i>
                </li>
                <li class="nav-item">
                    <a href="konfirmasi.php">Konfirmasi Pembayaran</a>
                </li>
                <li class="separator">
                    <i class="flaticon-right-arrow"></i>
                </li>
                <li class="nav-item">
                    <a href="#"><?= $title; ?></a>
                </li>
            </ul>
            <?php if (isset($_SESSION['msg']) && $_SESSION['msg'] <> '') { ?>
                <div class="alert alert-success" role="alert">
                    <?= $_SESSION['msg']; ?>
                </div>
            <?php }
            $_SESSION['msg'] = ''; ?>
        </div>
        <div class="row">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><?= $title; ?></div>
                    </div>
                    <form action="bayar.php?id=<?= $data['id_transaksi']; ?>" id="form-submit" method="POST">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="kode_invoice">Kode Invoice</label>
                                <input type="text" name="kode_invoice" class="form-control" id="kode_invoice" value="<?= $data['kode_invoice']; ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="nama_pelanggan">Nama Pelanggan</label>
                                <input type="text" name="nama_pelanggan" class="form-control" id="nama_pelanggan" value="<?= $data['nama_pelanggan']; ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="total_harga">Total Yang Harus Dibayarkan</label>
                                <input type="text" name="total_harga" class="form-control" id="total_harga" value="<?= 'Rp ' . number_format($total_bayar); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="total_bayar">Masukan Jumlah Pembayaran</label>
                                <input type="number" name="total_bayar" id="total_bayar" class="form-control" value="<?= $total_bayar; ?>">
                                <?php if (isset($_GET['msg'])) : ?>
                                    <small class="text-danger"><?= $_GET['msg'] ?></small>
                                <?php endif ?>
                            </div>
                            <div class="card-action">
                                <button type="submit" name="btn-simpan" class="btn btn-success">Submit</button>
                                <a href="javascript:void(0)" onclick="window.history.back();" class="btn btn-danger">Batal</a>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require 'footer.php'; ?>
