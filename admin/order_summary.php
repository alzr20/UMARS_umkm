<?php
include '../includes/db_connect.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil filter dari query string
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$address_filter = isset($_GET['address']) ? $_GET['address'] : '';
$user_id_filter = isset($_GET['user_id']) ? $_GET['user_id'] : '';

// Query untuk jumlah orderan dan total pendapatan
$sql = "SELECT COUNT(order_id) AS total_orders, SUM(total) AS total_revenue
        FROM orders
        WHERE status = 'accepted'";
if ($start_date && $end_date) {
    $sql .= " AND order_date BETWEEN '$start_date' AND '$end_date'";
}
if ($status_filter) {
    $sql .= " AND status = '$status_filter'";
}
if ($address_filter) {
    $sql .= " AND shipping_address LIKE '%" . $conn->real_escape_string($address_filter) . "%'";
}

if ($user_id_filter) {
    $sql .= " AND user_id = '" . $conn->real_escape_string($user_id_filter) . "'";
}
$result = $conn->query($sql);
if (!$result) {
    die("Query gagal: " . $conn->error);
}

$data = $result->fetch_assoc();
$total_orders = $data['total_orders'];
$total_revenue = $data['total_revenue'];

// Format total_revenue dalam format Rupiah
$total_revenue_idr = "Rp " . number_format($total_revenue, 2, ',', '.');

// Query untuk jumlah pesanan berdasarkan status
$status_counts_sql = "SELECT status, COUNT(order_id) AS count
                      FROM orders
                      WHERE status IN ('accepted', 'rejected', 'pending')";
if ($start_date && $end_date) {
    $status_counts_sql .= " AND order_date BETWEEN '$start_date' AND '$end_date'";
}
if ($address_filter) {
    $status_counts_sql .= " AND shipping_address LIKE '%" . $conn->real_escape_string($address_filter) . "%'";
}
if ($user_id_filter) {
    $status_counts_sql .= " AND user_id = '" . $conn->real_escape_string($user_id_filter) . "'";
}
$status_counts_sql .= " GROUP BY status";
$status_counts_result = $conn->query($status_counts_sql);

if (!$status_counts_result) {
    die("Query gagal: " . $conn->error);
}

$status_counts = [
    'accepted' => 0,
    'rejected' => 0,
    'pending' => 0
];
while ($row = $status_counts_result->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}

// Query untuk barang yang terjual
$sold_items_sql = "SELECT p.name AS product_name, SUM(oi.quantity) AS quantity_sold, SUM(oi.quantity * p.price) AS total_revenue
                   FROM order_items oi
                   JOIN products p ON oi.product_id = p.product_id
                   JOIN orders o ON oi.order_id = o.order_id
                   WHERE o.status = 'accepted'";
if ($start_date && $end_date) {
    $sold_items_sql .= " AND o.order_date BETWEEN '$start_date' AND '$end_date'";
}
if ($status_filter) {
    $sold_items_sql .= " AND o.status = '$status_filter'";
}
if ($address_filter) {
    $sold_items_sql .= " AND o.shipping_address LIKE '%" . $conn->real_escape_string($address_filter) . "%'";
}
if ($user_id_filter) {
    $sold_items_sql .= " AND o.user_id = '" . $conn->real_escape_string($user_id_filter) . "'";
}
$sold_items_sql .= " GROUP BY p.name ORDER BY p.name";
$sold_items_result = $conn->query($sold_items_sql);

if (!$sold_items_result) {
    die("Query gagal: " . $conn->error);
}

// Query untuk mengambil data user, kecualikan admin
$users_sql = "SELECT user_id, username FROM users WHERE role != 'admin'";
$users_result = $conn->query($users_sql);

if (!$users_result) {
    die("Query gagal: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ringkasan Pesanan</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/style.css" rel="stylesheet">
    <!-- Print CSS -->
    <style>
        @media print {
            .no-print {
                display: none;
            }
            #sidebar {
                display: none;
            }
            .main-content {
                width: 100%;
            }
        }
        .btn-print-spacing {
            margin-bottom: 20px; /* Adjust spacing as needed */
        }
    </style>
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

            <main role="main" class="col-md-10 ml-sm-auto col-lg-10 px-4 main-content">
                <h1>Ringkasan Pesanan</h1>

                <!-- Form Filter -->
                <form method="GET" action="order_summary.php" class="mb-4">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="start_date">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="end_date">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">Semua Status</option>
                                <option value="accepted" <?php echo $status_filter == 'accepted' ? 'selected' : ''; ?>>Diterima</option>
                                <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Ditolak</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
    <label for="address">Wilayah</label>
    <select class="form-control" id="address" name="address">
        <option value="">Semua Wilayah</option>
        <option value="Jakarta Pusat" <?php echo $address_filter == 'Jakarta Pusat' ? 'selected' : ''; ?>>Jakarta Pusat</option>
        <option value="Jakarta Barat" <?php echo $address_filter == 'Jakarta Barat' ? 'selected' : ''; ?>>Jakarta Barat</option>
        <option value="Jakarta Selatan" <?php echo $address_filter == 'Jakarta Selatan' ? 'selected' : ''; ?>>Jakarta Selatan</option>
        <option value="Jakarta Timur" <?php echo $address_filter == 'Jakarta Timur' ? 'selected' : ''; ?>>Jakarta Timur</option>
        <option value="Jakarta Utara" <?php echo $address_filter == 'Jakarta Utara' ? 'selected' : ''; ?>>Jakarta Utara</option>
        <option value="Bekasi" <?php echo $address_filter == 'Bekasi' ? 'selected' : ''; ?>>Bekasi</option>
        <option value="Depok" <?php echo $address_filter == 'Depok' ? 'selected' : ''; ?>>Depok</option>
    </select>
</div>

                        <div class="form-group col-md-3">
                            <label for="user_id">User ID</label>
                            <select class="form-control" id="user_id" name="user_id">
                                <option value="">Semua Pengguna</option>
                                <?php while ($user = $users_result->fetch_assoc()) { ?>
                                    <option value="<?php echo htmlspecialchars($user['user_id']); ?>" <?php echo $user_id_filter == $user['user_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['username']); ?> (ID: <?php echo htmlspecialchars($user['user_id']); ?>)
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3 align-self-end">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>

                <!-- Tombol Print dengan jarak -->
                <button class="btn btn-secondary no-print btn-print-spacing" onclick="window.print()">Print Halaman Ini</button>

                <!-- Tabel Ringkasan Pesanan -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>Total Orderan</th>
                            <th>Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo number_format($total_orders); ?></td>
                            <td><?php echo $total_revenue_idr; ?></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Tabel Status Pesanan -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Diterima</td>
                            <td><?php echo number_format($status_counts['accepted']); ?></td>
                        </tr>
                        <tr>
                            <td>Ditolak</td>
                            <td><?php echo number_format($status_counts['rejected']); ?></td>
                        </tr>
                        <tr>
                            <td>Menunggu</td>
                            <td><?php echo number_format($status_counts['pending']); ?></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Tabel Barang yang Terjual -->
                <h2>Barang yang Terjual</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Jumlah Terjual</th>
                            <th>Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $sold_items_result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo number_format($row['quantity_sold']); ?></td>
                                <td><?php echo "Rp " . number_format($row['total_revenue'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
