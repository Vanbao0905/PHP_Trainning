<?php
// Start the session
session_start();

require_once 'models/UserModel.php';
$userModel = new UserModel();

// --- Tạo CSRF token cho session (nếu chưa có) ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// --------------------------------------------------

$params = [];
if (!empty($_GET['keyword'])) {
    $params['keyword'] = $_GET['keyword'];
}

$users = $userModel->getUsers($params);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
    <?php include 'views/header.php'?>
    <div class="container">
        <?php if (!empty($users)) {?>
            <div class="alert alert-warning" role="alert">
                List of users! <br>
                <!-- Hiển thị ví dụ như text an toàn (không phải link hoạt động) -->
                <?php echo htmlspecialchars('Hacker: http://php.local/list_users.php?keyword=ASDF%25%22%3BTRUNCATE+banks%3B%23%23'); ?>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Username</th>
                        <th scope="col">Fullname</th>
                        <th scope="col">Type</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) {?>
                        <tr>
                            <th scope="row"><?php echo htmlspecialchars($user['id']);?></th>
                            <td>
                                <?php echo htmlspecialchars($user['name']);?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($user['fullname']);?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($user['type']);?>
                            </td>
                            <td>
                                <a href="form_user.php?id=<?php echo (int)$user['id'] ?>">
                                    <i class="fa fa-pencil-square-o" aria-hidden="true" title="Update"></i>
                                </a>
                                <a href="view_user.php?id=<?php echo (int)$user['id'] ?>">
                                    <i class="fa fa-eye" aria-hidden="true" title="View"></i>
                                </a>

                                <!-- Secure delete: dùng POST + CSRF token (styled like a link) -->
                                <form method="POST" action="delete_user.php" style="display:inline;margin:0;padding:0;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES); ?>">
                                    <button type="submit" class="btn btn-link" style="padding:0;border:none;color:inherit;">
                                        <i class="fa fa-eraser" aria-hidden="true" title="Delete"></i>
                                    </button>
                                </form>
                                <!-- end secure delete -->

                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php }else { ?>
            <div class="alert alert-dark" role="alert">
                This is a dark alert—check it out!
            </div>
        <?php } ?>
    </div>
</body>
</html>
