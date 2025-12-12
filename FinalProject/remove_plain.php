<?php
// remove_plain.php - development helper to drop password_plain column
require_once 'db_connect.php';

// Double-check: only run when connected and column exists
$check = $mysqli->query("SHOW COLUMNS FROM `users` LIKE 'password_plain'");
if ($check && $check->num_rows > 0) {
    if ($mysqli->query("ALTER TABLE `users` DROP COLUMN `password_plain`")) {
        echo "COLUMN_DROPPED";
    } else {
        echo "ERROR_DROP:" . $mysqli->error;
    }
} else {
    echo "NO_COLUMN";
}
$mysqli->close();
?>