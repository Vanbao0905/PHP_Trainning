<?php
// delete_user.php (minimal CSRF protection)
session_start();
require_once 'models/UserModel.php';
$userModel = new UserModel();

// Optional: check login (recommended)
// if (empty($_SESSION['user_id'])) { http_response_code(403); exit('Forbidden'); }

// Only accept POST for destructive action
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}

// Validate CSRF token from POST (form) or header (AJAX)
$clientToken = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
if (empty($_SESSION['csrf_token']) || empty($clientToken) || 
    !hash_equals((string)$_SESSION['csrf_token'], (string)$clientToken)) {
    http_response_code(403);
    exit('CSRF token validation failed.');
}

// Validate id
$id = $_POST['id'] ?? null;
if ($id === null || !ctype_digit((string)$id)) {
    http_response_code(400);
    exit('Invalid id');
}

// Perform delete (model should use prepared statement)
$deleted = $userModel->deleteUserById((int)$id);

// Redirect back (or you can return JSON if used via AJAX)
header('Location: list_users.php');
exit;
