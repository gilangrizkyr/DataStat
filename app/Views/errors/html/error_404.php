<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>404 - Halaman Tidak Ditemukan</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">

<style>
:root {
    --bg: #020617;
    --panel: #020617;
    --border: #1e293b;
    --text: #e5e7eb;
    --muted: #94a3b8;
    --primary: #38bdf8;
    --secondary: #818cf8;
    --danger: #fb7185;
    --glow: rgba(56,189,248,.35);
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: Inter, system-ui, sans-serif;
}

body {
    min-height: 100vh;
    background:
        radial-gradient(circle at 20% 10%, #020617, transparent 45%),
        radial-gradient(circle at 80% 80%, #020617, transparent 45%),
        var(--bg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text);
    overflow: hidden;
}

/* FLOATING GLOW */
body::before {
    content: "";
    position: absolute;
    width: 420px;
    height: 420px;
    background: radial-gradient(circle, var(--glow), transparent 70%);
    animation: float 8s ease-in-out infinite;
    z-index: 0;
}

@keyframes float {
    0% { transform: translate(-120px, -80px) }
    50% { transform: translate(120px, 80px) }
    100% { transform: translate(-120px, -80px) }
}

/* CARD */
.container {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 2.5rem;
    max-width: 540px;
    animation: enter .8s ease;
}

@keyframes enter {
    from { opacity: 0; transform: translateY(20px) scale(.96) }
    to { opacity: 1; transform: translateY(0) scale(1) }
}

h1 {
    font-size: clamp(5rem, 18vw, 8rem);
    font-weight: 900;
    line-height: 1;
    background: linear-gradient(
        120deg,
        var(--primary),
        var(--secondary),
        var(--primary)
    );
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: shimmer 3s linear infinite;
}

@keyframes shimmer {
    to { background-position: 200% center }
}

h2 {
    margin-top: 1rem;
    font-size: 1.6rem;
    font-weight: 600;
}

p {
    margin-top: .8rem;
    color: var(--muted);
    line-height: 1.6;
    font-size: .95rem;
}

.actions {
    margin-top: 2.2rem;
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: .7rem 1.6rem;
    border-radius: 999px;
    font-size: .9rem;
    font-weight: 600;
    text-decoration: none;
    transition: all .25s ease;
    position: relative;
    overflow: hidden;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: #020617;
    box-shadow: 0 0 25px rgba(56,189,248,.25);
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 40px rgba(56,189,248,.45);
}

.btn-outline {
    border: 1px solid var(--border);
    color: var(--text);
}

.btn-outline:hover {
    background: #020617;
    transform: translateY(-3px);
}

.footer {
    margin-top: 2.5rem;
    font-size: .75rem;
    color: #64748b;
}
</style>
</head>

<body>

<div class="container">
    <h1>404</h1>

    <h2>Halaman Tidak Ditemukan</h2>

    <p>
        <?php if (ENVIRONMENT !== 'production') : ?>
            Route atau URL yang kamu akses tidak tersedia atau belum terdaftar di aplikasi ini.
        <?php else : ?>
            <?= lang('Errors.sorryCannotFind') ?>
        <?php endif; ?>
    </p>

    <div class="actions">
        <a href="<?= base_url('/') ?>" class="btn btn-primary">🏠 Beranda</a>
        <a href="javascript:history.back()" class="btn btn-outline">⬅️ Kembali</a>
    </div>

    <div class="footer">
        CodeIgniter <?= \CodeIgniter\CodeIgniter::CI_VERSION ?> · PHP <?= PHP_VERSION ?>
    </div>
</div>

</body>
</html>
