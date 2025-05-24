<<?php
require_once 'config.php';

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

if (isset($_GET['delete'])) {
    $idToDelete = (int)$_GET['delete'];
    $conn->query("DELETE FROM users WHERE id = $idToDelete");
    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $lastName = htmlspecialchars($_POST['last_name'] ?? '');
    $name = htmlspecialchars($_POST['name'] ?? '');
    $age = htmlspecialchars($_POST['age'] ?? '');
    $about = $_POST['about'] ?? '';
    $aboutText = implode(" ", preg_split('/(?<=[.?!])\s+/', $about, -1, PREG_SPLIT_NO_EMPTY));

    if (!empty($_POST['edit_id'])) {
        $editId = (int)$_POST['edit_id'];
        $stmt = $conn->prepare("UPDATE users SET last_name=?, name=?, age=?, about=? WHERE id=?");
        $stmt->bind_param("ssisi", $lastName, $name, $age, $aboutText, $editId);
        $stmt->execute();
        $stmt->close();
    } else {
        $phone = preg_replace('/\D+/', '', $_POST['phone'] ?? '');
        $fileName = '';

        if (!empty($_FILES["photo"]["name"])) {
            $uploadDir = "uploads/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = basename($_FILES["photo"]["name"]);
            $uploadFile = $uploadDir . $fileName;
            if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $uploadFile)) {
                $fileName = '';
            }
        }

        $waLink = "https://wa.me/" . $phone;
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($waLink);

        $stmt = $conn->prepare("INSERT INTO users (last_name, name, age, phone, about, photo, qr) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissss", $lastName, $name, $age, $phone, $aboutText, $fileName, $qrUrl);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}

$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Анкета</title>
    <style>
        body {
            margin: 0;
            background-color: #0d1b2a;
            color: #f1f1f1;
            font-family: Arial, sans-serif;
        }
        .azimka {
            display: flex;
            justify-content: space-between;
            max-width: 1100px;
            margin: 40px auto;
            background-color: #1b263b;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            border-radius: 12px;
            overflow: hidden;
        }
        .azi, .pppp {
            flex: 1;
            padding: 30px;
        }
        .azi input, .azi textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 15px;
            background-color: #415a77;
            border: none;
            border-radius: 6px;
            color: white;
        }
        .azi input[type="submit"] {
            background-color: #778da9;
            cursor: pointer;
        }
        .user-info {
            display: flex;
            gap: 20px;
            background-color: #1e2f45;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .left-side {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        .right-side {
            flex: 1;
        }
        .photo {
            max-width: 160px;
            border-radius: 10px;
        }
        .qr {
            width: 100px;
            height: 100px;
            border: 2px solid #fff;
            border-radius: 10px;
        }
        ul.about-list {
            list-style-type: disc;
            padding-left: 20px;
        }
        ul.about-list li {
            margin-bottom: 8px;
            background-color: #415a77;
            padding: 6px 10px;
            border-radius: 6px;
        }
        .actions {
            margin-top: 15px;
        }
        .actions a {
            display: inline-block;
            margin-right: 10px;
            padding: 6px 14px;
            background-color: rgba(255, 255, 255, 0.15);
            color: #f5f5dc;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .actions a:hover {
            background-color: rgba(245, 245, 220, 0.5);
            color: #0d1b2a;
        }
    </style>
</head>
<body>
<div class="azimka">
    <div class="azi">
        <?php if ($editId):
            $editUser = $conn->query("SELECT * FROM users WHERE id = $editId")->fetch_assoc(); ?>
            <h2>Редактирование анкеты</h2>
            <form action="" method="post">
                <input type="hidden" name="edit_id" value="<?= $editUser['id'] ?>">
                <p>Фамилия: <input type="text" name="last_name" value="<?= htmlspecialchars($editUser['last_name']) ?>" required></p>
                <p>Имя: <input type="text" name="name" value="<?= htmlspecialchars($editUser['name']) ?>" required></p>
                <p>Возраст: <input type="text" name="age" value="<?= htmlspecialchars($editUser['age']) ?>" required></p>
                <p>О себе: <textarea name="about" rows="4" required><?= htmlspecialchars($editUser['about']) ?></textarea></p>
                <p><input type="submit" value="Сохранить изменения"></p>
            </form>
        <?php else: ?>
            <h2>Добавить анкету</h2>
            <form action="" method="post" enctype="multipart/form-data">
                <p>Фамилия: <input type="text" name="last_name" required /></p>
                <p>Имя: <input type="text" name="name" required /></p>
                <p>Возраст: <input type="text" name="age" required /></p>
                <p>Номер WhatsApp: <input type="text" name="phone" required /></p>
                <p>Фото: <input type="file" name="photo" accept="image/*" required /></p>
                <p>О себе: <textarea name="about" rows="4" required></textarea></p>
                <p><input type="submit" value="Отправить"></p>
            </form>
        <?php endif; ?>
    </div>

    <div class="pppp">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="user-info">
                <div class="left-side">
                    <?php if (!empty($row['photo'])): ?>
                        <img class="photo" src="uploads/<?= htmlspecialchars($row['photo']) ?>" alt="Фото">
                    <?php endif; ?>
                    <img class="qr" src="<?= htmlspecialchars($row['qr']) ?>" alt="QR-код WhatsApp">
                </div>
                <div class="right-side">
                    <p><strong>Привет, <?= htmlspecialchars($row['name']) ?> <?= htmlspecialchars($row['last_name']) ?>!</strong></p>
                    <p>Вам <?= htmlspecialchars($row['age']) ?> лет.</p>
                    <p>О себе:</p>
                    <ul class="about-list">
                        <?php foreach (preg_split('/(?<=[.?!])\s+/', $row['about'], -1, PREG_SPLIT_NO_EMPTY) as $sentence): ?>
                            <li><?= htmlspecialchars($sentence) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="actions">
                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Удалить анкету?')">Удалить</a>
                        <a href="?edit=<?= $row['id'] ?>">Изменить</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>
