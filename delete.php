
<?php
require_once "config.php";
require_once "functions.php";

// Penghapusan hanya boleh melalui metode POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

// Periksa token keamanan
$token = $_POST["csrf_token"] ?? "";

if (!validasiCsrfToken($token)) {
    http_response_code(403);
    exit("Permintaan tidak valid. Silakan kembali ke halaman utama.");
}

// Periksa ID produk
$id = filter_var(
    $_POST["id"] ?? null,
    FILTER_VALIDATE_INT
);

if ($id === false || $id === null || $id < 1) {
    header("Location: index.php");
    exit;
}

// Pastikan produk tersedia
$check = $pdo->prepare(
    "SELECT id FROM products WHERE id = :id"
);
$check->execute(["id" => $id]);

if (!$check->fetch()) {
    header("Location: index.php");
    exit;
}

// Hapus produk dengan prepared statement
$delete = $pdo->prepare(
    "DELETE FROM products WHERE id = :id"
);
$delete->execute(["id" => $id]);

header("Location: index.php?deleted=1");
exit;
