<?php
// Database configuration
require_once 'db_connect.php'; 

// Connect to MySQL
if ($mysqli->connect_error) {
    die("<tr><td colspan='6'>Database Connection Failed: " . $mysqli->connect_error . "</td></tr>");
}

// 1. Fetch data from the database
// We don't fetch 'password' directly for display
$sql = "SELECT id, name, email, created_at, admin_code FROM users ORDER BY name ASC";
$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    // 2. Loop through the results and generate HTML rows
    while ($row = $result->fetch_assoc()) {
        
        // Determine admin status: If admin_code is NOT NULL, the user is an admin
        $isAdmin = !is_null($row['admin_code']);
        $adminStatusText = $isAdmin ? 'Yes' : 'No';
        // Note: $adminStatusClass is not used in the provided HTML, but kept for completeness
        
        // Format the date for display (e.g., 12/5/2025)
        $createdAt = date('n/j/Y', strtotime($row['created_at']));

        echo '<tr>';
        echo '<td><input type="checkbox" name="selected_users[]" value="' . $row['id'] . '"></td>';
        echo '<td>' . htmlspecialchars($row['name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['email']) . '</td>';
        echo '<td>' . $row['created_at'] . '</td>';
        echo '<td>' . $adminStatusText . '</td>'; // Use the clearer status text

        // 👇 CORRECTED PART: Send 1 or 0 for the modal to read
        echo '<td class="actions">';
        echo '<button class="btn-action edit" data-id="' . $row['id'] . '" 
                   data-name="' . htmlspecialchars($row['name']) . '" 
                   data-email="' . htmlspecialchars($row['email']) . '" 
                   data-isadmin="' . ($isAdmin ? '1' : '0') . '">Edit</button>'; // <-- FIXED
        echo '<button class="btn-action delete" data-id="' . $row['id'] . '">Delete</button>';
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo "<tr><td colspan='6'>No users found in the database.</td></tr>";
}

$mysqli->close();
?>