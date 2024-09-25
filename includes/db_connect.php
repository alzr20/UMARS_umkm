<?php
$servername = "localhost";
$username = "root";
$password = ""; // Ganti dengan password database Anda
$dbname = "umkm"; // Ganti dengan nama database Anda

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
