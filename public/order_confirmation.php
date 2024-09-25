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

// Mengambil order_id dari parameter URL
if (!isset($_GET['order_id'])) {
    header("Location: store.php");
    exit();
}

$order_id = intval($_GET['order_id']);

// Mengambil detail pesanan dari database
$sql_order = "SELECT o.order_id, o.user_id, o.status, o.total, o.proof_of_transfer, o.shipping_address, o.shipping_date, u.username 
              FROM orders o 
              JOIN users u ON o.user_id = u.user_id 
              WHERE o.order_id = ?";
$order_stmt = $conn->prepare($sql_order);
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order = $order_result->fetch_assoc();

if (!$order) {
    header("Location: store.php");
    exit();
}

// Mengambil item pesanan
$sql_items = "SELECT p.name, p.price, oi.quantity 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.product_id 
              WHERE oi.order_id = ?";
$items_stmt = $conn->prepare($sql_items);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
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

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4 main-content">
                <h1>Order Confirmation</h1>

                <div class="alert alert-success" role="alert">
                    Your order has been placed successfully!
                </div>

                <h3>Order Details</h3>
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
                        <?php while ($item = $items_result->fetch_assoc()):
                            $subtotal = $item['price'] * $item['quantity'];
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td>Rp <?php echo number_format($item['price'], 2, ',', '.'); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>Rp <?php echo number_format($subtotal, 2, ',', '.'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">Total</th>
                            <th>Rp <?php echo number_format($order['total'], 2, ',', '.'); ?></th>
                        </tr>
                    </tfoot>
                </table>

                <h3>Order Summary</h3>
                <p><strong>Order ID:</strong> <?php echo $order['order_id']; ?></p>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
                <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                <p><strong>Shipping Date:</strong> <?php echo htmlspecialchars($order['shipping_date']); ?></p>

                <?php if ($order['proof_of_transfer']): ?>
                    <h3>Proof of Transfer</h3>
                    <p>File: <a href="../uploads/<?php echo htmlspecialchars($order['proof_of_transfer']); ?>" target="_blank"><?php echo htmlspecialchars($order['proof_of_transfer']); ?></a></p>
                <?php endif; ?>

                <a href="store.php" class="btn btn-primary">Continue Shopping</a>
                <button class="btn btn-secondary print-btn" onclick="window.print()">Print Order Summary</button>
            </main>
        </div>
    </div>
    <?php include '../includes/footer.php';?>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
