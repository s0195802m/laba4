<?php
// index.php - Форма с валидацией на бэкенде, подсветкой ошибок и Cookies


// Функция для получения значения из Cookies или GET (приоритет: GET > Cookie)
function getValue($fieldName, $default = '') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET[$fieldName]) && $_GET[$fieldName] !== '') {
        return htmlspecialchars(trim($_GET[$fieldName]));
    }
    if (isset($_COOKIE['form_' . $fieldName])) {
        return htmlspecialchars($_COOKIE['form_' . $fieldName]);
    }
    return $default;
}

// Функция для получения ошибки из Cookies
function getError($fieldName) {
    if (isset($_COOKIE['error_' . $fieldName])) {
        return $_COOKIE['error_' . $fieldName];
    }
    return '';
}

// Функция для проверки наличия ошибки (для подсветки)
function hasError($fieldName) {
    return isset($_COOKIE['error_' . $fieldName]);
}

// Получаем значения полей (Cookies или GET)
$full_name = getValue('full_name');
$phone = getValue('phone');
$email = getValue('email');
$birth_date = getValue('birth_date');
$gender = getValue('gender');

// Языки: из Cookie хранятся как строка через запятую
$languages = [];
if (isset($_COOKIE['form_languages']) && $_COOKIE['form_languages'] !== '') {
    $languages = explode(',', $_COOKIE['form_languages']);
}
// Если есть GET-параметр languages (при ошибке)
if (isset($_GET['languages']) && is_array($_GET['languages'])) {
    $languages = $_GET['languages'];
}

$biography = getValue('biography');
$contract_accepted = (isset($_COOKIE['form_contract_accepted']) && $_COOKIE['form_contract_accepted'] == '1') ||
                     (isset($_GET['contract_accepted']) && $_GET['contract_accepted'] == '1');

$allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Лабораторная №4 — Cookies, валидация</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error-border {
            border: 2px solid #f44336 !important;
            background-color: #ffebee !important;
        }
        .field-error {
            color: #f44336;
            font-size: 0.8rem;
            margin-top: 0.25rem;
            display: block;
        }
        .error-summary {
            background-color: #ffebee;
            border-left: 5px solid #f44336;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>📡 Программно-аппаратные средства Web</h1>
           
            <h2>Задание 4. Валидация формы с использованием Cookies</h2>
            
        </div>
    </header>

    <main class="container">
        <!-- Вывод списка ошибок из Cookies -->
        <?php
        $error_fields = ['full_name', 'phone', 'email', 'birth_date', 'gender', 'languages', 'contract_accepted'];
        $error_messages = [];
        foreach ($error_fields as $field) {
            $err = getError($field);
            if (!empty($err)) $error_messages[] = $err;
        }
        if (!empty($error_messages)): ?>
        <div class="error-summary">
            <strong>❌ Исправьте следующие ошибки:</strong>
            <ul>
                <?php foreach ($error_messages as $msg): ?>
                    <li><?php echo htmlspecialchars($msg); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form action="process.php" method="GET" class="application-form">
            <!-- 1. ФИО -->
            <div class="form-group">
                <label>ФИО <span class="required">*</span></label>
                <input type="text" name="full_name" value="<?php echo $full_name; ?>"
                       class="<?php echo hasError('full_name') ? 'error-border' : ''; ?>">
                <?php if (hasError('full_name')): ?>
                    <span class="field-error">⚠️ <?php echo getError('full_name'); ?></span>
                <?php endif; ?>
            </div>

            <!-- 2. Телефон -->
            <div class="form-group">
                <label>Телефон <span class="required">*</span></label>
                <input type="text" name="phone" value="<?php echo $phone; ?>"
                       class="<?php echo hasError('phone') ? 'error-border' : ''; ?>">
                <?php if (hasError('phone')): ?>
                    <span class="field-error">⚠️ <?php echo getError('phone'); ?></span>
                <?php endif; ?>
            </div>

            <!-- 3. Email -->
            <div class="form-group">
                <label>Email <span class="required">*</span></label>
                <input type="email" name="email" value="<?php echo $email; ?>"
                       class="<?php echo hasError('email') ? 'error-border' : ''; ?>">
                <?php if (hasError('email')): ?>
                    <span class="field-error">⚠️ <?php echo getError('email'); ?></span>
                <?php endif; ?>
            </div>

            <!-- 4. Дата рождения -->
            <div class="form-group">
                <label>Дата рождения <span class="required">*</span></label>
                <input type="date" name="birth_date" value="<?php echo $birth_date; ?>"
                       class="<?php echo hasError('birth_date') ? 'error-border' : ''; ?>">
                <?php if (hasError('birth_date')): ?>
                    <span class="field-error">⚠️ <?php echo getError('birth_date'); ?></span>
                <?php endif; ?>
            </div>

            <!-- 5. Пол -->
            <div class="form-group">
                <label>Пол <span class="required">*</span></label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="male" <?php echo $gender == 'male' ? 'checked' : ''; ?>> Мужской</label>
                    <label><input type="radio" name="gender" value="female" <?php echo $gender == 'female' ? 'checked' : ''; ?>> Женский</label>
                </div>
                <?php if (hasError('gender')): ?>
                    <span class="field-error">⚠️ <?php echo getError('gender'); ?></span>
                <?php endif; ?>
            </div>

            <!-- 6. Языки -->
            <div class="form-group">
                <label>Любимый язык <span class="required">*</span></label>
                <select name="languages[]" multiple size="6" class="<?php echo hasError('languages') ? 'error-border' : ''; ?>">
                    <?php foreach ($allowed_languages as $lang): ?>
                        <option value="<?php echo $lang; ?>" <?php echo in_array($lang, $languages) ? 'selected' : ''; ?>>
                            <?php echo $lang; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (hasError('languages')): ?>
                    <span class="field-error">⚠️ <?php echo getError('languages'); ?></span>
                <?php endif; ?>
            </div>

            <!-- 7. Биография -->
            <div class="form-group">
                <label>Биография</label>
                <textarea name="biography" rows="5"><?php echo $biography; ?></textarea>
            </div>

            <!-- 8. Чекбокс -->
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="contract_accepted" value="1" <?php echo $contract_accepted ? 'checked' : ''; ?>>
                    С контрактом ознакомлен(а) <span class="required">*</span>
                </label>
                <?php if (hasError('contract_accepted')): ?>
                    <span class="field-error">⚠️ <?php echo getError('contract_accepted'); ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="submit-btn">✅ Отправить</button>
        </form>

        <div class="action-buttons">
            <a href="list.php" class="action-btn">📋 Анкеты</a>
            <a href="bd.html" class="action-btn secondary">🗄️ БД</a>
        </div>
    </main>
    <footer><div class="container"><p>Лабораторная №4 — Cookies </p></div></footer>
</body>
</html>
