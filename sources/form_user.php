<?php
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

// Cấu hình cookie cho session
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
require_once 'models/UserModel.php';
$userModel = new UserModel();

// Sinh CSRF token nếu chưa có
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clientToken = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$clientToken)) {
        http_response_code(403);
        exit('CSRF token validation failed');
    }

    if (!empty($_POST['submit'])) {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $name = trim((string)($_POST['name'] ?? ''));
        $fullname = trim((string)($_POST['fullname'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $type = $_POST['type'] ?? 'user';
        $password = $_POST['password'] ?? '';

        $errors = [];

        if ($name === '' || mb_strlen($name) > 100) $errors[] = 'Invalid name';
        if ($fullname === '' || mb_strlen($fullname) > 200) $errors[] = 'Invalid fullname';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email';
        if (!in_array($type, ['user','admin'], true)) $errors[] = 'Invalid type';

        // Thêm mới thì bắt buộc có password
        if (empty($id) && $password === '') {
            $errors[] = 'Password is required for new users';
        }

        if ($password !== '' && mb_strlen($password) < 8) {
            $errors[] = 'Password too short';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old_post'] = $_POST;
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            $payload = [
                'name' => $name,
                'fullname' => $fullname,
                'email' => $email,
                'type' => $type,
                'id' => $id,
            ];

            if ($password !== '') {
                $payload['password'] = $password; // Model sẽ tự hash
            }

            if (!empty($id)) {
                $userModel->updateUser($payload);
            } else {
                $userModel->insertUser($payload);
            }

            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: list_users.php');
            exit;
        }
    }
}

// Load user nếu có id
$_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$user = null;
if (!empty($_id)) {
    $user = $userModel->findUserById($_id);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>User form</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
    <?php include 'views/header.php'?>
    <div class="container">
        <?php
        if (!empty($_SESSION['form_errors'])) {
            echo '<div class="alert alert-danger" role="alert">';
            foreach ($_SESSION['form_errors'] as $err) {
                echo '<div>' . htmlspecialchars($err, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') . '</div>';
            }
            echo '</div>';
            unset($_SESSION['form_errors']);
        }
        $old = $_SESSION['old_post'] ?? null;
        if (isset($_SESSION['old_post'])) unset($_SESSION['old_post']);
        ?>

        <?php if ($user || empty($_id)) { ?>
            <div class="alert alert-warning" role="alert">User form</div>

            <form method="POST" id="userForm" novalidate>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$_id, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); ?>">

                <div class="form-group">
                    <label for="name">Username</label>
                    <input class="form-control" id="name" name="name" placeholder="Username"
                           value="<?php echo htmlspecialchars($old['name'] ?? ($user['name'] ?? ''), ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); ?>">
                </div>

                <div class="form-group">
                    <label for="fullname">Fullname</label>
                    <input class="form-control" id="fullname" name="fullname" placeholder="Fullname"
                           value="<?php echo htmlspecialchars($old['fullname'] ?? ($user['fullname'] ?? ''), ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email"
                           value="<?php echo htmlspecialchars($old['email'] ?? ($user['email'] ?? ''), ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); ?>">
                </div>

                <div class="form-group">
                    <label for="type">Type</label>
                    <select name="type" id="type" class="form-control">
                        <option value="user" <?php echo ((($old['type'] ?? $user['type'] ?? '') === 'user') ? 'selected' : ''); ?>>User</option>
                        <option value="admin" <?php echo ((($old['type'] ?? $user['type'] ?? '') === 'admin') ? 'selected' : ''); ?>>Admin</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Password (leave blank to keep current)</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Password">
                </div>

                <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
            </form>

        <?php } else { ?>
            <div class="alert alert-success" role="alert">User not found!</div>
        <?php } ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('userForm');
      if (!form) return;

      const handler = function (e) {
        e.preventDefault();

        const userData = {
          name: form.elements['name'].value,
          fullname: form.elements['fullname'].value,
          email: form.elements['email'].value,
          type: form.elements['type'].value,
          id: form.elements['id'] ? form.elements['id'].value : ''
        };

        const token = (document.querySelector('input[name="csrf_token"]') || {}).value || '';

        try {
          fetch('save_to_redis.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-Token': token
            },
            body: JSON.stringify(userData),
            keepalive: true
          }).catch(err => console.warn('Redis sync failed', err))
            .finally(() => {
              form.removeEventListener('submit', handler);
              form.submit();
            });
        } catch (err) {
          console.warn('Fetch error', err);
          form.removeEventListener('submit', handler);
          form.submit();
        }
      };

      form.addEventListener('submit', handler);
    });
    </script>
</body>
</html>
