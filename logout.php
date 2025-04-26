
<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Confirmation</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #0a1426; /* Deep navy background */
            color: #f5f7fa; /* Crisp white text */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            position: relative;
            overflow: hidden;
        }

        /* Background Gradient Overlay */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(20, 40, 80, 0.2), rgba(10, 20, 38, 0.9));
            z-index: -1;
        }

        /* Overlay with Blur */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            opacity: 0;
            animation: fadeInOverlay 0.5s ease forwards;
        }

        @keyframes fadeInOverlay {
            to { opacity: 1; }
        }

        /* Popup Box */
        .popup {
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.95), rgba(30, 64, 175, 0.05)); /* Premium gradient */
            padding: 30px;
            text-align: center;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.7);
            position: absolute;
            z-index: 10;
            width: 360px;
            border: 1px solid rgba(212, 175, 55, 0.2); /* Gold border */
            opacity: 0;
            transform: scale(0.7) translateY(50px);
            animation: popIn 0.6s ease-out 0.2s forwards;
        }

        @keyframes popIn {
            0% { opacity: 0; transform: scale(0.7) translateY(50px); }
            80% { transform: scale(1.05); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* Heading */
        .popup h2 {
            margin: 0 0 25px;
            font-size: 1.8rem;
            color: #d4af37; /* Gold accent */
            font-weight: 600;
            letter-spacing: 1.2px;
            text-shadow: 0 0 8px rgba(212, 175, 55, 0.4);
        }

        /* Button Container */
        .buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        /* Button Styles */
        button {
            padding: 14px 24px;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.4s ease, height 0.4s ease;
        }

        button:hover::before {
            width: 200%;
            height: 200%;
        }

        /* Yes Button */
        .yes {
            background: linear-gradient(135deg, #ef4444, #b91c1c); /* Vibrant red gradient */
            color: #f5f7fa;
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5);
        }

        .yes:hover {
            background: linear-gradient(135deg, #dc2626, #991b1b);
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.6);
        }

        /* No Button */
        .no {
            background: linear-gradient(135deg, #10b981, #047857); /* Rich green gradient */
            color: #f5f7fa;
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
        }

        .no:hover {
            background: linear-gradient(135deg, #059669, #065f46);
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.6);
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .popup {
                width: 300px;
                padding: 25px;
            }
            .popup h2 {
                font-size: 1.5rem;
                margin-bottom: 20px;
            }
            button {
                padding: 12px 20px;
                font-size: 1rem;
            }
            .buttons {
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Overlay -->
    <div class="overlay"></div>

    <!-- Popup -->
    <div class="popup">
        <h2>Are you sure you want to log out?</h2>
        <div class="buttons">
            <button class="yes" onclick="logout()">Yes</button>
            <button class="no" onclick="cancelLogout()">No</button>
        </div>
    </div>

    <script>
        function logout() {
            window.location.href = "logout_process.php"; // Redirect to actual logout process
        }

        function cancelLogout() {
            window.history.back(); // Return to the previous page
        }
    </script>
</body>
</html>