<?php
session_start(); // Memulai sesi

// Mengecek jika pengguna sudah login
if (isset($_SESSION['user_id'])) {
    // Mengambil role pengguna dari database
    include '../includes/db_connect.php';
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT role FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($role);
    $stmt->fetch();
    $stmt->close();
    
    if ($role === 'admin') {
        header("Location: admin_dashboard.php"); // Arahkan ke halaman dashboard admin
        exit();
    } else {
        header("Location: ../public/store.php"); // Arahkan ke halaman store untuk customer
        exit();
    }
}

// Memproses form login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include '../includes/db_connect.php'; // Menghubungkan ke database

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query untuk mendapatkan data pengguna
    $sql = "SELECT user_id, password, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id, $hashed_password, $role);
        $stmt->fetch();

        // Memverifikasi kata sandi
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            
            if ($role === 'admin') {
                header("Location: admin_dashboard.php"); // Arahkan ke halaman dashboard admin
            } else {
                header("Location: ../public/store.php"); // Arahkan ke halaman store untuk customer
            }
            exit();
        } else {
            $error = "Nama pengguna atau kata sandi salah.";
        }
    } else {
        $error = "Nama pengguna atau kata sandi salah.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
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
        .login-container {
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
    <div class="login-container">
        <h2 class="text-center">Login</h2>
        <form method="post" action="login.php">
            <?php if (isset($error)) echo '<div class="alert alert-danger" role="alert">' . $error . '</div>'; ?>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Kata Sandi:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
            <div class="form-group text-center mt-3">
                <p>Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
            </div>
        </form>
    </div>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

