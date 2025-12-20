<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require_once '../config.php';

$id = (int)($_GET['id'] ?? 0);

// LẤY SẢN PHẨM + DANH MỤC
$stmt = $conn->prepare("
    SELECT p.*, c.categoryName
    FROM products p
    LEFT JOIN categories c ON p.categoryID = c.categoryID
    WHERE p.productID = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: products.php');
    exit;
}

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

        /* ====== XỬ LÝ DANH MỤC ====== */
        $stmtCat = $conn->prepare("SELECT categoryID FROM categories WHERE categoryName=?");
        $stmtCat->bind_param("s", $categoryName);
        $stmtCat->execute();
        $rsCat = $stmtCat->get_result();

        if ($rsCat->num_rows > 0) {
            $categoryID = $rsCat->fetch_assoc()['categoryID'];
        } else {
            $stmtInsertCat = $conn->prepare("INSERT INTO categories(categoryName) VALUES(?)");
            $stmtInsertCat->bind_param("s", $categoryName);
            $stmtInsertCat->execute();
            $categoryID = $stmtInsertCat->insert_id;
        }

        /* ====== XỬ LÝ ẢNH ====== */
        $imagePath = $product['image'];
        if (!empty($_FILES['image']['name'])) {
            $folder = "../uploads/";
            if (!is_dir($folder)) mkdir($folder, 0755, true);

            $imagePath = "uploads/" . time() . "_" . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], "../" . $imagePath);
        }

        /* ====== UPDATE ====== */
        $stmt = $conn->prepare("
            UPDATE products SET
                productName=?,
                description=?,
                image=?,
                listPrice=?,
                discountPercent=?,
                stock_quantity=?,
                categoryID=?
            WHERE productID=?
        ");
        $stmt->bind_param(
            "sssddiii",
            $name,
            $desc,
            $imagePath,
            $price,
            $discount,
            $stock,
            $categoryID,
            $id
        );

        if ($stmt->execute()) {
            $success = "✔ Cập nhật sản phẩm thành công";
        } else {
            $error = "❌ Lỗi cập nhật: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Sửa sản phẩm</title>
    <link rel="stylesheet" href="../css/product_form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

    <div class="form-container">
        <h2><i class="fa-solid fa-pen-to-square"></i> Sửa sản phẩm</h2>

        <?php if ($success): ?><div class="alert success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>

        <form method="post" enctype="multipart/form-data">

            <label>Tên sản phẩm</label>
            <input type="text" name="productName" value="<?= htmlspecialchars($product['productName']) ?>" required>

            <label>Danh mục</label>
            <input type="text" name="categoryName" value="<?= htmlspecialchars($product['categoryName']) ?>"
                placeholder="Nhập danh mục..." required>

            <label>Giá bán</label>
            <input type="number" name="listPrice" step="0.01" value="<?= $product['listPrice'] ?>" required>

            <label>Giảm giá (%)</label>
            <input type="number" name="discountPercent" min="0" max="100" value="<?= $product['discountPercent'] ?>">

            <label>Tồn kho</label>
            <input type="number" name="stock_quantity" min="0" value="<?= $product['stock_quantity'] ?>">

            <label>Mô tả</label>
            <textarea name="description"><?= htmlspecialchars($product['description']) ?></textarea>

            <?php if (!empty($product['image'])): ?>
            <label>Ảnh hiện tại</label>
            <div class="image-preview-box">
                <img src="../<?= $product['image'] ?>" class="preview-img">
            </div>
            <?php endif; ?>


            <label>Đổi ảnh</label>
            <input type="file" name="image" accept="image/*">

            <div class="form-actions">
                <button type="submit" class="btn primary">
                    <i class="fa fa-save"></i> Cập nhật
                </button>
                <a href="products.php" class="btn">
                    <i class="fa fa-arrow-left"></i> Quay lại
                </a>
            </div>

        </form>
    </div>

</body>

</html>