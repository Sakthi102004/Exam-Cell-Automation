<?php
$currentYear = date("Y"); // Get current year dynamically
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document with Footer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            
        }

        html, body {
            min-height: 100%; /* Ensures page takes full height */


        }

        /* Sample Content Styling (for demonstration, replace with your actual content) */
        .content {
            padding: 20px;
            min-height: 100vh; /* Ensures content fills at least one viewport height */
        }

        /* Footer Styling */
        footer {
            background: linear-gradient(135deg, #0a1426, #1e293b); /* Deep navy gradient */
            color: #f5f7fa;
            padding: 5px 0; /* Minimal padding */
            text-align: center;
            font-family: 'Poppins', sans-serif;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: 1000; /* Stays above content if needed */
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.2); /* Subtle top shadow */
            position: fixed;
            bottom: 0;
            width: 100%;
            
        }

        .footer-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 5px;
            opacity: 0;
            animation: fadeIn 0.5s ease forwards;
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        .footer-section {
            margin: 10px;
        }

        .footer-section h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #d4af37; /* Gold accent */
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .footer-section p {
            font-size: 0.9rem;
            color: #cbd5e1; /* Soft gray */
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
            display: flex;
            gap: 15px;
        }

        .footer-section ul li {
            display: flex;
            align-items: center;
        }

        .footer-section ul li a {
            text-decoration: none;
            color: #cbd5e1;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s ease, padding-left 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px; /* Space between icon and text */
        }

        .footer-section ul li a:hover {
            color: #d4af37;
            padding-left: 5px;
        }

        .footer-section ul li a i {
            font-size: 1rem;
            color: #d4af37; /* Gold icons */
            transition: transform 0.3s ease;
        }

        .footer-section ul li a:hover i {
            transform: scale(1.2); /* Slight scale on hover */
        }

        .social-icons {
            display: flex;
            gap: 15px;
        }

        .social-icons .icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            font-size: 0.9rem; /* Adjusted for icons */
            color: #f5f7fa;
            background: #1e40af; /* Blue */
            text-decoration: none;
            transition: transform 0.3s ease, background 0.3s ease;
        }

        .social-icons .icon:hover {
            background: #d4af37; /* Gold on hover */
            transform: scale(1.1);
        }

        .footer-bottom {
            font-size: 0.85rem;
            color: #94a3b8; /* Muted gray */
            margin-top: 10px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .footer-container {
                flex-direction: column;
                text-align: center;
            }
            .footer-section ul {
                justify-content: center;
            }
            .footer-section {
                margin: 5px 0;
            }
        }

        @media (max-width: 480px) {
            .footer-container {
                padding: 0 15px;
            }
            .footer-section h3 {
                font-size: 1rem;
            }
            .footer-section p, .footer-section ul li a {
                font-size: 0.85rem;
            }
            .social-icons .icon {
                width: 25px;
                height: 25px;
                font-size: 0.75rem;
            }
            .footer-bottom {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sample Content (Replace with your actual content) -->
    <div class="content">

        <!-- Simulate long content -->
        <div style="height: 120vh;"></div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>We are dedicated to providing quality services and enhancing user experience with innovation.</p>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about_us.php">About</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-icons">
                    <a href="https://www.facebook.com/dbcyelagiri" class="icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://twitter.com/dbcyelagiri1/status/1235807755006406658" class="icon"><i class="fab fa-twitter"></i></a>
                    <a href="https://in.linkedin.com/company/don-bosco-college-co-ed-yelagiri-hills" class="icon"><i class="fab fa-linkedin-in"></i></a>
                    <a href="https://www.instagram.com/dbcyelagiri" class="icon"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© <?php echo $currentYear; ?> YourCompany. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>