<?php
use CodeIgniter\CodeIgniter;
use Config\Services;

/**
 * Penjelasan error sederhana (Bahasa Indonesia)
 */
function jelaskanError(\Throwable $e): string
{
    $msg = strtolower($e->getMessage());

    return match (true) {
        str_contains($msg, 'undefined variable') =>
            'Variabel digunakan sebelum didefinisikan. Pastikan variabel sudah dibuat atau dikirim ke view.',

        str_contains($msg, 'call to undefined method') =>
            'Method yang dipanggil tidak ada. Biasanya karena typo atau salah class.',

        str_contains($msg, 'undefined array key'),
        str_contains($msg, 'undefined index') =>
            'Key array tidak ditemukan. Pastikan index tersebut tersedia sebelum diakses.',

        str_contains($msg, 'syntax error') =>
            'Kesalahan penulisan kode (syntax). Cek tanda ; { } atau struktur PHP.',

        str_contains($msg, 'class') && str_contains($msg, 'not found') =>
            'Class tidak ditemukan. Periksa namespace, autoload, dan nama file.',

        default =>
            'Aplikasi gagal dijalankan karena terjadi error internal. Lihat detail teknis di bawah untuk menemukan penyebabnya.',
    };
}

$errorId = uniqid('ERR-', true);
$penjelasan = jelaskanError($exception);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?= esc($title) ?></title>

<style>
:root {
    --bg: #020617;
    --panel: #020617;
    --border: #1e293b;
    --text: #e5e7eb;
    --muted: #94a3b8;
    --danger: #fb7185;
    --info: #38bdf8;
    --warn: #facc15;
    --glow: rgba(56,189,248,.25);
}

* { box-sizing: border-box }

body {
    margin: 0;
    background:
        radial-gradient(circle at 20% 10%, #020617, transparent 40%),
        radial-gradient(circle at 80% 80%, #020617, transparent 40%),
        #020617;
    color: var(--text);
    font-family: Inter, system-ui, sans-serif;
    animation: fadeIn 1s ease forwards;
}

@keyframes fadeIn {
    from { opacity: 0 }
    to { opacity: 1 }
}

header {
    padding: 24px;
    border-bottom: 1px solid var(--border);
    animation: slideDown .8s ease;
}

@keyframes slideDown {
    from { transform: translateY(-20px); opacity: 0 }
    to { transform: translateY(0); opacity: 1 }
}

header h1 {
    margin: 0;
    font-size: clamp(22px, 4vw, 30px);
    color: var(--danger);
    text-shadow: 0 0 15px rgba(251,113,133,.3);
}

.env {
    font-size: 12px;
    color: var(--muted);
    margin-bottom: 6px;
}

.container {
    max-width: 1200px;
    margin: auto;
    padding: 24px;
}

.grid {
    display: grid;
    gap: 20px;
}

@media (min-width: 900px) {
    .grid {
        grid-template-columns: 1fr 1fr;
    }
}

.card {
    background: linear-gradient(180deg, #020617, #020617);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px;
    position: relative;
    overflow: hidden;
    animation: pop .6s ease;
}

@keyframes pop {
    from { transform: scale(.96); opacity: 0 }
    to { transform: scale(1); opacity: 1 }
}

.card::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(
        120deg,
        transparent,
        var(--glow),
        transparent
    );
    opacity: 0;
    transition: opacity .3s;
}

.card:hover::after {
    opacity: 1;
}

.card h2 {
    margin: 0 0 12px;
    font-size: 16px;
    color: var(--info);
}

.message {
    font-family: ui-monospace, monospace;
    color: #fecaca;
    background: rgba(251,113,133,.08);
    border: 1px solid rgba(251,113,133,.2);
    padding: 14px;
    border-radius: 10px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%,100% { box-shadow: 0 0 0 transparent }
    50% { box-shadow: 0 0 25px rgba(251,113,133,.15) }
}

.explain {
    background: rgba(250,204,21,.08);
    border-left: 4px solid var(--warn);
    padding: 14px;
    border-radius: 10px;
    color: #fde68a;
}

.location {
    font-size: 13px;
    color: var(--warn);
    margin-bottom: 10px;
}

pre {
    background: #020617;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 14px;
    overflow-x: auto;
    font-size: 13px;
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    font-size: 11px;
    border-radius: 999px;
    border: 1px solid var(--border);
    color: var(--muted);
    margin-top: 8px;
}

details summary {
    cursor: pointer;
    color: var(--muted);
    font-size: 13px;
}

details[open] summary {
    color: var(--text);
}
</style>
</head>

<body>

<header>
    <div class="env">
        <?= date('H:i:s') ?> • PHP <?= PHP_VERSION ?>
        • CI <?= CodeIgniter::CI_VERSION ?>
        • <?= ENVIRONMENT ?>
    </div>
    <h1><?= esc($title) ?></h1>
</header>

<div class="container grid">

    <!-- ERROR -->
    <section class="card">
        <h2>❌ Error</h2>
        <div class="message">
            <?= esc($exception->getMessage()) ?>
        </div>
        <span class="badge"><?= esc($errorId) ?></span>
    </section>

    <!-- PENJELASAN -->
    <section class="card">
        <h2>💡 Penjelasan</h2>
        <div class="explain">
            <?= esc($penjelasan) ?>
        </div>
    </section>

</div>

<div class="container">

    <!-- LOKASI -->
    <section class="card">
        <h2>📍 Lokasi Error</h2>
        <div class="location">
            <?= esc(clean_path($file)) ?> : baris <?= esc($line) ?>
        </div>

        <?php if (is_file($file)): ?>
            <pre><?= static::highlightFile($file, $line, 10); ?></pre>
        <?php endif; ?>
    </section>

    <!-- TRACE -->
    <section class="card">
        <h2>🧵 Stack Trace</h2>
        <ol>
        <?php foreach ($trace as $i => $row): ?>
            <li style="margin-bottom:8px;font-size:13px">
                <strong>#<?= $i ?></strong>
                <?= isset($row['file'])
                    ? esc(clean_path($row['file']).':'.$row['line'])
                    : '{internal}' ?>
                <br>
                <span style="color:var(--muted)">
                    <?= esc(($row['class'] ?? '').($row['type'] ?? '').($row['function'] ?? '')) ?>
                </span>
            </li>
        <?php endforeach ?>
        </ol>
    </section>

    <!-- DETAIL -->
    <section class="card">
        <h2>⚙ Detail Teknis</h2>

        <details>
            <summary>Request</summary>
            <?php $req = Services::request(); ?>
            <pre><?= esc(print_r([
                'url' => (string)$req->getUri(),
                'method' => $req->getMethod(),
                'ip' => $req->getIPAddress(),
                'ajax' => $req->isAJAX(),
            ], true)) ?></pre>
        </details>

        <details>
            <summary>Memory</summary>
            <pre><?= esc(print_r([
                'usage' => static::describeMemory(memory_get_usage(true)),
                'peak' => static::describeMemory(memory_get_peak_usage(true)),
                'limit' => ini_get('memory_limit'),
            ], true)) ?></pre>
        </details>
    </section>

</div>

</body>
</html>
