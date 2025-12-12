<?php
// delete_user.php
// Handles the request to delete a user from the 'users' table.

// 1. Include the database connection file
require_once 'db_connect.php'; 

// Use the mysqli object established in db_connect.php
global $mysqli;

// Set the content type header for JSON response
header('Content-Type: application/json');

// Check if the request method is POST and the ID is set
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    
    // 2. Sanitize and validate the input ID
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    
    if ($id === false) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'Invalid user ID provided.']);
        $mysqli->close();
        exit;
    }

    // 3. Prepare the SQL DELETE statement
    // Note: Due to the FOREIGN KEY with ON DELETE CASCADE defined in your SQL:
    // 'CONSTRAINT `fk_sec_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE'
    // Deleting the user from the `users` table will automatically delete their corresponding
    // security questions from the `security_questions` table.
    $sql = "DELETE FROM users WHERE id = ?";
    
    if ($stmt = $mysqli->prepare($sql)) {
        
        // Bind the ID parameter (i = integer)
        $stmt->bind_param("i", $id); 

        // 4. Execute the statement
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                // Deletion successful
                echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
            } else {
                // No rows affected (user ID didn't exist)
                echo json_encode(['success' => false, 'message' => 'User not found or already deleted.']);
            }
        } else {
            // Execution failed
            http_response_code(500); // Internal Server Error
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        // Statement preparation failed
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'message' => 'Error preparing statement: ' . $mysqli->error]);
    }

    $mysqli->close();

} else {
    // Invalid request method or missing ID field
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Invalid request: Requires POST method with user ID.']);
}
?>