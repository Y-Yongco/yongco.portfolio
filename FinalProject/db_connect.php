<?php
// db_connect.php
// Simple mysqli connection. Update credentials if needed.
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'ecoscan';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}
$mysqli->set_charset('utf8mb4');

// Use $mysqli in other scripts by including this file.
?>