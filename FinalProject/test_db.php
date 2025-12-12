<?php
// test_db.php — place next to db_connect.php
require_once 'db_connect.php';

if (!isset($mysqli) || $mysqli->connect_errno) {
    echo 'Connection FAILED: ' . ($mysqli->connect_error ?? 'no mysqli object');
    exit;
}

// run a simple query
if ($res = $mysqli->query("SELECT COUNT(*) AS c FROM users")) {
    $row = $res->fetch_assoc();
    echo 'Connected OK. users count: ' . ($row['c'] ?? '0');
    $res->free();
} else {
    echo 'Connected but query failed: ' . $mysqli->error;
}

$mysqli->close();
?>