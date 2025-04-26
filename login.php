<?php
session_start();
include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $username; // Store username in session

            if ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($user['role'] == 'teacher') {
                header("Location: teacher_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit();
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "Invalid username!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        /* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Arial', sans-serif;
}

/* Body Styling with Background Image */
body {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background: url('background.jpeg') no-repeat center center/cover; /* Replace 'background.jpg' with your image path */
    color: #fff;
    overflow: hidden;
}

/* Container Styling */
body > div {
    text-align: center;
    background: rgba(0, 0, 0, 0.6); /* Semi-transparent black overlay */
    padding: 40px 30px;
    border-radius: 12px;
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
    max-width: 400px;
    width: 100%;
}

/* Logo Styling */
.logo {
    width: 100px;
    height: auto;
    margin-bottom: 20px;
    animation: fadeIn 1.5s ease-in-out;
}

/* Heading Styling */
h2 {
    font-size: 2rem;
    margin-bottom: 20px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #f9a825; /* Golden yellow color */
}

/* Input Fields Styling */
input[type="text"],
input[type="password"] {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: none;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    font-size: 1rem;
    transition: background 0.3s ease, transform 0.3s ease;
}

input[type="text"]:focus,
input[type="password"]:focus {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.02);
    outline: none;
}

/* Button Styling */
button {
    width: 100%;
    padding: 12px;
    margin-top: 15px;
    border: none;
    border-radius: 6px;
    background: #f9a825;
    color: #fff;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.3s ease;
}

button:hover {
    background: #ffcc00;
    transform: scale(1.05);
}

/* Register Link Styling */
a {
    display: inline-block;
    margin-top: 20px;
    color: #f9a825;
    text-decoration: none;
    font-size: 1rem;
    transition: color 0.3s ease;
}

a:hover {
    color: #ffcc00;
}

/* Animation for Logo */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
/* Password Toggle Icon */
.password-toggle {
    position: relative;
}

.password-toggle i {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #f9a825;
    font-size: 1.2rem;
    transition: color 0.3s ease;
}

.password-toggle i:hover {
    color: #ffcc00;
}

.password-toggle input[type="checkbox"] {
    display: none; /* Hide the actual checkbox */
}
    </style>
</head>
<body>
    <div>
        <img src="logo.png" alt="Logo" class="logo"> <!-- Replace 'logo.png' with your logo path -->
        <h2>Login</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required><br>
            
            <!-- Password Field with Toggle -->
            <div class="password-toggle">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <label for="toggle-password">
                    <i class="fas fa-eye" id="eye-icon"></i> <!-- Eye Icon -->
                </label>
                <input type="checkbox" id="toggle-password" class="toggle-password-checkbox">
            </div>
            
            <button type="submit">Login</button>
        </form>
        <a href="register.php">Register</a>
    </div>

    <!-- JavaScript for Password Toggle -->
    <script>
        const passwordField = document.getElementById('password');
        const toggleCheckbox = document.getElementById('toggle-password');
        const eyeIcon = document.getElementById('eye-icon');

        toggleCheckbox.addEventListener('change', function () {
            if (this.checked) {
                passwordField.type = 'text'; // Show password
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash'); // Change icon to "eye-slash"
            } else {
                passwordField.type = 'password'; // Hide password
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye'); // Change icon back to "eye"
            }
        });
    </script>
</body>
</html>