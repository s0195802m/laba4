<?php
// success.php - Страница успешного сохранения
session_start();

$id = $_GET['id'] ?? '';

// Очищаем Cookies с ошибками
foreach (['full_name', 'phone', 'email', 'birth_date', 'gender', 'languages', 'biography', 'contract_accepted', 'general'] as $field) {
    setcookie("error_$field", "", time() - 3600, '/');
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Успешное сохранение — Лабораторная работа №4</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>📡 Программно-аппаратные средства Web</h1>
            <p class="subtitle">(с) Сергей Синица 2020</p>
            <h2>Задание 4. Данные успешно сохранены</h2>
        </div>
    </header>
    <main class="container">
        <div class="success-message" style="background: #e8f5e9; border-left: 5px solid #4caf50; padding: 1.5rem; border-radius: 20px;">
            <strong>✅ Ваши данные успешно сохранены в базу данных!</strong><br>
            <?php if ($id): ?>
                ID вашей записи: <strong><?php echo htmlspecialchars($id); ?></strong>
            <?php endif; ?>
        </div>
        <div class="action-buttons" style="margin-top: 1.5rem;">
            <a href="index.php" class="action-btn">📝 Заполнить новую анкету</a>
            <a href="list.php" class="action-btn">📋 Посмотреть все анкеты</a>
        </div>
    </main>
    <footer>
        <div class="container">
            <p>Лабораторная работа №4 — Cookies | Май 2026</p>
        </div>
    </footer>
</body>
</html>