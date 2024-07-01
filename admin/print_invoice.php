<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <title>Print Invoice <?= rand(1000, 9999) ?> - Mikada Laundry</title>
    <style>
        /* Additional styles specific to your invoice */
        .invoice-container { max-width: 600px; margin: 20px auto; padding: 20px; background: #f9f9f9; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .invoice-details { margin-top: 20px; }
        .print-button { text-align: center; margin-top: 40px; }
        .logo-container { text-align: center; }
        .logo-container img { max-width: 320px; }
    </style>
</head>
<body>
    <?php
    require 'koneksi.php';

    if (isset($_GET['id'])) {
        $id_transaksi = $_GET['id'];
        
        // Sanitize input
        $id_transaksi = mysqli_real_escape_string($conn, $id_transaksi);

        // Query construction to fetch invoice details including discount, tax, and customer details
        $query = "SELECT transaksi.*, pelanggan.nama_pelanggan, pelanggan.alamat_pelanggan, pelanggan.telp_pelanggan,
                  detail_transaksi.keterangan, transaksi.diskon AS diskon_decimal, transaksi.pajak AS pajak,
                  transaksi.biaya_tambahan AS biaya_tambahan,
                  SUM(detail_transaksi.total_harga) AS subtotal
                  FROM transaksi 
                  INNER JOIN pelanggan ON pelanggan.id_pelanggan = transaksi.id_pelanggan 
                  INNER JOIN detail_transaksi ON detail_transaksi.id_transaksi = transaksi.id_transaksi 
                  WHERE transaksi.id_transaksi = '$id_transaksi'";
        
        // Execute query
        $result = mysqli_query($conn, $query);

        if (!$result) {
            die('Error: ' . mysqli_error($conn)); // Handle query error
        }

        $trans = mysqli_fetch_assoc($result);

        if (!$trans) {
            die('Invoice not found'); // Handle no invoice found scenario
        }

        // Check if keterangan is empty
        $keterangan_message = empty($trans['keterangan']) ? "Keterangan tidak tersedia" : htmlspecialchars($trans['keterangan']);

        // Calculate discount percentage (assuming 'diskon_decimal' is stored as 0.12 for 12%)
        $diskon_percentage = $trans['diskon_decimal'] * 100; // Convert decimal to percentage

        // Calculate total including additional cost
        $total = ($trans['subtotal'] + $trans['pajak'] + $trans['biaya_tambahan']) * (1 - $trans['diskon_decimal']);

        date_default_timezone_set('Asia/Jakarta');
        
        // Define arrays for Indonesian day and month names
        $dayNames = [
            'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
        ];
        
        $monthNames = [
            '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        
        // Get current day of the week (0 = Sunday, 1 = Monday, ..., 6 = Saturday)
        $dayOfWeek = date('w');
        $dayName = $dayNames[$dayOfWeek];
        
        // Get current day of the month, month, and year
        $dayOfMonth = date('d');
        $month = date('n');
        $monthName = $monthNames[$month];
        $year = date('Y');
        
        // Get current time in HH:ii format
        $time = date('H:i');
        
        // Format the date and time
        $now = "{$dayName}, {$dayOfMonth} - {$monthName} - {$year} [ {$time} ]";
    ?>
    <div class="print-content">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2 col-xs-12 brandSection">
                    <!-- Your brand header section -->
                    <div class="logo-container">
                        <img src="../assets/img/mikada Laundry light mode.png" alt="Mikada Laundry">
                    </div>
                    <!-- Example: Brand Logo, contact info -->
                </div>
                <div class="col-md-12 col-sm-12 content">
                    <!-- Invoice title and details -->
                    <h1>Invoice<strong> #<?= htmlspecialchars($trans['kode_invoice']) ?></strong></h1>
                    <p><p>Waktu Print: <b><?= $now ?></b></p></p>
                    <!-- Additional details as needed -->
                </div>

                <div class="col-md-12 col-sm-12 panelPart">
                    <!-- From (Sender) section -->
                    <div class="panel panel-default">
                        <div class="panel-heading">From</div>
                        <div class="panel-body">
                            <p>Admin Mikada Laundry</p>
                            <p>Jl. Minangkabau Dalam II No.4 9, RT.9/RW.6, Menteng Atas, Kecamatan Setiabudi, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12960</p>
                            <p>Phone: (021) 22837475 / +628561463864</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12 panelPart">
                    <!-- To (Receiver) sections -->
                    <div class="panel panel-default">
                        <div class="panel-heading">To</div>
                        <div class="panel-body">
                            <p>Nama Costumer: <?= htmlspecialchars($trans['nama_pelanggan']) ?></p>
                            <p>Alamat Costumer: <?= htmlspecialchars($trans['alamat_pelanggan']) ?></p>
                            <p>Nomor Telp Costumer: <?= htmlspecialchars($trans['telp_pelanggan']) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12 tableSection">
                    <!-- Items section -->
                    <h1>ITEMS</h1>
                    <table class="table text-center">
                        <thead>
                            <tr class="tableHead">
                                <th style="width:30px;">No</th>
                                <th>Description</th>
                                <th style="width:100px;">Unit Price</th>
                                <th style="width:100px;">Quantity</th>
                                <th style="width:100px;text-align:center;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query to fetch invoice items including package details from paket_cuci
                            $query_items = "SELECT dt.*, pc.jenis_paket, pc.nama_paket, pc.harga 
                                            FROM detail_transaksi dt 
                                            INNER JOIN paket_cuci pc ON dt.id_paket = pc.id_paket 
                                            WHERE dt.id_transaksi = '$id_transaksi'";
                            $result_items = mysqli_query($conn, $query_items);

                            if ($result_items) {
                                $no = 1;
                                while ($item = mysqli_fetch_assoc($result_items)) {
                                    echo "<tr>";
                                    echo "<td>{$no}</td>"; // Numbering the items
                                    echo "<td>{$item['jenis_paket']} - {$item['nama_paket']}</td>"; // Display package details
                                    echo "<td>Rp " . number_format($item['harga']) . "</td>"; // Display package price
                                    echo "<td>{$item['qty']}</td>"; // Display quantity from detail_transaksi
                                    echo "<td>Rp " . number_format($item['total_harga']) . "</td>"; // Display total_harga from detail_transaksi
                                    echo "</tr>";
                                    $no++;
                                }
                            } else {
                                echo "<tr><td colspan='5'>No items found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-12 col-sm-12 lastSectionleft">
                    <!-- Special Notes and Amount Due section -->
                    <div class="row">
                        <div class="col-md-8 col-sm-6 Sectionleft">
                            <p><i><?= $keterangan_message ?></i></p>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <!-- Example: Amount due section -->
                            <div class="panel panel-default">
                                <div class="panel-body lastPanel">
                                    AMOUNT DUE
                                </div>
                                <div class="panel-footer lastFooter">
                                    <div class="row">
                                        <div class="col-md-5 col-sm-6 col-xs-6 panelLastLeft">
                                            <p>SUBTOTAL</p>
                                            <p>BIAYA TAMBAHAN</p>
                                            <p>PAJAK</p>
                                            <p>DISKON</p>
                                            <p>TOTAL</p>
                                        </div>
                                        <div class="col-md-7 col-sm-6 col-xs-6 panelLastRight">
                                            <p>Rp <?= number_format($trans['subtotal']) ?></p>
                                            <p>Rp <?= number_format($trans['biaya_tambahan']) ?></p>
                                            <p>Rp <?= number_format($trans['pajak']) ?></p>
                                            <p><?= number_format($diskon_percentage) ?>%</p> <!-- Display discount percentage -->
                                            <p><strong>Rp <?= number_format($total) ?></strong></p> <!-- Display total -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12 print-button">
                    <!-- Print button -->
                    <button onclick="printInvoice()" class="btn btn-primary">Print Invoice</button>
                </div>
            </div>
        </div>
    </div>
    <?php
        // Close database connection
        mysqli_close($conn);
    }
    ?>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script>
        function printInvoice() {
            // Hide everything else on the page except for the invoice container
            var elementsToHide = document.querySelectorAll('body > *:not(.print-content)');
            elementsToHide.forEach(function(element) {
                element.style.display = 'none';
            });

            // Print the invoice container
            window.print();

            // Show everything again after printing
            elementsToHide.forEach(function(element) {
                element.style.display = '';
            });
        }
    </script>
</body>
</html>
