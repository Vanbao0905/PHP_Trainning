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
    header('location: list_users.php');
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
            <form method="POST">
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

                <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
            </form>
        <?php } else { ?>
            <div class="alert alert-success" role="alert">
                User not found!
            </div>
        <?php } ?>
    </div>
</body>
<script>
// Khi submit form
document.querySelector("form").addEventListener("submit", function(e) {
    const userData = {
        name: document.querySelector("[name='name']").value,
        fullname: document.querySelector("[name='fullname']").value,
        email: document.querySelector("[name='email']").value,
        type: document.querySelector("[name='type']").value,
        password: document.querySelector("[name='password']").value
    };

    // Lưu vào localStorage (dữ liệu tạm trên browser)
    localStorage.setItem("user", JSON.stringify(userData));

    console.log("✅ Lưu localStorage:", userData);
});
function syncToServer() {
    const userData = JSON.parse(localStorage.getItem("user"));
    if (!userData) return;

    fetch("save_to_redis.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(userData)
    })
    .then(res => res.text())
    .then(data => console.log("Server response:", data))
    .catch(err => console.error(err));
}

<button onclick="syncToServer()">Sync to Server (Redis)</button>

</script>
</html>
