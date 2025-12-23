<?php
session_start();
require_once 'config.php';

$order_id = (int)($_GET['order_id'] ?? 0);
if ($order_id <= 0) {
    die("Đơn hàng không hợp lệ");
}

/* ====== LẤY ĐƠN HÀNG ====== */
$stmt = $conn->prepare("
    SELECT order_code, grand_total, payment_status 
    FROM orders 
    WHERE order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Không tìm thấy đơn hàng");
}

/* ====== CHẶN THANH TOÁN LẠI ====== */
if ($order['payment_status'] === 'paid') {
    die("<h3 style='color:green;text-align:center'>Đơn hàng này đã được thanh toán ✅</h3>");
}

$amount     = (int)$order['grand_total'];
$order_code = $order['order_code'];

/* ====== THÔNG TIN NGÂN HÀNG (ANH SỬA CHỖ NÀY) ====== */
$bank_id      = "MB";              // MB, VCB, ACB, TPB...
$account_no   = "0366063966";       // SỐ TÀI KHOẢN
$account_name = "NUYEN DINH TIEN";   // TÊN CHỦ TK
$note         = "TT DON $order_code";

/* ====== LINK QR VIETQR ====== */
$qr_url = "https://api.vietqr.io/image/{$bank_id}-{$account_no}-compact.png"
        . "?amount={$amount}"
        . "&addInfo=" . urlencode($note)
        . "&accountName=" . urlencode($account_name);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thanh toán QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="card shadow-lg border-0">
                    <div class="card-body text-center p-4">

                        <h3 class="text-danger fw-bold mb-3">
                            QUÉT MÃ QR THANH TOÁN
                        </h3>

                        <p class="mb-1"><b>Mã đơn hàng:</b></p>
                        <p class="text-primary fw-bold fs-5">
                            <?= htmlspecialchars($order_code) ?>
                        </p>

                        <p class="mb-1"><b>Số tiền cần thanh toán:</b></p>
                        <p class="text-danger fw-bold fs-4">
                            <?= number_format($amount, 0, ',', '.') ?>₫
                        </p>

                        <img src="<?= $qr_url ?>" alt="QR Payment" class="img-fluid my-3" style="max-width:260px">

                        <div class="alert alert-warning small mt-3">
                            Vui lòng chuyển khoản <b>đúng số tiền</b> và
                            <b>đúng nội dung</b> để hệ thống xác nhận nhanh nhất.
                        </div>

                        <form method="post" action="qr_confirm.php">
                            <input type="hidden" name="order_id" value="<?= $order_id ?>">
                            <button class="btn btn-success btn-lg w-100 mt-2">
                                Tôi đã thanh toán
                            </button>
                        </form>

                        <a href="index.php" class="d-block mt-3 text-decoration-none">
                            ← Quay về trang chủ
                        </a>

                    </div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>