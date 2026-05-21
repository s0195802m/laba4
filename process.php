<?php
// process.php - Обработчик формы (GET метод, валидация, Cookies, БД)
require_once 'config.php';

// Очищаем старые Cookies об ошибках перед новой проверкой
foreach (['full_name', 'phone', 'email', 'birth_date', 'gender', 'languages', 'biography', 'contract_accepted'] as $field) {
    setcookie("error_$field", "", time() - 3600, '/');
}

// Получаем данные из GET-запроса
$full_name = trim($_GET['full_name'] ?? '');
$phone = trim($_GET['phone'] ?? '');
$email = trim($_GET['email'] ?? '');
$birth_date = trim($_GET['birth_date'] ?? '');
$gender = $_GET['gender'] ?? '';
$languages = $_GET['languages'] ?? [];
$biography = trim($_GET['biography'] ?? '');
$contract_accepted = isset($_GET['contract_accepted']) && $_GET['contract_accepted'] == '1' ? 1 : 0;

// Массивы для хранения ошибок и данных для Cookies
$errors = [];
$formData = [];

// ==================== ВАЛИДАЦИЯ РЕГУЛЯРНЫМИ ВЫРАЖЕНИЯМИ ====================

// 1. ФИО: только буквы, пробелы, дефис, длина 2-150 символов
$formData['full_name'] = $full_name;
if (empty($full_name)) {
    $errors['full_name'] = "ФИО обязательно для заполнения.";
} elseif (strlen($full_name) < 2) {
    $errors['full_name'] = "ФИО должно содержать минимум 2 символа.";
} elseif (strlen($full_name) > 150) {
    $errors['full_name'] = "ФИО не должно превышать 150 символов.";
} elseif (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u', $full_name)) {
    $errors['full_name'] = "ФИО может содержать только буквы (русские/английские), пробелы и дефис.";
}

// 2. Телефон: проверка регулярным выражением
$formData['phone'] = $phone;
$phone_clean = preg_replace('/[^0-9+]/', '', $phone);
if (empty($phone_clean)) {
    $errors['phone'] = "Телефон обязателен для заполнения.";
} elseif (!preg_match('/^(\+7|8)[0-9]{10}$/', $phone_clean)) {
    $errors['phone'] = "Телефон должен быть в формате +7XXXXXXXXXX или 8XXXXXXXXXX (10 цифр после кода).";
} else {
    // Приводим к единому формату +7...
    if (preg_match('/^8([0-9]{10})$/', $phone_clean, $matches)) {
        $formData['phone'] = '+7' . $matches[1];
    } else {
        $formData['phone'] = $phone_clean;
    }
}

// 3. Email: проверка фильтром + регулярка
$formData['email'] = $email;
if (empty($email)) {
    $errors['email'] = "E-mail обязателен для заполнения.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Введите корректный E-mail (например, user@domain.ru).";
} elseif (strlen($email) > 100) {
    $errors['email'] = "E-mail не должен превышать 100 символов.";
}

// 4. Дата рождения
$formData['birth_date'] = $birth_date;
if (empty($birth_date)) {
    $errors['birth_date'] = "Дата рождения обязательна для заполнения.";
} else {
    $date_obj = DateTime::createFromFormat('Y-m-d', $birth_date);
    if (!$date_obj || $date_obj->format('Y-m-d') !== $birth_date) {
        $errors['birth_date'] = "Дата рождения должна быть в формате ГГГГ-ММ-ДД.";
    } else {
        $today = new DateTime();
        $age = $today->diff($date_obj)->y;
        if ($date_obj > $today) {
            $errors['birth_date'] = "Дата рождения не может быть в будущем.";
        } elseif ($age > 120) {
            $errors['birth_date'] = "Возраст не может превышать 120 лет.";
        }
    }
}

// 5. Пол
$formData['gender'] = $gender;
$allowed_genders = ['male', 'female'];
if (empty($gender)) {
    $errors['gender'] = "Выберите пол.";
} elseif (!in_array($gender, $allowed_genders)) {
    $errors['gender'] = "Выберите 'Мужской' или 'Женский'.";
}

// 6. Языки программирования
$allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
$formData['languages'] = implode(',', $languages);
if (empty($languages)) {
    $errors['languages'] = "Выберите хотя бы один любимый язык программирования.";
} else {
    $invalid_langs = array_diff($languages, $allowed_languages);
    if (!empty($invalid_langs)) {
        $errors['languages'] = "Выбраны недопустимые языки: " . implode(', ', $invalid_langs);
    }
}

// 7. Биография
$formData['biography'] = $biography;
if (strlen($biography) > 5000) {
    $errors['biography'] = "Биография не должна превышать 5000 символов.";
}

// 8. Чекбокс
$formData['contract_accepted'] = $contract_accepted;
if (!$contract_accepted) {
    $errors['contract_accepted'] = "Вы должны ознакомиться с контрактом и подтвердить согласие.";
}

// ==================== ЕСЛИ ЕСТЬ ОШИБКИ ====================
if (!empty($errors)) {
    // Сохраняем ошибки и данные в Cookies (до конца сессии)
    foreach ($errors as $field => $message) {
        setcookie("error_$field", $message, 0, '/');
    }
    foreach ($formData as $field => $value) {
        setcookie("form_$field", $value, 0, '/');
    }
    
    // Перенаправляем обратно на форму с GET-параметрами
    $query = http_build_query(array_filter($formData, function($v) { return $v !== '' && $v !== []; }));
    header("Location: index.php?" . $query);
    exit;
}

// ==================== УСПЕШНАЯ ВАЛИДАЦИЯ ====================
// Сохраняем данные в Cookies на 1 год (365 дней)
foreach ($formData as $field => $value) {
    setcookie("form_$field", $value, time() + 365 * 24 * 3600, '/');
}

// Сохраняем в базу данных
try {
    $pdo->beginTransaction();
    
    // Вставка анкеты
    $sql = "INSERT INTO applications (full_name, phone, email, birth_date, gender, biography, contract_accepted) 
            VALUES (:full_name, :phone, :email, :birth_date, :gender, :biography, :contract_accepted)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':full_name' => $formData['full_name'],
        ':phone' => $formData['phone'],
        ':email' => $formData['email'],
        ':birth_date' => $formData['birth_date'],
        ':gender' => $formData['gender'],
        ':biography' => $formData['biography'],
        ':contract_accepted' => $formData['contract_accepted']
    ]);
    
    $application_id = $pdo->lastInsertId();
    
    // Вставка языков
    $lang_sql = "SELECT id FROM programming_languages WHERE name = :name";
    $lang_stmt = $pdo->prepare($lang_sql);
    
    $link_sql = "INSERT INTO application_languages (application_id, language_id) VALUES (:app_id, :lang_id)";
    $link_stmt = $pdo->prepare($link_sql);
    
    foreach ($languages as $lang_name) {
        $lang_stmt->execute([':name' => $lang_name]);
        $lang_row = $lang_stmt->fetch();
        if ($lang_row) {
            $link_stmt->execute([
                ':app_id' => $application_id,
                ':lang_id' => $lang_row['id']
            ]);
        }
    }
    
    $pdo->commit();
    
    // Успех — перенаправляем на страницу успеха
    header("Location: success.php?id=" . $application_id);
    exit;
    
} catch (PDOException $e) {
    $pdo->rollBack();
    setcookie("error_general", "Ошибка базы данных: " . $e->getMessage(), 0, '/');
    header("Location: index.php");
    exit;
}
?>