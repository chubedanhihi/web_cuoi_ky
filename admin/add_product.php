<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
require_once '../config.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['productName'] ?? '');
    $price = (float)($_POST['listPrice'] ?? 0);
    $discount = (float)($_POST['discountPercent'] ?? 0);
    $stock = (int)($_POST['stock_quantity'] ?? 0);
    $desc = trim($_POST['description'] ?? '');
    $categoryName = trim($_POST['categoryName'] ?? '');

    if ($name === '' || $categoryName === '') {
        $error = "❌ Vui lòng nhập đầy đủ tên sản phẩm và danh mục!";
    } else {
        // Kiểm tra danh mục đã có chưa
        $stmtCat = $conn->prepare("SELECT categoryID FROM categories WHERE categoryName = ?");
        $stmtCat->bind_param("s", $categoryName);
        $stmtCat->execute();
        $resultCat = $stmtCat->get_result();

        if ($resultCat->num_rows > 0) {
            $categoryID = $resultCat->fetch_assoc()['categoryID'];
        } else {
            // Thêm mới danh mục
            $stmtInsertCat = $conn->prepare("INSERT INTO categories (categoryName) VALUES (?)");
            $stmtInsertCat->bind_param("s", $categoryName);
            $stmtInsertCat->execute();
            $categoryID = $stmtInsertCat->insert_id;
        }

        // Xử lý ảnh
        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $folder = "../uploads/";
            if (!is_dir($folder)) mkdir($folder, 0755, true);
            $imagePath = "uploads/" . time() . "_" . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], "../" . $imagePath);
        }

        // Thêm sản phẩm
        $stmt = $conn->prepare("
            INSERT INTO products 
            (productName, description, image, listPrice, discountPercent, stock_quantity, categoryID)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssddii", $name, $desc, $imagePath, $price, $discount, $stock, $categoryID);

        if ($stmt->execute()) {
            $success = "✔ Thêm sản phẩm thành công";
        } else {
            $error = "❌ Lỗi khi thêm sản phẩm: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm sản phẩm</title>
    <link rel="stylesheet" href="../css/product_form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

    <div class="form-container">
        <h2><i class="fa-solid fa-plus"></i> Thêm sản phẩm</h2>

        <?php if ($success): ?><div class="alert success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label>Tên sản phẩm</label>
            <input type="text" name="productName" required>

            <label>Danh mục</label>
            <input type="text" name="categoryName" placeholder="Nhập danh mục..." required>

            <label>Giá bán</label>
            <input type="number" name="listPrice" step="0.01" required>

            <label>Giảm giá (%)</label>
            <input type="number" name="discountPercent" value="0" min="0" max="100">

            <label>Tồn kho</label>
            <input type="number" name="stock_quantity" value="0" min="0">

            <label>Mô tả</label>
            <textarea name="description"></textarea>

            <label>Ảnh sản phẩm</label>
            <input type="file" name="image" accept="image/*">

            <div class="form-actions">
                <button type="submit" class="btn primary">
                    <i class="fa fa-save"></i> Lưu
                </button>
                <a href="products.php" class="btn">
                    <i class="fa fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </form>
    </div>

</body>

</html>