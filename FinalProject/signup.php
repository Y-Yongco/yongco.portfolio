<?php
header('Content-Type: application/json; charset=utf-8');
// Development flag. Set to false for production (disables debug output and plaintext storage).
$DEV = false;
require_once 'db_connect.php';

// Ensure `$mysqli` is initialized
if (!isset($mysqli) || !$mysqli instanceof mysqli) {
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST allowed']);
    exit;
}

// Helper to get POST safely
function get_post($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : null;
}

$name = get_post('name');
$email = get_post('email');
$password = get_post('password');
$confirm = get_post('confirm_password');
$admin_code = get_post('admin_code');
$pet_name = get_post('pet_name');
$birth_city = get_post('birth_city');
$mother_maiden = get_post('mother_maiden');

if (!$name || !$email || !$password || !$confirm || !$pet_name || !$birth_city || !$mother_maiden) {
    echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

if ($password !== $confirm) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

// Check if email exists
$stmt = $mysqli->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
if (!$stmt) {
    $err = $DEV ? $mysqli->error : 'Database error';
    echo json_encode(['success' => false, 'message' => 'Database prepare failed: ' . $err]);
    exit;
}
$stmt->bind_param('s', $email);
if (!$stmt->execute()) {
    $err = $DEV ? $stmt->error : 'Execution error';
    echo json_encode(['success' => false, 'message' => 'Database query failed: ' . $err]);
    $stmt->close();
    exit;
}
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    $stmt->close();
    exit;
}
$stmt->close();

// === CRITICAL FIX: Ensure admin_code is explicitly NULL if empty ===
if (empty($admin_code)) {
    $admin_code = NULL;
}
// ====================================================================

// Insert user
$hashed = password_hash($password, PASSWORD_DEFAULT);
$password_plain = $password; // only used in dev mode below

// If in dev mode, ensure the password_plain column exists and include it in INSERT
if (!empty($DEV)) {
    // Try to add column if it doesn't exist (ignore errors)
    $mysqli->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS password_plain VARCHAR(255) DEFAULT NULL");
    // Prepare insert including plaintext column
    $insert = $mysqli->prepare('INSERT INTO users (name, email, password, admin_code, password_plain) VALUES (?, ?, ?, ?, ?)');
    if (!$insert) {
        $err = $DEV ? $mysqli->error : 'Database error';
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $err]);
        exit;
    }
    $insert->bind_param('sssss', $name, $email, $hashed, $admin_code, $password_plain);
    if (!$insert->execute()) {
        $err = $DEV ? $insert->error : 'Execution error';
        echo json_encode(['success' => false, 'message' => 'Error creating user: ' . $err]);
        $insert->close();
        exit;
    }
} else {
    // Production path: do NOT store plaintext password
    // WARNING: Plaintext password storage (INSECURE)
    // You requested to store passwords without hashing. This is dangerous
    // and should only be used on a local development environment.
    
    // Insert user (store raw password)
    $stored_password = $password;
    $insert = $mysqli->prepare('INSERT INTO users (name, email, password, admin_code) VALUES (?, ?, ?, ?)');
    if (!$insert) {
        $err = $DEV ? $mysqli->error : 'Database error';
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $err]);
        exit;
    }
    // Now $admin_code is explicitly NULL, which mysqli can bind correctly to the VARCHAR column
    $insert->bind_param('ssss', $name, $email, $stored_password, $admin_code);
    if (!$insert->execute()) {
        $err = $DEV ? $insert->error : 'Execution error';
        echo json_encode(['success' => false, 'message' => 'Error creating user: ' . $err]);
        $insert->close();
        exit;
    }
}
$user_id = $insert->insert_id;
$insert->close();

// Insert security questions
$ins2 = $mysqli->prepare('INSERT INTO security_questions (user_id, pet_name, birth_city, mother_maiden_name) VALUES (?, ?, ?, ?)');
$ins2->bind_param('isss', $user_id, $pet_name, $birth_city, $mother_maiden);
if (!$ins2->execute()) {
    // rollback user
    $mysqli->query('DELETE FROM users WHERE id = ' . (int)$user_id);
    echo json_encode(['success' => false, 'message' => 'Error saving security questions']);
    $ins2->close();
    exit;
}
$ins2->close();

echo json_encode(['success' => true, 'message' => 'Account created successfully']);
$mysqli->close();
?>