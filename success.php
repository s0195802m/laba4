<?php
// success.php - НЕТ session_start()
$id = $_GET['id'] ?? '';

// Очищаем Cookies ошибок (не удаляем данные формы — они остаются на год)
foreach (['full_name', 'phone', 'email', 'birth_date', 'gender', 'languages', 'biography', 'contract_accepted'] as $field) {
    setcookie("error_$field", "", time() - 3600, '/');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Успешно — Лаба 4</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header><div class="container"><h1>✅ Успех!</h1></div></header>
    <main class="container">
        <div class="success-message" style="background:#e8f5e9; padding:1.5rem; border-radius:20px;">
            <strong>Данные сохранены в БД!</strong><br>
            ID: <?php echo htmlspecialchars($id); ?>
        </div>
        <a href="index.php" class="action-btn">📝 Новая анкета</a>
    </main>
</body>
</html>
