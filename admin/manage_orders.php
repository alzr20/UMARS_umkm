<?php
include '../includes/db_connect.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// Ambil data pesanan dengan nama pengguna dan barang yang dipesan, urutkan berdasarkan tanggal pesanan terbaru
$sql = "
    SELECT o.order_id, u.username, u.phone_number, o.status, o.order_date, o.shipping_date, o.total, o.proof_of_transfer, o.shipping_address, o.refund, o.note
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
";
$result = $conn->query($sql);
if (!$result) {
    die("Query gagal: " . $conn->error);
}

// Fungsi untuk mendapatkan items yang dipesan
function getOrderItems($order_id, $conn) {
    $sql = "SELECT p.name, oi.quantity
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare statement failed: " . $conn->error);
    }

    $stmt->bind_param("i", $order_id);
    if (!$stmt->execute()) {
        die("Execute statement failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row['name'] . " (x" . $row['quantity'] . ")";
    }
    $stmt->close();
    
    return implode(", ", $items);
}

// Update status pesanan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_status'])) {
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];
        $note = $_POST['note'];

        // Handle status update without file uploads
        $sql = "UPDATE orders SET status = ?, note = ? WHERE order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $status, $note, $order_id);
        if ($stmt->execute()) {
            $message = "Status pesanan berhasil diperbarui.";
        } else {
            $message = "Gagal memperbarui status pesanan: " . $stmt->error;
        }
        $stmt->close();
        header("Location: manage_orders.php");
        exit();
    }

    if (isset($_POST['upload_refund'])) {
        $order_id = $_POST['order_id'];
        $existing_refund = $_POST['existing_refund'];

        // Handle file upload for refund photo
        if (isset($_FILES['refund']) && $_FILES['refund']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/';
            $upload_file = $upload_dir . basename($_FILES['refund']['name']);
            if (move_uploaded_file($_FILES['refund']['tmp_name'], $upload_file)) {
                $refund_photo = basename($_FILES['refund']['name']);
            } else {
                $message = "Gagal mengupload foto refund.";
            }
        } else {
            $refund_photo = $existing_refund; // Keep existing photo if no new upload
        }

        $sql = "UPDATE orders SET refund = ? WHERE order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $refund_photo, $order_id);
        if ($stmt->execute()) {
            $message = "Foto refund berhasil diupload.";
        } else {
            $message = "Gagal mengupload foto refund: " . $stmt->error;
        }
        $stmt->close();
        header("Location: manage_orders.php");
        exit();
    }

    if (isset($_POST['update_note'])) {
        $order_id = $_POST['order_id'];
        $note = $_POST['note'];

        // Handle note update
        $sql = "UPDATE orders SET note = ? WHERE order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $note, $order_id);
        if ($stmt->execute()) {
            $message = "Note berhasil diperbarui.";
        } else {
            $message = "Gagal memperbarui note: " . $stmt->error;
        }
        $stmt->close();
        header("Location: manage_orders.php");
        exit();
    }
}

$total_profit = 0;
$result->data_seek(0);
while ($row = $result->fetch_assoc()) {
    if ($row['status'] == 'accepted') {
        $total_profit += $row['total'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <nav id="sidebar" class="col-md-2 d-md-block sidebar">
            <h4 class="text-center">Admin Dashboard</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="admin_dashboard.php"><i class="fi fi-rs-house-chimney"></i>Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Manage_Product.php"><i class="fi fi-ss-box-open"></i>Manage Product</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_orders.php"><i class="fi fi-rr-order-history"></i>Manage Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="order_summary.php"><i class="fi fi-ss-summary-check"></i>Order Summary</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php"><i class="fi fi-br-exit"></i>Logout</a>
                </li>
            </ul>
        </nav>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4 main-content">
            <h1>Manage Orders</h1>
            <?php if (isset($message)) echo "<div class='alert alert-info' role='alert'>$message</div>"; ?>
            
            <div id="printable-area">
                <div class="horizontal-scroll">
                <div class="container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Order ID</th>
                            <th>Username</th>
                            <th>Phone Number</th>
                            <th>Items Ordered</th>
                            <th>Order Date</th>
                            <th>Shipping Date & Time</th>
                            <th>Total</th>
                            <th>Proof of Transfer</th>
                            <th>Shipping Address</th>
                            <th>Status</th>
                            <th>Refund</th>
                            <th>Note</th>
                            <th>Update Status</th>
                            <th>Upload Refund Photo</th>
                            <th>Update Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $result->data_seek(0);
                        while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                                <td><?php echo htmlspecialchars(getOrderItems($row['order_id'], $conn)); ?></td>
                                <td><?php echo date("d M Y", strtotime($row['order_date'])); ?></td>
                                <td><?php echo date("d M Y, H:i", strtotime($row['shipping_date'])); ?></td>
                                <td><?php echo number_format($row['total'], 2); ?></td>
                                <td>
                                    <?php if ($row['proof_of_transfer']): ?>
                                        <a href="../uploads/<?php echo htmlspecialchars($row['proof_of_transfer']); ?>" target="_blank">View Proof</a>
                                    <?php else: ?>
                                        No Proof
                                    <?php endif; ?>
                                </td>
                                <td><?php echo nl2br(htmlspecialchars($row['shipping_address'])); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td>
                                    <?php if ($row['refund'] != 'none'): ?>
                                        <a href="../uploads/<?php echo htmlspecialchars($row['refund']); ?>" target="_blank">View Refund Photo</a>
                                    <?php else: ?>
                                        No Photo
                                    <?php endif; ?>
                                </td>
                                <td><?php echo nl2br(htmlspecialchars($row['note'])); ?></td>
                                <td>
                                    <!-- Form for updating status -->
                                    <form method="post" action="manage_orders.php" style="margin-bottom: 10px;">
                                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['order_id']); ?>">
                                        <select name="status" class="form-control">
                                            <option value="pending" <?php if ($row['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                            <option value="accepted" <?php if ($row['status'] == 'accepted') echo 'selected'; ?>>Accepted</option>
                                            <option value="rejected" <?php if ($row['status'] == 'rejected') echo 'selected'; ?>>Rejected</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-primary btn-sm mt-2">Update Status</button>
                                    </form>
                                </td>
                                <td>
                                    <!-- Form for uploading refund photo -->
                                    <form method="post" action="manage_orders.php" enctype="multipart/form-data" style="margin-bottom: 10px;">
                                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['order_id']); ?>">
                                        <input type="hidden" name="existing_refund" value="<?php echo htmlspecialchars($row['refund']); ?>">
                                        <div class="form-group">
                                            <label for="refund">Refund Photo:</label>
                                            <input type="file" name="refund" class="form-control-file" id="refund">
                                            <small class="form-text text-muted">Upload photo related to refund.</small>
                                        </div>
                                        <button type="submit" name="upload_refund" class="btn btn-secondary btn-sm mt-2">Upload Refund Photo</button>
                                    </form>
                                </td>
                                <td>
                                    <!-- Form for updating note -->
                                    <form method="post" action="manage_orders.php" style="margin-bottom: 10px;">
                                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['order_id']); ?>">
                                        <div class="form-group mt-2">
                                            <label for="note">Note:</label>
                                            <textarea name="note" class="form-control" id="note"><?php echo htmlspecialchars($row['note']); ?></textarea>
                                        </div>
                                        <button type="submit" name="update_note" class="btn btn-info btn-sm mt-2">Update Note</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    function printReport() {
        window.print();
    }
</script>
</body>
</html>
