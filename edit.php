
<?php
require_once "config.php";
require_once "functions.php";

$errors = [];

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

// Ambil produk berdasarkan ID
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(["id" => $id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php");
    exit;
}

$name = $product["name"];
$category = $product["category"];
$price = (string) $product["price"];
$stock = (string) $product["stock"];

$categories = [
    "Laptop",
    "Aksesoris",
    "Audio",
    "Komponen PC",
    "Lainnya"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $stock = trim($_POST["stock"] ?? "");
    $token = $_POST["csrf_token"] ?? "";

    if (!validasiCsrfToken($token)) {
        $errors[] = "Permintaan tidak valid. Silakan kembali dan coba lagi.";
    } else {
        $errors = validasiProduk($name, $category, $price, $stock);
    }

    // Pastikan nama unik, kecuali nama milik produk yang sedang diedit
    if (empty($errors)) {
        $check = $pdo->prepare(
            "SELECT id FROM products
             WHERE name = :name AND id != :id
             LIMIT 1"
        );

        $check->execute([
            "name" => $name,
            "id" => $id
        ]);

        if ($check->fetch()) {
            $errors[] = "Nama produk sudah digunakan. Gunakan nama lain.";
        }
    }

    if (empty($errors)) {
        $update = $pdo->prepare(
            "UPDATE products
             SET name = :name,
                 category = :category,
                 price = :price,
                 stock = :stock
             WHERE id = :id"
        );

        $update->execute([
            "name" => $name,
            "category" => $category,
            "price" => $price,
            "stock" => $stock,
            "id" => $id
        ]);

        header("Location: index.php?updated=1");
        exit;
    }
}

$csrfToken = buatCsrfToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk | TechStore</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <div class="container header-content">
        <a href="index.php" class="brand">Tech<span>Store</span></a>
        <p>Product Management System</p>
    </div>
</header>

<main class="container page-content">
    <section class="panel edit-panel">
        <a href="index.php" class="back-link">&larr; Kembali ke daftar produk</a>

        <div class="section-heading">
            <div>
                <p class="eyebrow">PRODUCT MANAGEMENT</p>
                <h1>Edit Produk #<?= (int) $product["id"] ?></h1>
                <p>Perbarui informasi produk di bawah ini.</p>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <strong>Periksa kembali data kamu:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="edit.php?id=<?= (int) $id ?>" method="POST"
              class="product-form">
            <input type="hidden" name="csrf_token"
                   value="<?= e($csrfToken) ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Nama Produk</label>
                    <input type="text" id="name" name="name"
                           minlength="3" maxlength="100"
                           value="<?= e($name) ?>" required>
                </div>

                <div class="form-group">
                    <label for="category">Kategori</label>
                    <select id="category" name="category" required>
                        <option value="">Pilih kategori</option>
                        <?php foreach ($categories as $item): ?>
                            <option value="<?= e($item) ?>"
                                <?= $category === $item ? "selected" : "" ?>>
                                <?= e($item) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="price">Harga (Rp)</label>
                    <input type="number" id="price" name="price"
                           min="0.01" step="0.01"
                           value="<?= e($price) ?>" required>
                </div>

                <div class="form-group">
                    <label for="stock">Stok</label>
                    <input type="number" id="stock" name="stock"
                           min="0" step="1"
                           value="<?= e($stock) ?>" required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    Simpan Perubahan
                </button>
                <a href="index.php" class="btn btn-light">Batal</a>
            </div>
        </form>
    </section>
</main>

</body>
</html>
