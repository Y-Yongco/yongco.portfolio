// Show Login Form
function showLogin() {
    const loginForm = document.getElementById('loginForm');
    loginForm.classList.add('active');
}

// Show Sign Up Form
function showSignup() {
    const signupForm = document.getElementById('signupForm');
    signupForm.classList.add('active');
}

// Go Back to Home
function goHome(event) {
    if (event) event.preventDefault();
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    
    loginForm.classList.remove('active');
    signupForm.classList.remove('active');
}

// AJAX form handlers
document.addEventListener('DOMContentLoaded', function() {
    // FIX: Target the correct form ID 'loginFormElement' as defined in index.html
    const signupForm = document.getElementById('signupFormElement');
    const loginForm = document.getElementById('loginFormElement'); 

    if (signupForm) {
        signupForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            try {
                const res = await fetch('signup.php', { method: 'POST', body: fd });
                const text = await res.text();
                // Try to parse JSON, otherwise show raw text
                try {
                    const data = JSON.parse(text);
                    alert(data.message || 'No message returned');
                    if (data.success) {
                        this.reset();
                        goHome();
                    }
                    
                } catch (e) {
                    alert('Sign-up error — server returned: ' + res.status + ' — ' + text);
                    console.error('Non-JSON signup response:', res.status, text);
                }
            } catch (err) {
                console.error(err);
                alert('Network or fetch error: ' + err.message);
            }
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            try {
                const res = await fetch('login.php', { method: 'POST', body: fd });
                const text = await res.text();
                try {
                    const data = JSON.parse(text);
                    alert(data.message || 'Login result received.');
                    
                    if (data.success && data.redirect) {
                        // This handles the redirect URL sent by login.php
                        window.location.href = data.redirect;
                    }

                } catch (jsonError) {
                    alert("Login error — server returned: " + res.status + ' — ' + text);
                    console.error("JSON Parse Error:", jsonError);
                }
            } catch (error) {
                alert('An unexpected network error occurred.');
                console.error('Fetch Error:', error);
            }
        });
    }
});


document.addEventListener('DOMContentLoaded', function() {
    // 1. Event listener for all delete buttons
    document.querySelectorAll('.btn-action.delete').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            const userName = this.closest('tr').children[1].textContent; // Grabs the name column

            // 2. Confirmation dialog
            if (confirm(`Are you sure you want to delete user: ${userName} (ID: ${userId})? This action cannot be undone.`)) {
                
                // 3. Send AJAX request to delete_user.php
                fetch('delete_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        // 4. Remove the row from the table on success (without a full page reload)
                        this.closest('tr').remove(); 
                    } else {
                        alert(`Deletion Failed: ${data.message}`);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred during the deletion process.');
                });
            }
        });
    });
});

// Add this logic to your existing JavaScript file

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('delete')) {
        const userId = e.target.getAttribute('data-id');
        
        if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            // Perform the AJAX deletion
            deleteUser(userId);
        }
    }
});

function deleteUser(id) {
    // Using the Fetch API for the AJAX request
    fetch('delete_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + id // Send the ID in the request body
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Re-fetch or manually remove the row from the table
            window.location.reload(); // Simple solution: reload the page to update the list
        } else {
            alert('Deletion failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error during fetch operation:', error);
        alert('An unexpected error occurred during deletion.');
    });
}


/**
 * Fetches system statistics from the server and updates the dashboard.
 */
function loadSystemStatistics() {
    fetch('get_system_stats.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.data) {
                const stats = data.data;

                // --- Update User Statistics ---
                // Assuming you have elements with these IDs in your HTML
                document.getElementById('stat-total-users').textContent = stats.total_users;
                document.getElementById('stat-admin-users').textContent = stats.admin_users;
                document.getElementById('stat-standard-users').textContent = stats.standard_users;
                
                // --- Update Analysis Statistics ---
                document.getElementById('stat-total-sq-count').textContent = stats.total_security_questions;
                document.getElementById('stat-users-with-sq').textContent = stats.users_with_security_questions;

                // Calculate the percentage of users with security questions
                let sqPercentage = 0;
                if (stats.total_users > 0) {
                    sqPercentage = (stats.users_with_security_questions / stats.total_users) * 100;
                }
                document.getElementById('stat-sq-percentage').textContent = `${sqPercentage.toFixed(1)}%`;

            } else {
                console.error("Failed to load statistics:", data.message);
                // Optionally display an error message on the dashboard
            }
        })
        .catch(error => {
            console.error("Error fetching statistics:", error);
            // Optionally display an error message on the dashboard
        });
}

// Call the function when the page loads (or when the dashboard content is ready)
document.addEventListener('DOMContentLoaded', loadSystemStatistics);