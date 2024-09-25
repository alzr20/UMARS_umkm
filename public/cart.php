<?php
session_start();
include '../includes/db_connect.php';

// Mengecek jika pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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

// Mengambil data keranjang pengguna
$sql = "SELECT c.cart_id, p.name, p.price, c.quantity 
        FROM cart c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Mengupdate kuantitas produk di keranjang
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_cart'])) {
    $cart_ids = $_POST['cart_id'];
    $quantities = $_POST['quantity'];

    foreach ($cart_ids as $index => $cart_id) {
        $quantity = $quantities[$index];
        $update_sql = "UPDATE cart SET quantity = ? WHERE cart_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $quantity, $cart_id);
        $update_stmt->execute();
        $update_stmt->close();
    }
    header("Location: cart.php"); // Redirect untuk mencegah pengiriman ulang data
    exit();
}

// Menghapus produk dari keranjang
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_from_cart'])) {
    $cart_id = $_POST['remove_from_cart'];

    $delete_sql = "DELETE FROM cart WHERE cart_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $cart_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    header("Location: cart.php"); // Redirect untuk mencegah pengiriman ulang data
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
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
    <h1>Cart</h1>

    <!-- Tabel Keranjang -->
    <?php if ($result->num_rows > 0): ?>
        <form method="post" action="cart.php">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_price = 0;
                    while ($row = $result->fetch_assoc()): 
                        $subtotal = $row['price'] * $row['quantity'];
                        $total_price += $subtotal;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td>Rp <?php echo number_format($row['price'], 2, ',', '.'); ?></td>
                            <td>
                                <input type="number" name="quantity[]" value="<?php echo $row['quantity']; ?>" class="form-control" min="1" required>
                                <input type="hidden" name="cart_id[]" value="<?php echo $row['cart_id']; ?>">
                            </td>
                            <td>Rp <?php echo number_format($subtotal, 2, ',', '.'); ?></td>
                            <td>
                                <!-- Button untuk menghapus produk dari keranjang -->
                                <button type="submit" name="remove_from_cart" value="<?php echo $row['cart_id']; ?>" class="btn btn-danger">Remove</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Total</th>
                        <th>Rp <?php echo number_format($total_price, 2, ',', '.'); ?></th>
                        <th>
                            <!-- Button untuk mengupdate kuantitas semua produk -->
                            <button type="submit" name="update_cart" class="btn btn-warning">Update Quantities</button>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </form>
        <a href="./checkout.php" class="btn btn-primary">Proceed to Checkout</a>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</main>
<?php include '../includes/footer.php';?>
<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
