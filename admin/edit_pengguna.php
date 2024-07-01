<?php
$title = 'Edit Data Pengguna';
require 'koneksi.php';

$role = [
    'admin',
    'owner',
    'kasir'
];

$id_user = $_GET['id'];
$query = "SELECT * FROM user WHERE id_user = '$id_user'";
$queryedit = mysqli_query($conn, $query);

// Fetch outlets from database
$query_outlet = "SELECT id_outlet, nama_outlet FROM outlet";
$outlet_result = mysqli_query($conn, $query_outlet);

if (isset($_POST['btn-simpan'])) {
    $nama = $_POST['nama_user'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $id_outlet = $_POST['id_outlet']; // Ambil id_outlet yang dipilih dari form

    // Check if password is being updated
    if ($_POST['password'] != null || $_POST['password'] == '') {
        $password = md5($_POST['password']);
        $query = "UPDATE user SET nama_user = '$nama', username = '$username', role = '$role', password = '$password', outlet_id = '$id_outlet' WHERE id_user = '$id_user'";
    } else {
        $query = "UPDATE user SET nama_user = '$nama', username = '$username', role = '$role', outlet_id = '$id_outlet' WHERE id_user = '$id_user'";
    }

    $update = mysqli_query($conn, $query);
    if ($update) {
        $_SESSION['msg'] = 'Berhasil Update ' . $role;
        header('location:pengguna.php');
        exit();
    } else {
        $_SESSION['msg'] = 'Gagal mengupdate data ' . $role . '!!!';
        header('location:pengguna.php');
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
                    <a href="pengguna.php">Pengguna</a>
                </li>
                <li class="separator">
                    <i class="flaticon-right-arrow"></i>
                </li>
                <li class="nav-item">
                    <a href="#">Edit Pengguna</a>
                </li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><?= $title; ?></div>
                    </div>
                    <?php while ($edit = mysqli_fetch_array($queryedit)) { ?>
                        <form action="" method="POST">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="largeInput">Nama Pengguna</label>
                                    <input type="text" name="nama_user" class="form-control form-control" id="defaultInput" value="<?= $edit['nama_user']; ?>" placeholder="Nama...">
                                </div>
                                <div class="form-group">
                                    <label for="largeInput">Username</label>
                                    <input type="text" name="username" class="form-control form-control" id="defaultInput" value="<?= $edit['username']; ?>" placeholder="Username...">
                                </div>
                                <div class="form-group">
                                    <label for="largeInput">Password</label>
                                    <input type="text" name="password" class="form-control form-control" id="defaultInput">
                                    <small>Kosongkan jika tidak melakukan perubahan password</small>
                                </div>
                                <div class="form-group">
                                    <label for="defaultSelect">Role</label>
                                    <select name="role" class="form-control form-control" id="defaultSelect">
                                        <?php foreach ($role as $key) : ?>
                                            <?php if ($key == $edit['role']) : ?>
                                                <option value="<?= $key ?>" selected><?= $key ?></option>
                                            <?php else : ?>
                                                <option value="<?= $key ?>"><?= ucfirst($key) ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="defaultSelect">Pilih Outlet</label>
                                    <select name="id_outlet" class="form-control form-control" id="defaultSelect">
                                        <?php while ($outlet = mysqli_fetch_array($outlet_result)) { ?>
                                            <option value="<?= $outlet['id_outlet']; ?>" <?= ($outlet['id_outlet'] == $edit['outlet_id']) ? 'selected' : ''; ?>>
                                                <?= $outlet['nama_outlet']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <input type="hidden" name="id_user" value="<?= $edit['id_user']; ?>">
                                <div class="card-action">
                                    <button type="submit" name="btn-simpan" class="btn btn-success">Submit</button>
                                    <a href="javascript:void(0)" onclick="window.history.back();" class="btn btn-danger">Batal</a>
                                </div>
                            </div>
                        </form>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require 'footer.php'; ?>
