<?php
session_start();
require_once 'config.php';
require_once 'model/product_db.php';
require_once 'model/config_shipping.php';

/* ================== LẤY USER ================== */
$user_id = $_SESSION['user_id'] ?? null;

/* ================== NHẬN DỮ LIỆU ================== */
$fullname  = trim($_POST['fullname'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$email     = trim($_POST['email'] ?? '');
$address   = trim($_POST['address'] ?? '');
$note      = trim($_POST['note'] ?? '');
$payment = strtoupper($_POST['payment_method'] ?? 'COD');


$total_all    = (int)($_POST['total_all'] ?? 0);
$shipping_fee = (int)($_POST['shipping_fee'] ?? 0);
$grand_total  = $total_all + $shipping_fee;
$weight       = (int)($_POST['weight'] ?? 500);

$to_district_id = (int)($_POST['to_district_id'] ?? 0);
$to_ward_code   = $_POST['to_ward_code'] ?? '';

$product_ids = $_POST['product_id'] ?? [];
$quantities  = $_POST['qty'] ?? [];

/* ================== VALIDATE ================== */
if ($fullname === '' || $phone === '' || $address === '' || empty($product_ids)) {
    die("<script>alert('Thiếu thông tin đặt hàng!');history.back();</script>");
}

/* ================== TẠO ĐƠN ================== */
$order_code = 'DH' . date('YmdHis') . rand(100, 999);
$cod_amount = ($payment === 'COD') ? $grand_total : 0;
$status     = 'pending'; // ✅ KHAI BÁO BIẾN TRƯỚC (FIX LỖI)
$payment_status = 'unpaid';

if ($payment === 'QR') {
    $status = 'pending';          // chờ thanh toán
    $payment_status = 'unpaid';
}

/* ================== LƯU ORDERS ================== */
$stmt = $conn->prepare("
    INSERT INTO orders (
        user_id, order_code, customer_name, customer_phone, customer_email,
        customer_address, payment_method, total_amount, shipping_fee,
        grand_total, cod_amount, order_status, payment_status, note
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

$stmt->bind_param(
    "issssssdiidsss",
    $user_id,          // i
    $order_code,       // s
    $fullname,         // s
    $phone,            // s
    $email,            // s
    $address,          // s
    $payment,          // s
    $total_all,        // d (decimal)
    $shipping_fee,     // i
    $grand_total,      // i
    $cod_amount,       // d
    $status,           // s
    $payment_status,   // s
    $note              // s
);


$stmt->execute();
$order_id = $stmt->insert_id;
$stmt->close();

/* ================== LƯU ORDER_ITEMS ================== */
$stmt2 = $conn->prepare("
    INSERT INTO order_items (order_id, productID, quantity, price)
    VALUES (?,?,?,?)
");

foreach ($product_ids as $i => $pid) {
    $qty = (int)($quantities[$i] ?? 1);
    $p = get_product($pid);
    if (!$p) continue;

    $price = $p['listPrice'] * (1 - $p['discountPercent'] / 100);
    $stmt2->bind_param("iiid", $order_id, $pid, $qty, $price);
    $stmt2->execute();
}
$stmt2->close();

/* ================== GHN ================== */
$items = [];
foreach ($product_ids as $i => $pid) {
    $p = get_product($pid);
    if (!$p) continue;

    $qty = (int)($quantities[$i] ?? 1);
    $price = $p['listPrice'] * (1 - $p['discountPercent'] / 100);

    $items[] = [
        "name"     => $p['productName'],
        "code"     => (string)$pid,
        "quantity" => $qty,
        "price"    => (int)$price
    ];
}

$payload = [
    "payment_type_id" => $cod_amount > 0 ? 2 : 1,
    "note"            => $note ?: "Shop KO - Cảm ơn quý khách",
    "required_note"   => "CHOXEMHANGKHONGTHU",
    "to_name"         => $fullname,
    "to_phone"        => $phone,
    "to_address"      => $address,
    "to_ward_code"    => $to_ward_code,
    "to_district_id"  => $to_district_id,
    "cod_amount"      => $cod_amount,
    "weight"          => $weight,
    "length"          => 30,
    "width"           => 20,
    "height"          => 15,
    "insurance_value" => $total_all,
    "service_type_id" => 2,
    "items"           => $items
];

$ch = curl_init('https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/create');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Token: ' . GHN_TOKEN,
        'ShopId: ' . GHN_SHOP_ID
    ]
]);

$response = curl_exec($ch);
curl_close($ch);
$result = json_decode($response, true);

/* ================== UPDATE GHN ================== */
if (isset($result['code']) && $result['code'] === 200) {
    $tracking_code = $result['data']['order_code'];

    $stmt3 = $conn->prepare("
        UPDATE orders
        SET shipping_order_code = ?, order_status = 'processing'
        WHERE order_id = ?
    ");
    $stmt3->bind_param("si", $tracking_code, $order_id);
    $stmt3->execute();
    $stmt3->close();
}

/* ================== REDIRECT ================== */
$_SESSION['last_order_code'] = $order_code;
$_SESSION['tracking_code']  = $tracking_code ?? '';

if ($payment === 'QR') {
    header("Location: qr_payment.php?order_id=$order_id");
    exit;
} else {
    header("Location: order_success.php?order_id=$order_id");
    exit;
}