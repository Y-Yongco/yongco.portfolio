<?php
require_once 'db_connect.php'; 
header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Login Required.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && 
    isset($_POST['id'], $_POST['name'], $_POST['email'], $_POST['is_admin'])) {
    
    global $mysqli;

    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $name = trim($_POST['name']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $is_admin_flag = filter_var($_POST['is_admin'], FILTER_VALIDATE_INT);

    if ($id === false || empty($name) ||!filter_var($email, FILTER_VALIDATE_EMAIL) ||
        ($is_admin_flag !== 0 && $is_admin_flag !== 1)) {

        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
        exit;
    }

    // Convert 0/1 → YES / NULL
    $admin_code_value = ($is_admin_flag == 1) ? 'YES' : NULL;

    $sql = "UPDATE users SET name = ?, email = ?, admin_code = ? WHERE id = ?";

    if ($stmt = $mysqli->prepare($sql)) {

        $stmt->bind_param("sssi", $name, $email, $admin_code_value, $id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => $stmt->affected_rows > 0 
                    ? 'User details updated successfully.'
                    : 'No changes detected.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error executing update: ' . $stmt->error]);
        }

        $stmt->close();

    } else {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $mysqli->error]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
