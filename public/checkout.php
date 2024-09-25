<?php
session_start();
include '../includes/db_connect.php'; // Menghubungkan ke database

// Mengecek jika pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Mengambil data pengguna
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
$user_name = $user_row['username']; // Menggunakan username
$user_stmt->close();

// Mendapatkan user_id dari sesi
$user_id = $_SESSION['user_id'];

// Mendapatkan data dari cart
$sql_cart = "SELECT c.cart_id, c.product_id, c.quantity, p.name, p.price
             FROM cart c
             JOIN products p ON c.product_id = p.product_id
             WHERE c.user_id = ?";
$cart_stmt = $conn->prepare($sql_cart);

// Cek apakah prepare berhasil
if ($cart_stmt === false) {
    die('Prepare failed: ' . $conn->error);
}

$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();

$items = [];
$total_amount = 0;

while ($row = $cart_result->fetch_assoc()) {
    $subtotal = $row['price'] * $row['quantity'];
    $items[] = $row;
    $total_amount += $subtotal;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $region = $_POST['region'];
    $detail_address = $_POST['detail_address'];
    $shipping_address = $region . ' - ' . $detail_address; // Gabungkan wilayah dan detail alamat
    $shipping_date = $_POST['shipping_date'];
    $proof_of_transfer = $_FILES['proof_of_transfer']['name'];
    $proof_of_transfer_tmp = $_FILES['proof_of_transfer']['tmp_name'];
    $proof_of_transfer_path = '../uploads/' . basename($proof_of_transfer);

    // Validasi file upload
    if (!empty($proof_of_transfer) && move_uploaded_file($proof_of_transfer_tmp, $proof_of_transfer_path)) {
        // Mendapatkan tanggal pesanan saat ini
        $order_date = date('Y-m-d H:i:s');

        // Insert order ke database
        $sql_order = "INSERT INTO orders (user_id, status, total, proof_of_transfer, shipping_address, shipping_date, order_date)
                      VALUES (?, 'pending', ?, ?, ?, ?, ?)";
        $order_stmt = $conn->prepare($sql_order);

        // Cek apakah prepare berhasil
        if ($order_stmt === false) {
            die('Prepare failed: ' . $conn->error);
        }

        $order_stmt->bind_param("idssss", $user_id, $total_amount, $proof_of_transfer, $shipping_address, $shipping_date, $order_date);
        $order_stmt->execute();
        $order_id = $conn->insert_id;

        // Insert items ke order_items
        $sql_items = "INSERT INTO order_items (order_id, product_id, quantity, price)
                      VALUES (?, ?, ?, ?)";
        $items_stmt = $conn->prepare($sql_items);

        // Cek apakah prepare berhasil
        if ($items_stmt === false) {
            die('Prepare failed: ' . $conn->error);
        }

        foreach ($items as $item) {
            $items_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $items_stmt->execute();
        }

        // Hapus item dari keranjang setelah checkout
        $sql_delete_cart = "DELETE FROM cart WHERE user_id = ?";
        $delete_cart_stmt = $conn->prepare($sql_delete_cart);

        // Cek apakah prepare berhasil
        if ($delete_cart_stmt === false) {
            die('Prepare failed: ' . $conn->error);
        }

        $delete_cart_stmt->bind_param("i", $user_id);
        $delete_cart_stmt->execute();

        // Redirect ke halaman konfirmasi
        header("Location: order_confirmation.php?order_id=$order_id");
        exit();
    } else {
        $error = "Gagal mengunggah bukti transfer.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/style.css" rel="stylesheet">
    <style>
        @media print {
            .print-btn {
                display: none;
            }
        }
        .order-summary {
            margin-right: 20px;
        }
        .account-info {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .account-info img {
            max-width: 100%;
            height: auto;
        }
        .checkout-container {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
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
                <a class="nav-link" href="order_history.php">Order History</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="cart.php">Cart</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($user_name); ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item text-danger" href="../admin/logout.php">Logout</a>
                </div>
            </li>
        </ul>
    </div>
</nav>

<main role="main" class="container mt-4">
    <h1>Checkout</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="checkout-container">
        <div class="order-summary flex-grow-1">
            <h3>Order Summary</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item):
                        $subtotal = $item['price'] * $item['quantity'];
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>Rp <?php echo number_format($item['price'], 2, ',', '.'); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>Rp <?php echo number_format($subtotal, 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Total</th>
                        <th>Rp <?php echo number_format($total_amount, 2, ',', '.'); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="account-info">
            <h3>Account Information</h3>
            <img src="../images/qr.png" alt="Account Number Image"> <!-- Update with actual image path -->
            <p><strong>No Rekening:</strong> <p>Bank DKI: 123-456-7890</p>
            <p>Bank Mandiri: 123-456-7890</p> <!-- Update this with actual account number -->
            <p>Bank BRI: 123-456-7890</p> <!-- Update this with actual account number -->
        </div>
    </div>

    <form method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="region">Wilayah:</label>
        <select id="region" name="region" class="form-control" required>
            <option value="" disabled selected>Pilih Wilayah</option>
            <option value="Jakarta Pusat">Jakarta Pusat</option>
            <option value="Jakarta Barat">Jakarta Barat</option>
            <option value="Jakarta Selatan">Jakarta Selatan</option>
            <option value="Jakarta Utara">Jakarta Utara</option>
            <option value="Jakarta Timur">Jakarta Timur</option>
            <option value="Depok">Depok</option>
            <option value="Bekasi">Bekasi</option>
        </select>
    </div>
    <div class="form-group">
        <label for="detail_address">Detail Alamat:</label>
        <textarea id="detail_address" name="detail_address" class="form-control" rows="3" required></textarea>
    </div>
    <div class="form-group">
        <label for="shipping_date">Tanggal dan Waktu Pengiriman:</label>
        <input type="datetime-local" id="shipping_date" name="shipping_date" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="proof_of_transfer">Unggah Bukti Transfer:</label>
        <input type="file" id="proof_of_transfer" name="proof_of_transfer" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Place Order</button>
</form>

</main>
<?php include '../includes/footer.php';?>
<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
