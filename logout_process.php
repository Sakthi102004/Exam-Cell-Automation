<?php
// Start the session
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page with a simple styled confirmation
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out</title>
    <style>
        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #0a1426; /* Deep navy */
            color: #f5f7fa;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(20, 40, 80, 0.1), rgba(10, 20, 38, 0.9));
            z-index: -1;
        }

        .logout-container {
            background: rgba(15, 23, 42, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6);
            text-align: center;
            max-width: 500px;
            opacity: 0;
            transform: scale(0.8);
            animation: popIn 0.6s ease-out forwards;
        }

        @keyframes popIn {
            0% { opacity: 0; transform: scale(0.8) translateY(30px); }
            80% { transform: scale(1.05); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }

        h1 {
            font-size: 2rem;
            color: #d4af37; /* Gold accent */
            font-weight: 600;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            margin-bottom: 20px;
            text-shadow: 0 0 8px rgba(212, 175, 55, 0.4);
        }

        p {
            font-size: 1.1rem;
            color: #cbd5e1; /* Soft gray */
            margin-bottom: 30px;
        }

        .btn {
            display: inline-flex;
            padding: 14px 30px;
            background: linear-gradient(135deg, #1e40af, #3b82f6); /* Blue gradient */
            color: #f5f7fa;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(30, 64, 175, 0.5);
            transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
        }

        .btn:hover {
            background: linear-gradient(135deg, #d4af37, #a68b2a); /* Gold gradient */
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.6);
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <h1>Logged Out</h1>
        <p>You have been successfully logged out. Redirecting you shortly...</p>
        <a href="login.php" class="btn">Back to Login</a>
    </div>

    <script>
        // Redirect to login page after 2 seconds
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 2000);
    </script>
</body>
</html>