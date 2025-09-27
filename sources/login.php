<?php
// login.php (sửa đồng bộ với UserModel PDO)

// cấu hình cookie cho session (phải trước session_start())
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
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

// Nếu đã login rồi, redirect tới list
if (!empty($_SESSION['user_id'])) {
    header('Location: list_users.php');
    exit;
}

$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Lấy và sanitize input
    $username = trim((string)($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    // Basic validation
    if ($username === '' || $password === '') {
        // Không nêu rõ trường nào sai để tránh thông tin cho attacker
        $_SESSION['message'] = 'Invalid credentials';
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    // Thực hiện auth qua model (model dùng password_verify internally)
    $user = $userModel->auth($username, $password);

    if ($user) {
        // Login thành công: thiết lập session an toàn
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'] ?? '';
        $_SESSION['message'] = 'Login successful';
        header('Location: list_users.php');
        exit;
    } else {
        // Login thất bại (ghi chung chung)
        $_SESSION['message'] = 'Login failed';
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Login</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
<?php include 'views/header.php'?>

<div class="container">
    <div style="max-width:600px;margin:50px auto;">
        <?php if (!empty($message)): ?>
            <div class="alert alert-info" role="alert">
                <?php echo htmlspecialchars((string)$message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="panel-title">Login</div>
                <div style="float:right; font-size: 80%; position: relative; top:-10px">
                    <a href="#">Forgot password?</a>
                </div>
            </div>

            <div class="panel-body" style="padding-top:30px;">
                <form method="post" class="form-horizontal" role="form" autocomplete="off" novalidate>
                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input id="login-username" type="text" class="form-control" name="username"
                               value="" placeholder="username or email" maxlength="191" required>
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        <input id="login-password" type="password" class="form-control" name="password"
                               placeholder="password" required>
                    </div>

                    <div class="margin-bottom-25">
                        <input type="checkbox" tabindex="3" name="remember" id="remember">
                        <label for="remember"> Remember Me</label>
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <div class="col-sm-12 controls">
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
                            <a id="btn-fblogin" href="#" class="btn btn-primary">Login with Facebook</a>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12 control">
                            Don't have an account!
                            <a href="form_user.php"> Sign Up Here </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
