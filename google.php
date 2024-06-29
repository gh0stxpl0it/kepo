<?php
session_start();
include_once 'gpconfig.php';

// Konfigurasi koneksi ke database MySQL
$host = 'localhost';
$db_name = 'kkjlozhk_mikada-laundry';
$username = 'kkjlozhk_dopper';
$password = 'botak239@';

// Menangani error untuk debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (isset($_GET['code'])) {
    $gclient->authenticate($_GET['code']);
    $_SESSION['token'] = $gclient->getAccessToken();
    header('Location: ' . filter_var($redirect_url, FILTER_SANITIZE_URL));
    exit();
}

if (isset($_SESSION['token'])) {
    $gclient->setAccessToken($_SESSION['token']);
}

if ($gclient->getAccessToken()) {
    // Dapatkan data profil pengguna dari Google
    $gpuserprofile = $google_oauthv2->userinfo->get();
    $email = $gpuserprofile['email'];
    $profile_image = $gpuserprofile['picture']; // Ambil URL gambar profil pengguna

    // Cek apakah pengguna sudah ada di database
    $sql = $pdo->prepare("SELECT id_user, username, nama_user, role, outlet_id FROM user WHERE email=:email");
    $sql->bindParam(':email', $email);
    $sql->execute();
    $user = $sql->fetch();

    if (empty($user)) {
        // Jika pengguna tidak ada, buat pengguna baru dengan peran default 'kasir'
        $ex = explode('@', $email);
        $username = $ex[0];
        $nama = $gpuserprofile['given_name'] . " " . $gpuserprofile['family_name'];

        // Masukkan pengguna baru ke database
        $sql = $pdo->prepare("INSERT INTO user(username, nama_user, email, role, outlet_id) VALUES(:username, :nama_user, :email, 'kasir', NULL)");
        $sql->bindParam(':username', $username);
        $sql->bindParam(':nama_user', $nama);
        $sql->bindParam(':email', $email);
        $sql->execute();

        $id = $pdo->lastInsertId();
        $role = 'kasir';
        $outlet_id = NULL;
    } else {
        // Pengguna sudah ada, ambil data pengguna
        $id = $user['id_user'];
        $username = $user['username'];
        $nama = $user['nama_user'];
        $role = $user['role'];
        $outlet_id = $user['outlet_id'];
    }

    // Set variabel sesi
    $_SESSION['id_user'] = $id;
    $_SESSION['user_id'] = $id;
    $_SESSION['username'] = $username;
    $_SESSION['nama_user'] = $nama;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;
    $_SESSION['outlet_id'] = $outlet_id;
    $_SESSION['profile_image'] = $profile_image; // Simpan URL gambar profil dalam sesi

    // Redirect ke halaman berdasarkan peran pengguna
    switch ($role) {
        case 'admin':
            header('location:admin');
            break;
        case 'owner':
            header('location:owner');
            break;
        default:
            header('location:kasir');
            break;
    }
    exit();
} else {
    // Jika token akses tidak tersedia, redirect ke halaman login Google
    $authUrl = $gclient->createAuthUrl();
    header("location: " . $authUrl);
    exit();
}
?>
