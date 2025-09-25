<?php
// form_user.php (sửa hoàn chỉnh)
// Start the session
session_start();
require_once 'models/UserModel.php';
$userModel = new UserModel();

// Sinh CSRF token nếu chưa có (1 token cho session)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user = null;
$_id = $_GET['id'] ?? null;

// Load user nếu có id
if (!empty($_id)) {
    $user = $userModel->findUserById($_id);
}

// CHỈ kiểm tra CSRF khi có POST (trước khi thao tác DB)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clientToken = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$clientToken)) {
        http_response_code(403);
        exit('CSRF token validation failed');
    }

    // Nếu token hợp lệ, xử lý insert/update
    if (!empty($_POST['submit'])) {
        if (!empty($_id)) {
            $userModel->updateUser($_POST);
        } else {
            $userModel->insertUser($_POST);
        }
        // Sau khi lưu DB thì quay về danh sách
        header('Location: list_users.php');
        exit;
    }
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
        <?php if ($user || empty($_id)) { ?>
            <div class="alert alert-warning" role="alert">User form</div>

            <form method="POST" id="userForm">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$_id, ENT_QUOTES); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES); ?>">

                <div class="form-group">
                    <label for="name">Username</label>
                    <input class="form-control" name="name" placeholder="Username"
                           value="<?php echo !empty($user[0]['name']) ? htmlspecialchars($user[0]['name'], ENT_QUOTES) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="fullname">Fullname</label>
                    <input class="form-control" name="fullname" placeholder="Fullname"
                           value="<?php echo !empty($user[0]['fullname']) ? htmlspecialchars($user[0]['fullname'], ENT_QUOTES) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" name="email" placeholder="Email"
                           value="<?php echo !empty($user[0]['email']) ? htmlspecialchars($user[0]['email'], ENT_QUOTES) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="type">Type</label>
                    <select name="type" class="form-control">
                        <option value="user" <?php echo (!empty($user[0]['type']) && $user[0]['type']=='user') ? 'selected' : '' ?>>User</option>
                        <option value="admin" <?php echo (!empty($user[0]['type']) && $user[0]['type']=='admin') ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Password">
                </div>

                <!-- Submit chính -->
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

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    // build a safe payload: DO NOT include password
    const userData = {
      name: form.elements['name'].value,
      fullname: form.elements['fullname'].value,
      email: form.elements['email'].value,
      type: form.elements['type'].value,
      id: form.elements['id'] ? form.elements['id'].value : ''
    };

    const token = (document.querySelector('input[name="csrf_token"]') || {}).value || '';

    // send small payload, allow browser to keepalive during navigation
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
      }).catch(err => console.warn('Redis sync (fire-and-forget) failed', err));
    } catch (err) {
      console.warn('Fetch error', err);
    }

    // allow normal form submission to continue
  });
});

    </script>
</body>
</html>
