<?php
// save_to_redis.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

session_start();

// Get client CSRF token from header first, fallback to JSON body later
$clientToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

// Read raw body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// If header token missing, try to fetch from JSON body field "csrf_token"
if (empty($clientToken) && is_array($data)) {
    $clientToken = $data['csrf_token'] ?? null;
}
// remove password before saving to redis
if (isset($toSave['password'])) {
    unset($toSave['password']);
}

// Validate CSRF token
if (empty($_SESSION['csrf_token']) || empty($clientToken) || !hash_equals($_SESSION['csrf_token'], (string)$clientToken)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'CSRF token validation failed.']);
    exit;
}

// Validate JSON decode
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON body.']);
    exit;
}

// Minimal input validation (adjust according to your app rules)
$name = trim((string)($data['name'] ?? ''));
$email = trim((string)($data['email'] ?? ''));
$type = trim((string)($data['type'] ?? 'user'));
$password = (string)($data['password'] ?? '');

// Require at least name
if ($name === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Missing required field: name']);
    exit;
}

// Optional: further validations (email format, password length, type whitelist)
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Invalid email format']);
    exit;
}

if (!in_array($type, ['user', 'admin'], true)) {
    $type = 'user';
}

// Sanitize Redis key: allow alnum, dash, underscore
$safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
$key = "user:" . $safeName;

// Prepare value to save (remove csrf_token if present)
$toSave = $data;
unset($toSave['csrf_token']);

// Optionally hash password before saving (if you intend to store it)
if ($password !== '') {
    // NOTE: If saving to Redis as a cache only, do NOT store plaintext password in production.
    // $toSave['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    // unset($toSave['password']);
}

// Connect to Redis and save
try {
    $redis = new Redis();
    // host 'web-redis' matches your docker-compose service name; change if different
    $connected = $redis->connect('web-redis', 6379, 2.0); // timeout 2s
    if (!$connected) {
        throw new RuntimeException('Could not connect to Redis');
    }

    // Save as JSON string; consider expiry if needed
    $ok = $redis->set($key, json_encode($toSave, JSON_UNESCAPED_UNICODE));
    if ($ok === false) {
        throw new RuntimeException('Redis set failed');
    }

    http_response_code(200);
    echo json_encode(['ok' => true, 'message' => "Saved user to Redis", 'key' => $key]);
    exit;
} catch (Throwable $e) {
    // Log $e->getMessage() somewhere server-side in production
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error: ' . $e->getMessage()]);
    exit;
}
