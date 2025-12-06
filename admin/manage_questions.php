<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
if (!is_admin_logged_in()) redirect('login.php');

if ($_POST && isset($_POST['question_text'])) {
    $text = trim($_POST['question_text']);
    $type = $_POST['question_type'];
    $options = null;
    if ($type === 'dropdown' && !empty($_POST['options'])) {
        $opts = array_filter(array_map('trim', explode("\n", $_POST['options'])));
        $options = json_encode($opts, JSON_UNESCAPED_UNICODE);
    }
    if ($text) {
        $pdo->prepare("INSERT INTO questions (question_text, question_type, options) VALUES (?,?,?)")
            ->execute([$text, $type, $options]);
        redirect('manage_questions.php');
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM questions WHERE id = ?")->execute([$_GET['delete']]);
    redirect('manage_questions.php');
}

$questions = $pdo->query("SELECT * FROM questions ORDER BY id ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Kelola Pertanyaan</title>
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
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="manage_questions.php" class="nav-item active">
                    <i class="fas fa-poll"></i>
                    <span>Kelola Pertanyaan</span>
                </a>
                <a href="export_csv.php" class="nav-item">
                    <i class="fas fa-file-export"></i>
                    <span>Ekspor Data</span>
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
                <h1>Kelola Pertanyaan</h1>
                <a href="dashboard.php" class="btn btn-outline">&larr; Kembali</a>
            </header>

            <div class="card">
                <h3>Tambah Pertanyaan Baru</h3>
                <form method="POST">
                    <div class="input-group">
                        <textarea name="question_text" placeholder="Tulis pertanyaan..." required></textarea>
                    </div>
                    <div class="input-group">
                        <select name="question_type" id="qType" onchange="toggleOptions()" required>
                            <option value="text">Teks</option>
                            <option value="number">Angka</option>
                            <option value="slider">Slider (1-5)</option>
                            <option value="dropdown">Dropdown</option>
                        </select>
                    </div>
                    <div id="optField" class="input-group" style="display:none;">
                        <textarea name="options" placeholder="Satu opsi per baris"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </form>
            </div>

            <div class="card mt-20">
                <h3>Daftar Pertanyaan (<?= count($questions) ?>)</h3>
                <?php if (empty($questions)): ?>
                    <p class="text-muted">Belum ada pertanyaan.</p>
                <?php else: ?>
                    <div class="question-list">
                        <?php foreach ($questions as $q): ?>
                            <div class="question-item">
                                <div>
                                    <strong><?= htmlspecialchars($q['question_text']) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= ucfirst($q['question_type']) ?></small>
                                </div>
                                <a href="?delete=<?= $q['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
    function toggleOptions() {
        const type = document.getElementById('qType').value;
        const field = document.getElementById('optField');
        field.style.display = (type === 'dropdown') ? 'block' : 'none';
    }
    </script>
</body>
</html>