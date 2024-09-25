<?php
include '../includes/db_connect.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Mendapatkan jumlah customer dan jumlah orderan
$sql_customers = "SELECT COUNT(*) AS total_customers FROM users WHERE role = 'customer'";
$sql_orders = "SELECT COUNT(*) AS total_orders FROM orders WHERE status IN ('accepted', 'pending')";

$result_customers = $conn->query($sql_customers);
$result_orders = $conn->query($sql_orders);

$total_customers = $result_customers->fetch_assoc()['total_customers'];
$total_orders = $result_orders->fetch_assoc()['total_orders'];

// Mendapatkan tanggal pengiriman dari filter jika ada
$filter_date_start = isset($_POST['date_start']) ? $_POST['date_start'] : '';
$filter_date_end = isset($_POST['date_end']) ? $_POST['date_end'] : '';

// Query untuk orderan dengan status "accepted" dan filter tanggal pengiriman
$sql_accepted_orders = "
    SELECT o.order_id, o.user_id, o.order_date, o.shipping_date, o.total, o.shipping_address, 
           GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ' x ', FORMAT(oi.price, 2), ')') SEPARATOR ', ') AS items
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN products p ON oi.product_id = p.product_id
    WHERE o.status = 'accepted'
    " . ($filter_date_start ? "AND o.shipping_date >= '$filter_date_start'" : '') . "
    " . ($filter_date_end ? "AND o.shipping_date <= '$filter_date_end'" : '') . "
    GROUP BY o.order_id
    ORDER BY o.shipping_date DESC
";
$result_accepted_orders = $conn->query($sql_accepted_orders);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-2 d-md-block sidebar">
                <h4 class="text-center">Admin Dashboard</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Manage_Product.php">Manage Product</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_orders.php">Manage Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="order_summary.php">Order Summary</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">Logout</a>
                    </li>
                </ul>
            </nav>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4 main-content">
                <h1>Selamat datang, Admin!</h1>
                <div class="alert alert-info" role="alert">
                    Ini adalah halaman dashboard admin. Anda dapat mengakses berbagai fitur melalui menu di sebelah kiri.
                </div>
                
                <!-- Dashboard Statistics -->
                <div class="dashboard-stats mb-4">
                    <div class="stat-card badge badge-primary">
                        <i class="fas fa-users"></i>
                        <div class="stat-value"><?php echo htmlspecialchars($total_customers); ?></div>
                        <div class="stat-label">Customers</div>
                    </div>
                    <div class="stat-card badge badge-success">
                        <i class="fas fa-box"></i>
                        <div class="stat-value"><?php echo htmlspecialchars($total_orders); ?></div>
                        <div class="stat-label">Orders</div>
                    </div>
                </div>
                
                <!-- Formulir Filter -->
                <h2>Filter Orderan</h2>
                <form method="post" action="">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="date_start">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="date_start" name="date_start" value="<?php echo htmlspecialchars($filter_date_start); ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="date_end">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="date_end" name="date_end" value="<?php echo htmlspecialchars($filter_date_end); ?>">
                        </div>
                        <div class="form-group col-md-4 align-self-end">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>

                <!-- Tabel Orderan dengan Status Accepted dan Barang yang Dipesan -->
                <h2>Orderan yang Diterima</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID Order</th>
                            <th>ID Pelanggan</th>
                            <th>Tanggal Order</th>
                            <th>Tanggal Pengiriman</th>
                            <th>Total</th>
                            <th>Alamat Pengiriman</th>
                            <th>Barang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_accepted_orders->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['order_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['shipping_date']); ?></td>
                                <td><?php echo htmlspecialchars(number_format($row['total'], 2, ',', '.')); ?></td>
                                <td><?php echo htmlspecialchars($row['shipping_address']); ?></td>
                                <td><?php echo htmlspecialchars($row['items']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
