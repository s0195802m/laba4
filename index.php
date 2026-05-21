<?php
// index.php - Форма с валидацией на бэкенде, подсветкой ошибок и Cookies
session_start();

// Функция для получения значения из Cookies или POST (приоритет: POST > Cookie)
function getValue($fieldName, $default = '') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET[$fieldName])) {
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

// Функция для подсветки поля (если есть ошибка)
function hasError($fieldName) {
    return isset($_COOKIE['error_' . $fieldName]);
}

// Получаем значения полей из Cookies (если есть)
$full_name = getValue('full_name');
$phone = getValue('phone');
$email = getValue('email');
$birth_date = getValue('birth_date');
$gender = getValue('gender');
$languages = isset($_COOKIE['form_languages']) ? explode(',', $_COOKIE['form_languages']) : [];
$biography = getValue('biography');
$contract_accepted = isset($_COOKIE['form_contract_accepted']) && $_COOKIE['form_contract_accepted'] == '1';

// Массив допустимых языков
$allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лабораторная работа №4 — Валидация формы с Cookies</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Стили для подсветки ошибок */
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
        .error-summary ul {
            margin: 0.5rem 0 0 1.5rem;
        }
        .error-summary li {
            color: #c62828;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>📡 Программно-аппаратные средства Web</h1>
            <p class="subtitle">(с) Сергей Синица 2020</p>
            <h2>Задание 4. Валидация формы с использованием Cookies</h2>
            <p class="student-info">Выполнил: Дмитрий | Логин: u82461 | Группа: Web-бэкенд</p>
        </div>
    </header>

    <main class="container">
        <section class="intro">
            <p>Заполните форму ниже. Все поля обязательны для заполнения, кроме биографии. Данные сохраняются в Cookies на 1 год при успешной отправке. При ошибках поля подсвечиваются красным.</p>
        </section>

        <!-- Сообщения об ошибках -->
        <?php
        $error_messages = [];
        $error_fields = ['full_name', 'phone', 'email', 'birth_date', 'gender', 'languages', 'contract_accepted'];
        foreach ($error_fields as $field) {
            $err = getError($field);
            if (!empty($err)) {
                $error_messages[] = $err;
            }
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

        <!-- Форма отправляется методом GET (по заданию) -->
        <form action="process.php" method="GET" class="application-form">
            <!-- 1. ФИО -->
            <div class="form-group">
                <label for="full_name">ФИО <span class="required">*</span></label>
                <input type="text" id="full_name" name="full_name" 
                       class="<?php echo hasError('full_name') ? 'error-border' : ''; ?>"
                       value="<?php echo $full_name; ?>">
                <small>Только буквы, пробелы и дефис, не более 150 символов</small>
                <?php if (hasError('full_name')): ?>
                    <span class="field-error">⚠️ <?php echo htmlspecialchars(getError('full_name')); ?></span>
                <?php endif; ?>
            </div>

            <!-- 2. Телефон -->
            <div class="form-group">
                <label for="phone">Телефон <span class="required">*</span></label>
                <input type="tel" id="phone" name="phone"
                       class="<?php echo hasError('phone') ? 'error-border' : ''; ?>"
                       value="<?php echo $phone; ?>">
                <small>Формат: +7XXXXXXXXXX или 8XXXXXXXXXX (только цифры и +)</small>
                <?php if (hasError('phone')): ?>
                    <span class="field-error">⚠️ <?php echo htmlspecialchars(getError('phone')); ?></span>
                <?php endif; ?>
            </div>

            <!-- 3. Email -->
            <div class="form-group">
                <label for="email">E-mail <span class="required">*</span></label>
                <input type="email" id="email" name="email"
                       class="<?php echo hasError('email') ? 'error-border' : ''; ?>"
                       value="<?php echo $email; ?>">
                <small>example@domain.ru</small>
                <?php if (hasError('email')): ?>
                    <span class="field-error">⚠️ <?php echo htmlspecialchars(getError('email')); ?></span>
                <?php endif; ?>
            </div>

            <!-- 4. Дата рождения -->
            <div class="form-group">
                <label for="birth_date">Дата рождения <span class="required">*</span></label>
                <input type="date" id="birth_date" name="birth_date"
                       class="<?php echo hasError('birth_date') ? 'error-border' : ''; ?>"
                       value="<?php echo $birth_date; ?>">
                <small>Формат: ГГГГ-ММ-ДД, возраст не более 120 лет</small>
                <?php if (hasError('birth_date')): ?>
                    <span class="field-error">⚠️ <?php echo htmlspecialchars(getError('birth_date')); ?></span>
                <?php endif; ?>
            </div>

            <!-- 5. Пол -->
            <div class="form-group">
                <label>Пол <span class="required">*</span></label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="male" 
                        <?php echo ($gender == 'male') ? 'checked' : ''; ?> 
                        class="<?php echo hasError('gender') ? 'error-border' : ''; ?>"> Мужской</label>
                    <label><input type="radio" name="gender" value="female" 
                        <?php echo ($gender == 'female') ? 'checked' : ''; ?>
                        class="<?php echo hasError('gender') ? 'error-border' : ''; ?>"> Женский</label>
                </div>
                <?php if (hasError('gender')): ?>
                    <span class="field-error">⚠️ <?php echo htmlspecialchars(getError('gender')); ?></span>
                <?php endif; ?>
            </div>

            <!-- 6. Любимый язык программирования -->
            <div class="form-group">
                <label for="languages">Любимый язык программирования <span class="required">*</span></label>
                <select name="languages[]" id="languages" multiple size="6" 
                        class="<?php echo hasError('languages') ? 'error-border' : ''; ?>">
                    <?php foreach ($allowed_languages as $lang): ?>
                        <option value="<?php echo $lang; ?>" 
                            <?php echo (in_array($lang, $languages)) ? 'selected' : ''; ?>>
                            <?php echo $lang; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Зажмите Ctrl (или Cmd на Mac) для выбора нескольких языков</small>
                <?php if (hasError('languages')): ?>
                    <span class="field-error">⚠️ <?php echo htmlspecialchars(getError('languages')); ?></span>
                <?php endif; ?>
            </div>

            <!-- 7. Биография -->
            <div class="form-group">
                <label for="biography">Биография</label>
                <textarea id="biography" name="biography" rows="5"><?php echo $biography; ?></textarea>
                <small>Не более 5000 символов</small>
                <?php if (hasError('biography')): ?>
                    <span class="field-error">⚠️ <?php echo htmlspecialchars(getError('biography')); ?></span>
                <?php endif; ?>
            </div>

            <!-- 8. Чекбокс с контрактом -->
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="contract_accepted" value="1"
                        <?php echo $contract_accepted ? 'checked' : ''; ?>
                        class="<?php echo hasError('contract_accepted') ? 'error-border' : ''; ?>">
                    С контрактом ознакомлен(а) <span class="required">*</span>
                </label>
                <?php if (hasError('contract_accepted')): ?>
                    <span class="field-error">⚠️ <?php echo htmlspecialchars(getError('contract_accepted')); ?></span>
                <?php endif; ?>
            </div>

            <!-- 9. Кнопка отправки -->
            <div class="form-group">
                <button type="submit" class="submit-btn">✅ Отправить</button>
            </div>
        </form>

        <!-- Ссылки -->
        <div class="action-buttons">
            <a href="list.php" class="action-btn">📋 Посмотреть все анкеты</a>
            <a href="bd.html" class="action-btn secondary">🗄️ Структура БД</a>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Лабораторная работа №4 — Cookies, валидация регулярными выражениями | Май 2026</p>
        </div>
    </footer>
</body>
</html>