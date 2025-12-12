<?php
// admin.php

// 1. Start the session to access user data
session_start();

// 2. Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header('Location: index.html'); 
    exit;
}

// 3. Check for Admin Authorization
if (empty($_SESSION['admin_code'])) {
    // If the user is logged in but NOT an admin, redirect them to the standard dashboard
    header('Location: dashboard.php'); 
    exit;
}

// --- The rest of your admin.php file starts here ---
require_once 'db_connect.php'; 
// The $mysqli connection is now available globally
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        /* General Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2.main-title {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-refresh {
            background-color: #007bff;
            color: #fff;
        }

        .btn-go-dashboard {
            background-color: #28a745;
            color: #fff;
        }

        .btn-danger {
            background-color: #dc3545;
            color: #fff;
        }

        .filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .filters input, .filters select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f8f9fa;
            color: #333;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        .actions button {
            margin-right: 5px;
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 4px;
        }

        .actions .edit {
            background-color: #ffc107;
            color: #fff;
        }

        .actions .history {
            background-color: #17a2b8;
            color: #fff;
        }

        .actions .delete {
            background-color: #dc3545;
            color: #fff;
        }

        .analytics-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .analytics-card {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .analytics-card h4 {
            margin-bottom: 10px;
            color: #333;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
        }

        .stat-item {
            background: #fff;
            padding: 10px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>

    <header class="dashboard-header">
        <h1 class="logo">TrashTalker Admin Dashboard</h1>
        <a href="logout.php" class="logout-link">Logout</a>
    </header>

    <div class="container">
        <h2 class="main-title">Admin Dashboard</h2>

        <div class="header-actions">
            <button class="btn btn-refresh" onclick="refreshData()">Refresh Data</button>
            <a class="btn btn-go-dashboard" href="dashboard.php">Go to Dashboard</a>
            <span class="arrow-icon">→</span>
        </div>

        <div class="alert success" id="statusMessage" style="display: none;"></div>

        <h3>System Analytics</h3>
        <div class="analytics-container">
            <div class="analytics-card user-stats">
                <h4>User Statistics</h4>
                <div class="stats-grid">
                    <div class="stat-item">Total Users: <span id="total_users">...</span></div>
                    <div class="stat-item">Admin Users: <span id="admin_users">...</span></div>
                    <div class="stat-item">Standard Users: <span id="standard_users">...</span></div>
                </div>
            </div>
            <div class="analytics-card analysis-stats">
                <h4>Analysis Statistics</h4>
                <div class="stats-grid">
                    <div class="stat-item">Total Analyses: <span id="total_analyses">...</span></div>
                    <div class="stat-item">Last 24h: <span id="last_24h">...</span></div>
                    <div class="stat-item">Last 7d: <span id="last_7d">...</span></div>
                </div>
            </div>
            
            </div>

        <h3>User Management</h3>
        <div class="filters">
            <input type="text" id="searchUsers" placeholder="Search users...">
            <select id="filterTime">
                <option value="all">All Time</option>
                <option value="24h">Last 24 Hours</option>
                <option value="7d">Last 7 Days</option>
            </select>
            <select id="filterType">
                <option value="all">All Users</option>
                <option value="admin">Admin Users</option>
                <option value="standard">Standard Users</option>
            </select>
            <select id="sortBy">
                <option value="name">Sort by Name</option>
                <option value="date">Sort by Date</option>
            </select>
        </div>

        <div class="user-list">
            <table id="userTable">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Registered On</th>
                        <th>Admin</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <tr><td colspan="6">Loading users...</td></tr>
                </tbody>
            </table>
            
            <button class="btn btn-danger" id="deleteSelectedBtn" style="margin-top: 15px;">Delete Selected</button>
        </div>
    </div>

    <div id="editUserModal" style="display: none; border: 1px solid #ccc; padding: 20px; margin-top: 20px; background-color: #f9f9f9; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000; box-shadow: 0 4px 8px rgba(0,0,0,0.2); width: 400px; max-width: 90%;">
        <h3>✏️ Edit User Details</h3>
        <form id="editUserForm">
            <input type="hidden" id="edit-user-id" name="id">

            <label for="edit-name">Name:</label><br>
            <input type="text" id="edit-name" name="name" required><br><br>

            <label for="edit-email">Email:</label><br>
            <input type="email" id="edit-email" name="email" required><br><br>

            <label for="edit-is-admin">Is Admin:</label><br>
            <select id="edit-is-admin" name="is_admin">
                <option value="0">No (Standard User)</option>
                <option value="1">Yes (Admin)</option>
            </select><br><br>

            <button type="submit" class="btn btn-primary">Save Changes</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('editUserModal').style.display='none'">Cancel</button>
        </form>
    </div>
    <script>
    // --- Admin-specific JavaScript Functions ---

    // Function to load user table content
    function loadUserTable() {
        fetch('fetch_users.php')
            .then(response => response.text()) // Get HTML response
            .then(html => {
                document.getElementById('userTableBody').innerHTML = html;
            })
            .catch(error => console.error('Error loading users:', error));
    }

    // Function to load system statistics
    function loadSystemStatistics() {
        console.log('Loading system statistics...');
        fetch('get_system_stats.php')
          .then(response => {
            if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
          })
          .then(data => {
            console.log('Response from get_system_stats.php:', data);
            if (data.success) {
              const stats = data.stats;

              // Update User Stats
              document.getElementById('total_users').textContent = stats.total_users;
              document.getElementById('admin_users').textContent = stats.admin_users;
              document.getElementById('standard_users').textContent = stats.standard_users;

              // Update Analysis Stats
              document.getElementById('total_analyses').textContent = stats.total_analyses;
              document.getElementById('last_24h').textContent = stats.last_24h;
              document.getElementById('last_7d').textContent = stats.last_7d;

              console.log("Updated statistics successfully."); // Debugging log
            } else {
              const errorMessage = data.message || "Unknown error occurred while fetching statistics.";
              console.error("Failed to load statistics:", errorMessage);
            }
          })
          .catch(error => {
            console.error("Error fetching statistics:", error);
          });
    }

    // Unified refresh function
    function refreshData() {
        loadSystemStatistics();
        loadUserTable();
    }
    
    // Initial data load when the page is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log("Loading system statistics..."); // Debugging log
        refreshData(); 
    });


    // --- Modal and Edit/Delete Handlers ---

    // Event delegation for Edit and Delete buttons
    document.addEventListener('click', function(e) {
        // Handle Edit Button Click
        if (e.target.classList.contains('edit')) {
            const userId = e.target.getAttribute('data-id');
            const name = e.target.getAttribute('data-name');
            const email = e.target.getAttribute('data-email');
            const isAdmin = e.target.getAttribute('data-isadmin'); // This should be '0' or '1' from fetch_users.php

            document.getElementById('edit-user-id').value = userId;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-email').value = email;
            document.getElementById('edit-is-admin').value = isAdmin;

            document.getElementById('editUserModal').style.display = 'block';
        }

        // Handle Delete Button Click (for individual row delete)
        if (e.target.classList.contains('delete')) {
            const userId = e.target.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                deleteUser(userId);
            }
        }
    });

    // Handle Edit User Form Submission
    const editUserForm = document.getElementById('editUserForm');
    if (editUserForm) {
        editUserForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            const modal = document.getElementById('editUserModal');
            const statusMessage = document.getElementById('statusMessage');

            try {
                const res = await fetch('edit_user.php', { method: 'POST', body: fd });
                const data = await res.json();
                
                statusMessage.textContent = data.message;
                if (data.success) {
                    statusMessage.className = 'alert success';
                    refreshData(); // Reload data after successful edit
                    modal.style.display = 'none';
                } else {
                    statusMessage.className = 'alert error';
                }
                statusMessage.style.display = 'block';
                setTimeout(() => statusMessage.style.display = 'none', 5000); // Hide after 5 seconds

            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred during the update process.');
                modal.style.display = 'none';
            }
        });
    }

    // Handle Delete Selected Button
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    if (deleteSelectedBtn) {
        deleteSelectedBtn.addEventListener('click', function() {
            const selected = Array.from(document.querySelectorAll('#userTableBody input[type="checkbox"]:checked'))
                                .map(checkbox => checkbox.value);
            
            if (selected.length === 0) {
                alert('Please select at least one user to delete.');
                return;
            }

            if (confirm(`Are you sure you want to delete ${selected.length} user(s)? This action cannot be undone.`)) {
                // Assuming you have a separate delete_users_batch.php or similar endpoint
                fetch('delete_user.php', { // Reusing delete_user.php, assuming it can handle multiple IDs
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ ids: selected }) 
                })
                .then(response => response.json())
                .then(data => {
                    const statusMessage = document.getElementById('statusMessage');
                    statusMessage.textContent = data.message;
                    if (data.success) {
                        statusMessage.className = 'alert success';
                        refreshData(); 
                    } else {
                        statusMessage.className = 'alert error';
                    }
                    statusMessage.style.display = 'block';
                    setTimeout(() => statusMessage.style.display = 'none', 5000); 
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred during the deletion process.');
                });
            }
        });
    }

    // Add filter functionality
    document.getElementById('searchUsers').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('#userTableBody tr');
        rows.forEach(row => {
            const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            row.style.display = name.includes(query) ? '' : 'none';
        });
    });

    document.getElementById('filterTime').addEventListener('change', function() {
        // Implement filtering logic based on time
    });

    document.getElementById('filterType').addEventListener('change', function() {
        // Implement filtering logic based on user type
    });

    document.getElementById('sortBy').addEventListener('change', function() {
        // Implement sorting logic
    });
    </script>
    
    <script src="script.js"></script> 
</body>
</html>