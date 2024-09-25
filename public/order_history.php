<?php
session_start();
include '../includes/db_connect.php';

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

// Mengambil riwayat pesanan dari database
$sql_orders = "SELECT o.order_id, o.status, o.total, o.shipping_address, o.shipping_date, o.proof_of_transfer, o.refund, o.note
               FROM orders o
               WHERE o.user_id = ?
               ORDER BY o.order_id DESC";
$orders_stmt = $conn->prepare($sql_orders);
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/style.css" rel="stylesheet">
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

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4 main-content">
    <h1>Order History</h1>
    <?php if ($orders_result->num_rows > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Order ID</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Shipping Address</th>
                    <th>Shipping Date</th>
                    <th>Proof of Transfer</th>
                    <th>Refund</th>
                    <th>Note</th>
                    <th>Invoice</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1; // Inisialisasi variabel nomor urut
                while ($order = $orders_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $no++; ?></td> <!-- Menampilkan nomor urut dan increment variabel -->
                        <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                        <td><?php echo ucfirst(htmlspecialchars($order['status'])); ?></td>
                        <td>Rp <?php echo number_format($order['total'], 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($order['shipping_address']); ?></td>
                        <td><?php echo htmlspecialchars($order['shipping_date']); ?></td>
                        <td>
                            <?php if ($order['proof_of_transfer']): ?>
                                <a href="../uploads/<?php echo htmlspecialchars($order['proof_of_transfer']); ?>" target="_blank">View Proof</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($order['refund']): ?>
                                <a href="../uploads/<?php echo htmlspecialchars($order['refund']); ?>" target="_blank">View Refund</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?php echo nl2br(htmlspecialchars($order['note'])); ?></td>
                        <td>
                            <a href="order_confirmation.php?order_id=<?php echo htmlspecialchars($order['order_id']); ?>" class="btn btn-primary btn-sm">View Details</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No orders found.</p>
    <?php endif; ?>
    
    <a href="store.php" class="btn btn-primary">Continue Shopping</a>
</main>
<?php include '../includes/footer.php';?>
<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
