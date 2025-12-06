<?php
require_once '../includes/functions.php';
if (!is_admin_logged_in()) redirect('login.php');
require_once '../config/db.php';

$total_responses = $pdo->query("SELECT COUNT(*) FROM responses")->fetchColumn();
$total_questions = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();

$chart_data = [];
$questions_for_chart = $pdo->query("SELECT id, question_text FROM questions WHERE question_type IN ('slider','number')")->fetchAll();
foreach ($questions_for_chart as $q) {
    $avg = $pdo->prepare("SELECT AVG(CAST(answer_value AS DECIMAL(10,2))) FROM answers WHERE question_id = ?");
    $avg->execute([$q['id']]);
    $avg_val = $avg->fetchColumn();
    $chart_data[] = [
        'label' => substr($q['question_text'], 0, 30) . (strlen($q['question_text']) > 30 ? '...' : ''),
        'value' => round($avg_val ?? 0, 2)
    ];
}

$recent = $pdo->query("SELECT ip_address, submitted_at FROM responses ORDER BY submitted_at DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Dashboard Statistik</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body class="dashboard-body">
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-chart-line"></i>
                <span class="logo-text">Stat<span class="logo-accent">Kues</span></span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="manage_questions.php" class="nav-item">
                    <i class="fas fa-poll"></i>
                    <span>Kelola Pertanyaan</span>
                </a>
                <a href="export_csv.php" class="nav-item">
                    <i class="fas fa-file-export"></i>
                    <span>Ekspor Data (CSV)</span>
                </a>
                <a href="manage_account.php" class="nav-item">
                    <i class="fas fa-user-cog"></i>
                    <span>Akun Saya</span>
                </a>
                <a href="logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Keluar</span>
                </a>
            </nav>
        </aside>

        <main class="main-panel">
            <header class="topbar">
                <h1>Dashboard Statistik Kuesioner</h1>
                <div class="user-info">
                    <i class="far fa-user"></i>
                    <span><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></span>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon bg-indigo">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $total_responses ?></div>
                        <div class="stat-label">Total Responden</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-emerald">
                        <i class="fas fa-poll"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $total_questions ?></div>
                        <div class="stat-label">Pertanyaan Aktif</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-amber">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $total_responses > 0 ? '100' : '0' ?>%</div>
                        <div class="stat-label">Kelengkapan Data</div>
                    </div>
                </div>
            </div>

            <?php if (!empty($chart_data)): ?>
            <div class="card mt-20">
                <h2>Rata-rata Skor Pertanyaan Numerik</h2>
                <canvas id="statsChart" height="100"></canvas>
            </div>
            <?php endif; ?>

            <div class="card mt-20">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h2>5 Responden Terbaru</h2>
                    <a href="export_csv.php" class="btn btn-sm btn-outline">Ekspor Semua</a>
                </div>
                <?php if (empty($recent)): ?>
                    <p class="text-muted">Belum ada responden.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>IP Address</th>
                                    <th>Waktu Pengisian</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['ip_address']) ?></td>
                                    <td><?= date('d M Y H:i', strtotime($r['submitted_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../assets/js/chart.umd.js"></script>
    <script>
        <?php if (!empty($chart_data)): ?>
        const ctx = document.getElementById('statsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($chart_data, 'label')) ?>,
                datasets: [{
                    label: 'Rata-rata Skor',
                    data: <?= json_encode(array_column($chart_data, 'value')) ?>,
                    backgroundColor: '#6366f1',
                    borderColor: '#4f46e5',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 5 } }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>