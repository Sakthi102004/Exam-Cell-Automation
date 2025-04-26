<?php
$homeLink = '#'; // Default fallback
if (isset($_SESSION['user_role'])) { // Changed to user_role
    switch (strtolower($_SESSION['user_role'])) { // Convert to lowercase for consistency
        case 'admin':
            $homeLink = 'admin_dashboard.php';
            break;
        case 'teacher':
            $homeLink = 'teacher_dashboard.php';
            break;
        case 'student':
            $homeLink = 'student_dashboard.php';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Cell Automation</title>
    <style>
        /* Reset default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1e1e2f, #2a2a4a);
            color: #ffffff;
            min-height: 100vh;
        }

        /* Header styling */
        header {
            background: #2a2a4a;
            padding: 20px 40px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            position: sticky;
            top: 0;
            z-index: 100;
            position: fixed;
            top: 0;
            width: 100%;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Left section (Logo and Title) */
        .left-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 0 10px rgba(96, 165, 250, 0.5);
            transition: transform 0.3s ease;
        }

        .logo img:hover {
            transform: scale(1.1);
        }

        h1 {
            font-size: 1.8rem;
            color: #60a5fa;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 0 0 5px rgba(96, 165, 250, 0.5);
        }

        /* Right section (Navigation) */
        .right-section {
            display: flex;
            gap: 25px;
        }

        .right-section a {
            color: #d1d5db;
            text-decoration: none;
            font-size: 1.1rem;
            padding: 10px 15px;
            border-radius: 5px;
            transition: color 0.3s ease, background 0.3s ease, transform 0.2s ease;
        }

        .right-section a:hover {
            color: #60a5fa;
            background: rgba(96, 165, 250, 0.1);
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            .right-section {
                flex-wrap: wrap;
                justify-content: center;
                gap: 15px;
            }
            h1 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            header {
                padding: 15px 20px;
            }
            .logo img {
                width: 50px;
                height: 50px;
            }
            h1 {
                font-size: 1.3rem;
            }
            .right-section a {
                font-size: 1rem;
                padding: 8px 12px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="left-section">
                <div class="logo">
                    <img src="logo.png" alt="Company Logo" width="60">
                </div>
                <h1>Exam Cell Automation</h1>
            </div>
            <div class="right-section">
                <?php
                if (isset($_SESSION['user_role'])) { // Changed to user_role
                    echo "<a href='$homeLink'>Home</a>";
                } else {
                    echo "<a href='login.php'>Home</a>";
                }
                ?>
                <a href="about.php">About Us</a>
                <a href="services.php">Services</a>
                <a href="contact.php">Contact</a>
                <a href="logout.php">Logout</a>

                <?php
                if (isset($_SESSION['user_role'])) { // Changed to user_role
                    echo "<a href='logout.php'>Logout</a>";
                }
                ?>
            </div>
        </div>
    </header>
</body>
</html>