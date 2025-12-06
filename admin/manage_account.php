<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
if (!is_admin_logged_in()) redirect('login.php');

$message = '';
if ($_POST) {
    $new_username = trim($_POST['username']);
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($new_username)) {
        $message = "Username tidak boleh kosong.";
    } else {
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $message = "Password konfirmasi tidak cocok.";
            } else {
                $hashed = password_hash($new_password, PASSWORD_BCRYPT);
                $pdo->prepare("UPDATE admin SET username = ?, password = ? WHERE id = 1")
                    ->execute([$new_username, $hashed]);
                $_SESSION['admin_username'] = $new_username;
                $message = "✅ Akun berhasil diperbarui.";
            }
        } else {
            $pdo->prepare("UPDATE admin SET username = ? WHERE id = 1")
                ->execute([$new_username]);
            $_SESSION['admin_username'] = $new_username;
            $message = "✅ Username berhasil diperbarui.";
        }
    }
}

$stmt = $pdo->query("SELECT username FROM admin WHERE id = 1");
$admin = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Kelola Akun</title>
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
                <a href="manage_questions.php" class="nav-item">
                    <i class="fas fa-poll"></i>
                    <span>Kelola Pertanyaan</span>
                </a>
                <a href="export_csv.php" class="nav-item">
                    <i class="fas fa-file-export"></i>
                    <span>Ekspor Data</span>
                </a>
                <a href="manage_account.php" class="nav-item active">
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
                <h1>Kelola Akun</h1>
                <a href="dashboard.php" class="btn btn-outline">&larr; Kembali</a>
            </header>

            <div class="card">
                <?php if ($message): ?>
                    <div class="alert <?= strpos($message, '✅') !== false ? 'alert-success' : 'alert-error' ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="input-group">
                        <label>Username Baru</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required>
                    </div>
                    <div class="input-group">
                        <label>Password Baru (kosongkan jika tidak ingin ganti)</label>
                        <input type="password" name="password" autocomplete="new-password">
                    </div>
                    <div class="input-group">
                        <label>Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>