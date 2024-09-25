<?php
session_start();

// Mengecek jika pengguna sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

// Memproses form registrasi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include '../includes/db_connect.php'; // Menghubungkan ke database

    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];

    // Validasi password
    if (!preg_match("/[a-z]/", $password) || !preg_match("/[A-Z]/", $password) || !preg_match("/\d/", $password)) {
        $error = "Password harus mengandung huruf kecil, huruf besar, dan angka.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Query untuk memasukkan data pengguna baru dengan role sebagai 'customer'
        $sql = "INSERT INTO users (username, password, email, phone_number, role) VALUES (?, ?, ?, ?, 'customer')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $hashed_password, $email, $phone_number);

        if ($stmt->execute()) {
            header("Location: login.php");
            exit();
        } else {
            $error = "Terjadi kesalahan, coba lagi.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Open Sans', sans-serif;
            overflow: hidden; /* Mencegah scroll */
        }
        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('../images/logo.png') no-repeat center center fixed;
            background-size: cover; /* Menyesuaikan gambar dengan ukuran elemen */
            filter: blur(8px); /* Menambahkan efek blur pada background */
            z-index: -1; /* Menempatkan background di belakang konten */
        }
        .login-container { /* Menggunakan nama yang sama untuk konsistensi */
            max-width: 400px;
            width: 90%;
            padding: 2rem;
            border-radius: 0.5rem;
            background: #ffffff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            font-family: 'Roboto', sans-serif;
            position: absolute; /* Menggunakan posisi absolut */
            top: 50%; /* Memposisikan di tengah vertikal */
            left: 50%; /* Memposisikan di tengah horizontal */
            transform: translate(-50%, -50%); /* Memindahkan kontainer agar tepat di tengah */
            z-index: 1; /* Menempatkan container login di atas background */
        }
        h2 {
            font-family: 'Roboto', sans-serif;
        }
        .form-group a {
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="background"></div>
    <div class="login-container"> <!-- Menggunakan kelas yang sama -->
        <h2 class="text-center">Registrasi Akun</h2>
        <form method="post" action="register.php">
            <?php if (isset($error)) echo '<div class="alert alert-danger" role="alert">' . $error . '</div>'; ?>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Kata Sandi:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="phone_number">Nomor Telepon:</label>
                <input type="text" id="phone_number" name="phone_number" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Daftar</button>
            <div class="form-group text-center mt-3">
                <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
            </div>
        </form>
    </div>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
