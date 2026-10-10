
<?php

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}

function validasiProduk($name, $category, $price, $stock)
{
    $errors = [];

    $name = trim($name);
    $category = trim($category);

    if (strlen($name) < 3) {
        $errors[] = "Nama produk minimal 3 karakter.";
    }

    if ($category === "") {
        $errors[] = "Kategori produk wajib diisi.";
    }

    if ($price === "" || !is_numeric($price) || (float) $price <= 0) {
        $errors[] = "Harga harus berupa angka lebih dari 0.";
    }

    if (
        $stock === "" ||
        filter_var($stock, FILTER_VALIDATE_INT) === false ||
        (int) $stock < 0
    ) {
        $errors[] = "Stok harus berupa bilangan bulat minimal 0.";
    }

    return $errors;
}

function buatCsrfToken()
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}

function validasiCsrfToken($token)
{
    return isset($_SESSION["csrf_token"])
        && is_string($token)
        && hash_equals($_SESSION["csrf_token"], $token);
}
