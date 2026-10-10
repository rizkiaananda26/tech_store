# TechStore — Product Manager

# Deskripsi
TechStore adalah aplikasi web sederhana untuk mengelola data produk teknologi menggunakan PHP dan MySQL. Aplikasi menyediakan fitur tambah, lihat, edit, hapus, pencarian, dan filter produk.

# Teknologi
- PHP
- MySQL
- HTML
- CSS
- PDO untuk koneksi database

# Fitur
- Menambahkan produk dengan validasi input.
- Menampilkan produk dalam bentuk kartu responsif.
- Mengubah dan menghapus produk berdasarkan ID.
- Memastikan nama produk unik.
- Mencari produk berdasarkan nama atau kategori.
- Memfilter produk berdasarkan kategori.
- Menggunakan prepared statements, output escaping, dan CSRF token.
- Menggunakan pola Post/Redirect/Get (PRG) setelah penambahan produk.

# Persyaratan
- XAMPP atau lingkungan server PHP dan MySQL.
- PHP dengan ekstensi PDO MySQL aktif.

# Cara Menjalankan
1. Salin folder `tech_store` ke direktori `htdocs` XAMPP.
2. Jalankan Apache dan MySQL.
3. Buka phpMyAdmin.
4. Impor file `database/store_db.sql`, atau jalankan file tersebut melalui tab SQL.
5. Periksa konfigurasi koneksi database di `config.php`.
6. Buka `http://localhost/tech_store/` melalui browser.

# Cara Menguji
1. Tambahkan produk dengan data valid.
2. Coba nama produk kurang dari 3 karakter.
3. Coba harga nol atau negatif.
4. Coba stok negatif atau bukan bilangan bulat.
5. Coba tambahkan nama produk yang sama.
6. Refresh halaman setelah berhasil menambahkan produk dan pastikan tidak terjadi penambahan ulang.
7. Masukkan nama produk `<b>Promo</b>` dan pastikan tampil sebagai teks, bukan HTML yang dijalankan.
8. Uji fungsi pencarian, filter, edit, dan hapus.
9. Uji tampilan pada layar yang sempit.

# Struktur Proyek
- `config.php`: koneksi database dan sesi.
- `functions.php`: validasi input, output escaping, dan fungsi CSRF.
- `index.php`: daftar, pencarian, filter, dan penambahan produk.
- `edit.php`: mengubah data produk.
- `delete.php`: menghapus produk.
- `style.css`: desain dan tata letak responsif.
- `database/store_db.sql`: struktur database.

