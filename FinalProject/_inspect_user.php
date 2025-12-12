<?php
require 'db_connect.php';
$res = $mysqli->query('SELECT id,email,password FROM users ORDER BY id DESC LIMIT 1');
if ($res) {
    $row = $res->fetch_assoc();
    echo "Row:\n";
    print_r($row);
} else {
    echo 'Query failed: '.$mysqli->error;
}
$mysqli->close();
?>