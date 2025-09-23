<?php
// Kết nối Redis container (service name = redis trong docker-compose.yml)
$redis = new Redis();
$redis->connect('web-redis', 6379);

// Lấy JSON từ client
$data = json_decode(file_get_contents("php://input"), true);

var_dump($data);

if ($data) {
    // Lưu vào Redis (ví dụ key = "user:{name}")
    $key = "user:" . $data['name'];
    $redis->set($key, json_encode($data));

    echo "✅ Saved user to Redis with key $key";
} else {
    echo "❌ No data received";
}
