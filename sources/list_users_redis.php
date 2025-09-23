<?php
$redis = new Redis();
$redis->connect('web-redis', 6379);

$keys = $redis->keys('user:*');
$users = [];

foreach ($keys as $key) {
    $users[] = json_decode($redis->get($key), true);
}
?>
<!DOCTYPE html>
<html>
<head><title>Users from Redis</title></head>
<body>
    <h2>Users in Redis</h2>
    <table border="1" cellpadding="5">
        <tr>
            <th>Username</th>
            <th>Fullname</th>
            <th>Email</th>
            <th>Type</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['fullname']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['type']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
