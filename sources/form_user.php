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
        <?php if ($user || !isset($_id)) { ?>
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
    // Wait DOM loaded
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('userForm');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            // Note: we do NOT call e.preventDefault() here so the form will submit normally
            // to this PHP page (so DB insert/update still happens). We also send an async
            // fetch to save_to_redis.php to keep Redis in sync.

            // Collect user data from form
            const userData = {
                name: this.name.value,
                fullname: this.fullname.value,
                email: this.email.value,
                type: this.type.value,
                password: this.password.value,
                // include id if present (useful)
                id: this.id ? this.id.value : ''
            };

            // Get token dynamically from hidden input
            const tokenInput = document.querySelector('input[name="csrf_token"]');
            const token = tokenInput ? tokenInput.value : '';

            // Save to localStorage (optional)
            try {
                localStorage.setItem("user", JSON.stringify(userData));
                console.log("✅ Lưu localStorage:", userData);
            } catch (err) {
                console.warn('LocalStorage save failed', err);
            }

            // Send async request to save_to_redis.php
            // Important: include credentials so session cookie is sent (server reads $_SESSION)
            fetch("save_to_redis.php", {
                method: "POST",
                credentials: "same-origin", // send cookie for same origin
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-Token": token
                },
                body: JSON.stringify(userData)
            })
            .then(response => response.json().catch(() => null))
            .then(json => {
                if (json) {
                    console.log("save_to_redis response:", json);
                } else {
                    console.log("save_to_redis: non-json response or empty");
                }
            })
            .catch(err => {
                console.error("save_to_redis fetch error:", err);
            });

            // Let the normal form submit continue (do not preventDefault)
        });
    });
    </script>
</body>
</html>
