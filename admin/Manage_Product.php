<?php
include '../includes/db_connect.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

function uploadFile($file) {
    $upload_dir = '../uploads/';
    $upload_file = $upload_dir . basename($file["name"]);
    
    // Pastikan folder uploads ada
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Periksa kesalahan file
    if ($file["error"] != UPLOAD_ERR_OK) {
        return false;
    }

    // Pindahkan file ke folder uploads
    if (move_uploaded_file($file["tmp_name"], $upload_file)) {
        return basename($file["name"]); // Hanya kembalikan nama file
    } else {
        return false;
    }
}

function unformatPrice($price) {
    // Hapus pemisah ribuan dan ganti koma dengan titik
    return str_replace([',', ''], ['.', ''], $price);
}

function formatRupiah($price) {
    // Format harga ke format rupiah
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Tambah produk
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_product'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = unformatPrice($_POST['price']); // Ubah format harga
        $price = (float)$price; // Konversi ke float
        $image_url = uploadFile($_FILES['image']); // Upload file

        // Debug: cek nilai harga
        error_log("Harga: " . $price);

        if ($image_url === false) {
            $message = "Gagal mengupload gambar.";
        } else {
            $sql = "INSERT INTO products (name, description, price, image_url) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssds", $name, $description, $price, $image_url);
            if ($stmt->execute()) {
                $message = "Produk berhasil ditambahkan.";
            } else {
                $message = "Gagal menambahkan produk: " . $stmt->error;
            }
            $stmt->close();
        }
    } elseif (isset($_POST['update_product'])) {
        $product_id = $_POST['product_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = unformatPrice($_POST['price']); // Ubah format harga
        $price = (float)$price; // Konversi ke float
        $image_url = isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE ? uploadFile($_FILES['image']) : $_POST['existing_image']; // Upload file baru atau gunakan yang lama

        // Debug: cek nilai harga
        error_log("Harga: " . $price);

        $sql = "UPDATE products SET name = ?, description = ?, price = ?, image_url = ? WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsi", $name, $description, $price, $image_url, $product_id);
        if ($stmt->execute()) {
            $message = "Produk berhasil diperbarui.";
        } else {
            $message = "Gagal memperbarui produk: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Hapus produk
if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];

    // Hapus gambar terkait dari server
    $sql = "SELECT image_url FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($image_url);
    $stmt->fetch();
    $stmt->close();

    if ($image_url && file_exists('../uploads/' . $image_url)) {
        unlink('../uploads/' . $image_url);
    }

    // Hapus entri yang merujuk ke produk yang akan dihapus
    $sql = "DELETE FROM order_items WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();

    // Hapus produk dari tabel products
    $sql = "DELETE FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    if ($stmt->execute()) {
        $message = "Produk berhasil dihapus.";
    } else {
        $message = "Gagal menghapus produk: " . $stmt->error;
    }
    $stmt->close();
}

// Ambil data produk
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
if (!$result) {
    die("Query gagal: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Product</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/style.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function(){
            $("#toggleAddForm").click(function(){
                $("#addProductForm").toggle();
            });

            $(".edit-button").click(function(){
                var productId = $(this).data("id");
                $("#updateProductForm").show();
                $("#updateProductForm input[name='product_id']").val(productId);

                // Fetch product data via AJAX and fill the form
                $.ajax({
                    url: 'fetch_product.php', // Create this file to return product data
                    type: 'GET',
                    data: { id: productId },
                    success: function(response) {
                        var product = JSON.parse(response);
                        $("#updateProductForm input[name='name']").val(product.name);
                        $("#updateProductForm textarea[name='description']").val(product.description);
                        $("#updateProductForm input[name='price']").val(product.price);
                        $("#updateProductForm img").attr("src", product.image_url);
                        $("#updateProductForm input[name='existing_image']").val(product.image_url);
                    }
                });
            });

            $("#price, #update_price").on('input', function() {
                var inputVal = $(this).val().replace(/[^0-9,-]/g, '');
                $(this).val(inputVal);
            });
        });
    </script>
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
                    <a class="nav-link active" href="Manage_Product.php">Manage Product</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_orders.php">Manage Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="order_summary.php">Order Summary</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">Logout</a>
                </li>
            </ul>
        </nav>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4 main-content">
            <h1>Manage Product</h1>
            <!-- Pesan hasil operasi -->
            <?php if (isset($message)) echo "<div class='alert alert-info' role='alert'>$message</div>"; ?>
            
            <!-- Tombol untuk menampilkan form tambah produk -->
            <button id="toggleAddForm" class="btn btn-primary mb-3">Tambah Produk</button>

            <!-- Form Tambah produk -->
            <div id="addProductForm" style="display: none;">
                <h2>Tambah Produk</h2>
                <form method="post" action="Manage_Product.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Nama Produk</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea id="description" name="description" class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="price">Harga</label>
                        <input type="text" id="price" name="price" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="image">Gambar</label>
                        <input type="file" id="image" name="image" class="form-control">
                    </div>
                    <button type="submit" name="add_product" class="btn btn-primary">Tambah Produk</button>
                </form>
            </div>

            <!-- Form Update produk -->
            <div id="updateProductForm" style="display: none;">
                <h2>Update Produk</h2>
                <form method="post" action="Manage_Product.php" enctype="multipart/form-data">
                    <input type="hidden" name="product_id">
                    <div class="form-group">
                        <label for="update_name">Nama Produk</label>
                        <input type="text" id="update_name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="update_description">Deskripsi</label>
                        <textarea id="update_description" name="description" class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="update_price">Harga</label>
                        <input type="text" id="update_price" name="price" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="update_image">Gambar (Opsional)</label>
                        <input type="file" id="update_image" name="image" class="form-control">
                        <input type="hidden" name="existing_image">
                    </div>
                    <button type="submit" name="update_product" class="btn btn-primary">Update Produk</button>
                </form>
            </div>

            <!-- Daftar produk -->
            <h2>Daftar Produk</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>Harga</th>
                        <th>Gambar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td><?= formatRupiah($row['price']) ?></td>
                            <td><img src="../uploads/<?= htmlspecialchars($row['image_url']) ?>" alt="Product Image" style="width: 100px;"></td>
                            <td>
                                <button class="btn btn-warning edit-button" data-id="<?= htmlspecialchars($row['product_id']) ?>">Edit</button>
                                <a href="Manage_Product.php?delete=<?= htmlspecialchars($row['product_id']) ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>

<?php
$conn->close();
?>
</div>
</body>
</html>
