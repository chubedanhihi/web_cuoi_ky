<?php
// model/product_db.php
// ĐÃ CHUYỂN HOÀN TOÀN SANG MySQLi – HOẠT ĐỘNG NGAY VỚI config.php

/**
 * Lấy tất cả sản phẩm + tên danh mục
 */
function get_all_products() {
    global $conn;

    $sql = "SELECT p.*, c.categoryName 
            FROM products p 
            LEFT JOIN categories c ON p.categoryID = c.categoryID 
            ORDER BY p.productID DESC";

    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * Lấy sản phẩm theo danh mục
 */
function get_products_by_category($category_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT p.*, c.categoryName 
                            FROM products p 
                            LEFT JOIN categories c ON p.categoryID = c.categoryID 
                            WHERE p.categoryID = ? 
                            ORDER BY p.productID DESC");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $data;
}

/**
 * Lấy 1 sản phẩm chi tiết
 */
function get_product($product_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT p.*, c.categoryName 
                            FROM products p 
                            LEFT JOIN categories c ON p.categoryID = c.categoryID 
                            WHERE p.productID = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->num_rows > 0 ? $result->fetch_assoc() : null;
    $stmt->close();

    return $product;
}

/**
 * Đếm số đơn hàng chứa sản phẩm (nếu cần)
 */
function get_product_order_count($product_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT COUNT(*) AS orderCount FROM orderitems WHERE productID = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row['orderCount'] ?? 0;
}

/**
 * Thêm sản phẩm mới
 */
function add_product($category_id, $name, $description, $price, $discount_percent = 0, $image = null) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO products 
        (categoryID, productName, description, listPrice, discountPercent, image) 
        VALUES (?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("issdss", 
        $category_id,
        $name,
        $description,
        $price,
        $discount_percent,
        $image
    );

    $success = $stmt->execute();
    $insert_id = $success ? $conn->insert_id : 0;
    $stmt->close();

    return $insert_id;
}

/**
 * Cập nhật sản phẩm
 */
function update_product($product_id, $name, $desc, $price, $discount, $category_id, $image = null) {
    global $conn;

    $stmt = $conn->prepare("UPDATE products SET 
        categoryID = ?, 
        productName = ?, 
        description = ?, 
        listPrice = ?, 
        discountPercent = ?, 
        image = ? 
        WHERE productID = ?");

    $stmt->bind_param("issddsi",
        $category_id,
        $name,
        $desc,
        $price,
        $discount,
        $image,
        $product_id
    );

    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

/**
 * Xóa sản phẩm + xóa ảnh cũ
 */
function delete_product($product_id) {
    global $conn;

    // Lấy thông tin ảnh trước khi xóa
    $product = get_product($product_id);
    if ($product && !empty($product['image'])) {
        $image_path = '../images/' . $product['image']; // hoặc ../images/ tùy bạn
        if (file_exists($image_path)) {
            @unlink($image_path);
        }
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE productID = ?");
    $stmt->bind_param("i", $product_id);
    $success = $stmt->execute();
    $stmt->close();

    return $success;
}
?>