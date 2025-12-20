<?php
// model/category_db.php
// CHUẨN MySQLi – KHÔNG CÒN PDO

function get_categories() {
    global $conn; // đổi từ $connect thành $conn

    $sql = "SELECT categoryID, categoryName FROM categories ORDER BY categoryName ASC";
    $result = $conn->query($sql);

    if (!$result) {
        error_log("get_categories() error: " . $conn->error);
        return [];
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}
?>