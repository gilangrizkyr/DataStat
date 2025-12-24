<!-- Menampikan data users untuk crud -->

<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="contaiter">
    <h1>Halaman Users Superadmin</h1>
    <p>Ini adalah halaman untuk mengelola data pengguna (users) oleh superadmin.</p>
</div>

<!-- buatkan datanya tabel users dengan kolom id, nama, email, role, status, aksi (edit, hapus, lihat) -->



<?= $this->endSection() ?>