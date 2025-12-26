<?= $this->extend('layouts/superadmin') ?>
<?= $this->section('content') ?>

<div class="page-title">
    <h1> <i class="bi bi-person-gear me-2"></i><?= esc($title) ?></h1>
    <p class="mb-0">Informasi Application</p>
</div>

<div class="row">
    <div class="col">
        <h2>Application Details</h2>
    </div>
</div>

<?= $this->endSection() ?>