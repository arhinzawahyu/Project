<?php
require_once 'config/db.php';
$stmt = $pdo->query("SELECT * FROM questions ORDER BY id ASC");
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
$has_questions = !empty($questions);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Kuesioner TikTok</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

<div class="wrapper">
    <header class="header">
        <div class="logo">
            <i class="fas fa-chart-pie"></i>
            <h1>Kuesioner TikTok</h1>
        </div>
        <a href="admin/login.php" class="btn btn-outline">
            <i class="fas fa-lock"></i> Admin
        </a>
    </header>

    <main class="main-content">
        <?php if (!$has_questions): ?>
            <div class="card">
                <div style="text-align:center; padding:2rem;">
                    <i class="fas fa-exclamation-triangle" style="font-size:3rem; color:#f59e0b; margin-bottom:1.5rem;"></i>
                    <h2>FORM BELUM SIAP</h2>
                    <p>Admin belum menambahkan pertanyaan.</p>
                </div>
            </div>
        <?php else: ?>
            <form id="questionnaireForm">
                <?php foreach ($questions as $index => $q): 
                    $q_id = $q['id'];
                    $type = $q['question_type'];
                    $options = !empty($q['options']) ? json_decode($q['options'], true) : [];
                ?>
                    <div class="card">
                        <div style="display:flex; gap:12px; margin-bottom:16px; align-items:flex-start;">
                            <span style="display:flex; align-items:center; justify-content:center; width:28px; height:28px; background:#6366f1; color:white; border-radius:50%; font-weight:bold; flex-shrink:0;"> <?= $index + 1 ?> </span>
                            <h3 style="margin:0; font-size:1.25rem;"><?= htmlspecialchars($q['question_text']) ?></h3>
                        </div>

                        <?php if ($type === 'slider'): ?>
                            <div style="display:flex; flex-direction:column; gap:12px;">
                                <input type="range" name="q<?= $q_id ?>" min="1" max="5" value="3" class="slider" oninput="this.nextElementSibling.textContent=this.value" style="-webkit-appearance:none; height:8px; background:#e2e8f0; border-radius:4px;">
                                <div style="display:flex; justify-content:space-between; font-size:0.85rem; color:#64748b;">
                                    <span>1<br><small>Tidak Pernah</small></span>
                                    <span>5<br><small>Sangat Sering</small></span>
                                </div>
                                <output style="text-align:center; font-weight:bold; color:#6366f1; font-size:1.3rem;">3</output>
                            </div>

                        <?php elseif ($type === 'dropdown'): ?>
                            <select name="q<?= $q_id ?>" style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:12px; font-size:1rem;" required>
                                <option value="">— Pilih —</option>
                                <?php foreach ($options as $opt): ?>
                                    <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                                <?php endforeach; ?>
                            </select>

                        <?php elseif ($type === 'number'): ?>
                            <input type="number" name="q<?= $q_id ?>" min="0" placeholder="Masukkan angka..." style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:12px; font-size:1rem;" required>

                        <?php else: ?>
                            <input type="text" name="q<?= $q_id ?>" placeholder="Ketik jawaban Anda..." style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:12px; font-size:1rem;" required>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-paper-plane"></i> Kirim Respons
                </button>
            </form>
        <?php endif; ?>
    </main>

    <footer style="text-align:center; padding:1.5rem; color:#64748b; font-size:0.9rem; margin-top:auto;">
        © 2025 Kuesioner TikTok • Tugas Statistika
    </footer>
</div>

<script>
document.getElementById('questionnaireForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const answers = {};
    document.querySelectorAll('[name^="q"]').forEach(el => {
        const id = el.name.replace('q', '');
        answers[id] = el.value;
    });

    const res = await fetch('submit.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({answers: answers})
    });
    const data = await res.json();
    alert(data.message);
    if (data.success) {
        document.getElementById('questionnaireForm').reset();
        document.querySelectorAll('output').forEach(el => el.textContent = '3');
    }
});
</script>
</body>
</html>