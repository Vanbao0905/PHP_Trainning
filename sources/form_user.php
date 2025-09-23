<?php
// Start the session
session_start();
require_once 'models/UserModel.php';
$userModel = new UserModel();

$user = NULL; //Add new user
$_id = NULL;

if (!empty($_GET['id'])) {
    $_id = $_GET['id'];
    $user = $userModel->findUserById($_id); //Update existing user
}

if (!empty($_POST['submit'])) {
    if (!empty($_id)) {
        $userModel->updateUser($_POST);
    } else {
        $userModel->insertUser($_POST);
    }
    // Chuyển về danh sách
    header('location: list_users.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User form</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
    <?php include 'views/header.php'?>
    <div class="container">
        <?php if ($user || !isset($_id)) { ?>
            <div class="alert alert-warning" role="alert">
                User form
            </div>
            <form method="POST" id="userForm">
                <input type="hidden" name="id" value="<?php echo $_id ?>">
                
                <div class="form-group">
                    <label for="name">Username</label>
                    <input class="form-control" name="name" placeholder="Username"
                           value="<?php echo !empty($user[0]['name']) ? $user[0]['name'] : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="fullname">Fullname</label>
                    <input class="form-control" name="fullname" placeholder="Fullname"
                           value="<?php echo !empty($user[0]['fullname']) ? $user[0]['fullname'] : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" name="email" placeholder="Email"
                           value="<?php echo !empty($user[0]['email']) ? $user[0]['email'] : '' ?>">
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

            <!-- Nút Sync để gửi dữ liệu localStorage lên Redis -->
            <button onclick="syncToServer()">Sync to Server (Redis)</button>
        <?php } else { ?>
            <div class="alert alert-success" role="alert">
                User not found!
            </div>
        <?php } ?>
    </div>

    <script>
    // Khi submit form: vừa gửi tới server PHP vừa lưu vào localStorage
    document.getElementById("userForm").addEventListener("submit", function() {
        const userData = {
            name: this.name.value,
            fullname: this.fullname.value,
            email: this.email.value,
            type: this.type.value,
            password: this.password.value
        };

        localStorage.setItem("user", JSON.stringify(userData));
        console.log("✅ Lưu localStorage:", userData);
    });

    // Hàm sync lên Redis qua PHP
    function syncToServer() {
        const userData = JSON.parse(localStorage.getItem("user"));
        if (!userData) return alert("⚠️ Chưa có user trong localStorage");

        fetch("save_to_redis.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(userData)
        })
        .then(res => res.text())
        .then(data => console.log("Server response:", data))
        .catch(err => console.error(err));
    }
    </script>
</body>
</html>
