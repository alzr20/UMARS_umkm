<?php
session_start();
include '../includes/db_connect.php';


// Mengambil data pengguna jika sudah login
$user_name = "";
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_sql = "SELECT username FROM users WHERE user_id = ?";
    $user_stmt = $conn->prepare($user_sql);

    if ($user_stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_result === false) {
        die("Error executing query: " . $user_stmt->error);
    }

    $user_row = $user_result->fetch_assoc();
    $user_name = $user_row['username'];
    $user_stmt->close();
}

// Mengambil data produk
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

if ($result === false) {
    die("Error executing query: " . $conn->error);
}

// Menambahkan produk ke keranjang
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../admin/login.php");
        exit();
    }
    $product_id = $_POST['product_id'];
    $user_id = $_SESSION['user_id'];

    // Cek jika produk sudah ada di keranjang
    $check_sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $check_stmt = $conn->prepare($check_sql);

    if ($check_stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $check_stmt->bind_param("ii", $user_id, $product_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Update quantity jika produk sudah ada
        $update_sql = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
        $update_stmt = $conn->prepare($update_sql);

        if ($update_stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }

        $update_stmt->bind_param("ii", $user_id, $product_id);
        $update_stmt->execute();
    } else {
        // Insert produk baru ke keranjang
        $insert_sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)";
        $insert_stmt = $conn->prepare($insert_sql);

        if ($insert_stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }

        $insert_stmt->bind_param("ii", $user_id, $product_id);
        $insert_stmt->execute();
    }

    $_SESSION['cart_message'] = "Produk telah ditambahkan ke keranjang. Anda bisa melanjutkan berbelanja atau langsung ke keranjang.";
    header("Location: store.php");
    exit();
}

// Tutup statement dan koneksi database setelah exit
if (isset($check_stmt)) $check_stmt->close();
if (isset($update_stmt)) $update_stmt->close();
if (isset($insert_stmt)) $insert_stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/style.css" rel="stylesheet">
    <style>
        .custom-card {
            width: 100%; /* Make card full width within its column */
            margin: auto; /* Center the card */
        }

        .custom-card .card-img-top {
            height: 200px; /* Set a fixed height for the card image */
            object-fit: cover; /* Ensure the image covers the card without distortion */
        }

        @media (max-width: 768px) {
            .card-img-top {
                height: 150px; /* Adjust image height for smaller screens */
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-custom">
        <a class="navbar-brand" href="#">
            <img src="../images/logo.png" alt="Logo" width="100" height="55" class="d-inline-block align-top">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="store.php">Store</a>
                </li>
                <li class="nav-item">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a class="nav-link" href="order_history.php">Order History</a>
                    <?php else: ?>
                        <a class="nav-link" href="../admin/login.php">Order History</a>
                    <?php endif; ?>
                </li>
                <li class="nav-item">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a class="nav-link" href="cart.php">Cart</a>
                    <?php else: ?>
                        <a class="nav-link" href="../admin/login.php">Cart</a>
                    <?php endif; ?>
                </li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($user_name); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item text-danger" href="../admin/logout.php">Logout</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../admin/login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <!-- End Navbar -->

    <div class="container">
        <?php if($user_name): ?>
            <header class="mt-4">
                <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
            </header>
        <?php endif; ?>
        <div class="alert alert-info mt-4">
            <h3><strong>Pengumuman:</h2></strong> <h4>Minimal pemesanan adalah 10 item.</h4>
        </div>
        <div class="alert alert-info mt-4">
            <H4>Pemesanan Dilakukan 2 Hari Sebelum Pengiriman</H4>
        </div>
        
        <h1 class="mt-4">Store</h1>
        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-sm-12 col-md-6 col-lg-4 mb-4">
                        <div class="card custom-card">
                            <img src="../uploads/<?php echo htmlspecialchars($row['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                                <p class="card-text">Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></p>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                                <form method="post" action="store.php">
                                    <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                    <button type="submit" name="add_to_cart" class="btn btn-primary">Tambah ke Keranjang</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No products found.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php include '../includes/footer.php';?>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
