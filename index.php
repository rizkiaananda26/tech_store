
<?php
require_once "config.php";
require_once "functions.php";

$errors = [];
$name = "";
$category = "";
$price = "";
$stock = "";

$categories = [
    "Laptop",
    "Aksesoris",
    "Audio",
    "Komponen PC",
    "Lainnya"
];

// Proses tambah produk
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $stock = trim($_POST["stock"] ?? "");
    $token = $_POST["csrf_token"] ?? "";

    if (!validasiCsrfToken($token)) {
        $errors[] = "Permintaan tidak valid. Silakan muat ulang halaman.";
    } else {
        $errors = validasiProduk($name, $category, $price, $stock);
    }

    // Pastikan nama produk belum digunakan
    if (empty($errors)) {
        $check = $pdo->prepare(
            "SELECT id FROM products WHERE name = :name LIMIT 1"
        );
        $check->execute(["name" => $name]);

        if ($check->fetch()) {
            $errors[] = "Nama produk sudah digunakan. Gunakan nama lain.";
        }
    }

    // Simpan produk jika semua validasi berhasil
    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO products (name, category, price, stock)
             VALUES (:name, :category, :price, :stock)"
        );

        $stmt->execute([
            "name" => $name,
            "category" => $category,
            "price" => $price,
            "stock" => $stock
        ]);

        // PRG: cegah pengiriman ulang saat halaman di-refresh
        header("Location: index.php?added=1");
        exit;
    }
}

// Pencarian dan filter kategori
$search = trim($_GET["search"] ?? "");
$filterCategory = trim($_GET["category"] ?? "");

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($search !== "") {
    $sql .= " AND (name LIKE :search_name OR category LIKE :search_category)";
    $params["search_name"] = "%" . $search . "%";
    $params["search_category"] = "%" . $search . "%";
}

if ($filterCategory !== "") {
    $sql .= " AND category = :category";
    $params["category"] = $filterCategory;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$csrfToken = buatCsrfToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechStore | Product Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <div class="container header-content">
        <a href="index.php" class="brand">Tech<span>Store</span></a>
        <p>Product Management System</p>
    </div>
</header>

<main class="container">
    <section class="hero">
        <div>
            <p class="eyebrow">TECHSTORE INVENTORY</p>
            <h1>Kelola Produk Teknologi</h1>
            <p class="hero-text">
                Atur data produk, harga, kategori, dan stok dalam satu tempat.
            </p>
        </div>
        <div class="hero-icon" aria-hidden="true">⌘</div>
    </section>

    <?php if (isset($_GET["added"]) && $_GET["added"] === "1"): ?>
        <div class="alert success">
            Produk berhasil ditambahkan.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET["updated"]) && $_GET["updated"] === "1"): ?>
        <div class="alert success">
            Produk berhasil diperbarui.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET["deleted"]) && $_GET["deleted"] === "1"): ?>
        <div class="alert success">
            Produk berhasil dihapus.
        </div>
    <?php endif; ?>

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

    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>Tambah Produk</h2>
                <p>Masukkan informasi produk baru.</p>
            </div>
        </div>

        <form action="index.php" method="POST" class="product-form">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($csrfToken) ?>"
            >

            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Nama Produk</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        minlength="3"
                        maxlength="100"
                        placeholder="Contoh: Laptop ASUS"
                        value="<?= e($name) ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="category">Kategori</label>
                    <select id="category" name="category" required>
                        <option value="">Pilih kategori</option>
                        <?php foreach ($categories as $item): ?>
                            <option
                                value="<?= e($item) ?>"
                                <?= $category === $item ? "selected" : "" ?>
                            >
                                <?= e($item) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="price">Harga (Rp)</label>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        min="0.01"
                        step="0.01"
                        placeholder="Contoh: 7500000"
                        value="<?= e($price) ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="stock">Stok</label>
                    <input
                        type="number"
                        id="stock"
                        name="stock"
                        min="0"
                        step="1"
                        placeholder="Contoh: 10"
                        value="<?= e($stock) ?>"
                        required
                    >
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                + Tambah Produk
            </button>
        </form>
    </section>

    <section class="inventory-section">
        <div class="section-heading inventory-heading">
            <div>
                <h2>Daftar Produk</h2>
                <p>
                    Ditemukan <?= count($products) ?> produk.
                </p>
            </div>
        </div>

        <form action="index.php" method="GET" class="search-form">
            <div class="form-group search-input">
                <label for="search">Cari produk</label>
                <input
                    type="search"
                    id="search"
                    name="search"
                    placeholder="Cari nama atau kategori..."
                    value="<?= e($search) ?>"
                >
            </div>

            <div class="form-group filter-input">
                <label for="filter-category">Filter kategori</label>
                <select id="filter-category" name="category">
                    <option value="">Semua kategori</option>
                    <?php foreach ($categories as $item): ?>
                        <option
                            value="<?= e($item) ?>"
                            <?= $filterCategory === $item ? "selected" : "" ?>
                        >
                            <?= e($item) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-secondary">
                Cari
            </button>

            <a href="index.php" class="btn btn-light">Reset</a>
        </form>

        <?php if (empty($products)): ?>
            <div class="empty-state">
                <div class="empty-icon" aria-hidden="true">▤</div>
                <h3>Belum ada produk</h3>
                <p>
                    Tambahkan produk melalui formulir di atas untuk mulai
                    mengelola inventaris TechStore.
                </p>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <article class="product-card">
                        <div class="product-card-top">
                            <span class="category-badge">
                                <?= e($product["category"]) ?>
                            </span>
                            <span class="product-id">
                                ID #<?= (int) $product["id"] ?>
                            </span>
                        </div>

                        <div class="product-symbol" aria-hidden="true">
                            <?= e(strtoupper(substr($product["name"], 0, 1))) ?>
                        </div>

                        <h3><?= e($product["name"]) ?></h3>

                        <div class="product-price">
                            Rp <?= number_format((float) $product["price"], 0, ",", ".") ?>
                        </div>

                        <div class="stock-row">
                            <span>Stok tersedia</span>
                            <strong class="<?= (int) $product["stock"] < 3 ? "stock-low" : "stock-normal" ?>">
                                <?= (int) $product["stock"] ?> unit
                            </strong>
                        </div>

                        <?php if ((int) $product["stock"] < 3): ?>
                            <p class="stock-warning">Stok menipis</p>
                        <?php endif; ?>

                        <div class="card-actions">
                            <a
                                href="edit.php?id=<?= (int) $product["id"] ?>"
                                class="btn btn-secondary"
                            >
                                Edit
                            </a>

                            <form action="delete.php" method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $product["id"] ?>"
                                >
                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= e($csrfToken) ?>"
                                >
                                <button type="submit" class="btn btn-danger">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <footer class="site-footer">
        <p>TechStore Product Manager &copy; <?= date("Y") ?></p>
    </footer>
</main>

</body>
</html>
