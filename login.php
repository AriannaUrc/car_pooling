<?php

// Start the session
session_start();

// Check if the user is already logged in, if so, redirect to the index.php
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Only process POST requests for login-related actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle the login logic for AJAX requests
    $rawData = file_get_contents('php://input');

    $data = json_decode($rawData, true);
    var_dump($data); // Add this line for debugging purposes

    
    if ($data && isset($data['user']['id'], $data['user']['username'], $data['user']['role'])) {
        $_SESSION['user_id'] = $data['user']['id'];
        $_SESSION['username'] = $data["user"]['username'];
        $_SESSION['role'] = $data["user"]['role'];

        echo json_encode([
            'success' => true,
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    }

    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>

    <form id="loginForm">
        <input type="text" id="username" placeholder="Username" required><br>
        <input type="password" id="password" placeholder="Password" required><br>
        <select id="role">
            <option value="utente">Utente</option>
            <option value="autista">Autista</option>
        </select><br>
        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.html">Register here</a></p>

    <script>
        const ws = new WebSocket('ws://localhost:8080');

        ws.onopen = () => {
            console.log('WebSocket connected');
        };

        ws.onmessage = (event) => {
            
            const data = JSON.parse(event.data);
            console.log(data)

            alert(data.message);
            if (data.status === 'success') {
                // Correctly access the user data from the nested 'user' object
                const userData = data.user;
                console.log(userData)
                
                // Send the user data to the PHP backend using the fetch API
                fetch('login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    user: {
                    id: userData.id,
                    username: userData.username,
                    role: userData.role
                    }
                })
                })
                .then(response => {
                    console.log("Response Text:", response); // Log raw response
                    /* console.log("this may be jucky");
                    console.log(response.json()); */
                    return response;
                })
                .then(data => {
                    console.log("Parsed JSON Data:", data);
                    if (data.status == "200") {
                        console.log(userData.role)
                        
                        if(userData.role == "autista")
                        window.location.href = 'autista_dashboard.php';  // Redirect to dashboards.php
                        else{
                        window.location.href = 'user_dashboard.php';
                        }
                    } else {
                        alert('Login failed: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error during login process:', error);
                });
            }
        };


        document.getElementById('loginForm').addEventListener('submit', (event) => {
            event.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const role = document.getElementById('role').value;

            ws.send(JSON.stringify({ action: 'login', username, password, role }));
        });
    </script>
</body>
</html>
