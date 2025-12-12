<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once 'db_connect.php'; 
global $mysqli;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST allowed']);
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : null;
$password = isset($_POST['password']) ? $_POST['password'] : null;

if (!$email || !$password) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Missing credentials']);
    exit;
}

// Define the required admin code for clear reference (used to check the DB value)
// NOTE: Based on your previous files, this is used for a special admin setup.
$REQUIRED_ADMIN_CODE = 'YES';

// 1. Select 'password' and 'admin_code'
$stmt = $mysqli->prepare('SELECT id, name, password, admin_code FROM users WHERE email = ? LIMIT 1');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database query preparation failed: ' . $mysqli->error]);
    exit;
}

$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    // Return generic error message for security
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    $stmt->close();
    exit;
}
$user = $result->fetch_assoc();
$stmt->close();

// 2. Verify Password (Assumes plaintext storage based on your signup.php)
if (!hash_equals($user['password'], $password)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

// 3. Login Successful: Set Session Variables
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];

// Store the admin_code from the database in the session.
$_SESSION['admin_code'] = $user['admin_code']; 

// 4. Determine the redirection path and return it in the JSON response
$redirect_url = 'dashboard.php';
$is_admin = !empty($user['admin_code']); // Check if admin_code is NOT NULL/empty

if ($is_admin) {
    // If the user has an admin code, they are an admin
    $redirect_url = 'admin.php';
}

echo json_encode([
    'success' => true, 
    'message' => 'Login successful. Redirecting...', 
    'redirect' => $redirect_url
]);

exit;
?>