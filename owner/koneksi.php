<?php
session_start();
if ($_SESSION) {
    if ($_SESSION['role'] == 'owner') {
    } else {
        header("location:../index.php");
    }
} else {
    header('location:../index.php');
}

$conn = mysqli_connect("localhost", "kkjlozhk_dopper", "botak239@", "kkjlozhk_mikada-laundry");

if (mysqli_connect_error()) {
    echo "Koneksi ke database gagal : " . mysqli_connect_error();
}
