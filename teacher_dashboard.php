<?php
session_start();
include 'header.php';

include 'db_config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
    <style>
        /* Reset default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: url('background.jpeg') no-repeat center center/cover;
            color: #f5f7fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            position: relative;
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

        /* Container */
        .dashboard-wrapper {
            display: flex;
            width: 100%;
            max-width: 1440px;
            margin: 0 auto;
            flex-grow: 1;
            padding: 40px;
            gap: 40px;
            opacity: 0;
            animation: fadeIn 0.5s ease forwards;
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        /* Sidebar */
        .sidebar {
            width: 300px;
            background: rgba(15, 23, 42, 0.95);
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6);
            height: fit-content;
            position: sticky;
            top: 40px;
            transform: translateX(-100%);
            animation: slideInLeft 0.6s ease 0.2s forwards;
        }

        @keyframes slideInLeft {
            to { transform: translateX(0); }
        }

        .sidebar .title {
            font-size: 1.9rem;
            color: #d4af37;
            margin-bottom: 45px;
            text-align: center;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-shadow: 0 0 8px rgba(212, 175, 55, 0.4);
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin-bottom: 18px;
            opacity: 0;
            animation: fadeInUp 0.4s ease forwards;
            animation-delay: calc(0.1s * var(--i));
        }

        .sidebar ul li:nth-child(1) { --i: 1; }
        .sidebar ul li:nth-child(2) { --i: 2; }
        .sidebar ul li:nth-child(3) { --i: 3; }
        .sidebar ul li:nth-child(4) { --i: 4; }
        .sidebar ul li:nth-child(5) { --i: 5; }
        .sidebar ul li:nth-child(6) { --i: 6; }
        .sidebar ul li:nth-child(7) { --i: 7; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            padding: 14px 25px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 1.15rem;
            font-weight: 500;
            border-radius: 12px;
            transition: background 0.3s ease, color 0.3s ease, padding-left 0.3s ease, box-shadow 0.3s ease;
        }

        .sidebar ul li a:hover {
            background: #1e40af;
            color: #f5f7fa;
            padding-left: 30px;
            box-shadow: 0 4px 15px rgba(30, 64, 175, 0.5);
        }

        .sidebar ul li a::before {
            content: '✦';
            margin-right: 15px;
            font-size: 1rem;
            color: #d4af37;
            transition: transform 0.3s ease;
        }

        .sidebar ul li a:hover::before {
            color: #f5f7fa;
            transform: scale(1.2);
        }

        /* Main Content */
        .content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 50px;
        }

        .header {
            background: rgba(15, 23, 42, 0.95);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transform: translateY(-50px);
            animation: slideInDown 0.6s ease 0.3s forwards;
        }

        @keyframes slideInDown {
            to { transform: translateY(0); }
        }

        .header h1 {
            font-size: 2.2rem;
            color: #1e40af;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-shadow: 0 0 8px rgba(30, 64, 175, 0.4);
        }

        .header .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header .user-info span {
            font-size: 1.2rem;
            color: #cbd5e1;
            font-weight: 500;
        }

        .header .user-info .avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #d4af37, #a68b2a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #0a1426;
            font-weight: bold;
            box-shadow: 0 0 12px rgba(212, 175, 55, 0.5);
        }

        /* Cards */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 40px;
        }

        .card {
            background: rgba(15, 23, 42, 0.95);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
            opacity: 0;
            animation: fadeInUp 0.5s ease forwards;
            animation-delay: calc(0.1s * var(--i));
        }

        .card:nth-child(1) { --i: 1; }
        .card:nth-child(2) { --i: 2; }
        .card:nth-child(3) { --i: 3; }
        .card:nth-child(4) { --i: 4; }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.7);
        }

        .card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(30, 64, 175, 0.15), transparent 70%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .card:hover::before {
            opacity: 1;
        }

        .card h3 {
            font-size: 1.6rem;
            color: #d4af37;
            margin-bottom: 20px;
            font-weight: 600;
            letter-spacing: 1.2px;
            text-shadow: 0 0 6px rgba(212, 175, 55, 0.3);
        }

        .card p {
            color: #cbd5e1;
            font-size: 1.05rem;
            margin-bottom: 12px;
        }

        .card .stat {
            font-size: 2rem;
            color: #1e40af;
            font-weight: 700;
            margin: 20px 0;
            padding: 10px 20px;
            background: rgba(30, 64, 175, 0.15);
            border-radius: 10px;
            display: inline-block;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .card:hover .stat {
            background: rgba(30, 64, 175, 0.25);
            transform: scale(1.05);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .dashboard-wrapper {
                padding: 30px;
            }
            .sidebar {
                width: 260px;
            }
            .cards {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .dashboard-wrapper {
                flex-direction: column;
                padding: 20px;
            }
            .sidebar {
                width: 100%;
                position: static;
                transform: translateX(0);
                animation: none;
            }
            .sidebar ul li {
                animation-delay: 0s;
            }
            .content {
                width: 100%;
            }
            .header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
                transform: translateY(0);
                animation: none;
            }
            .cards {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .dashboard-wrapper {
                padding: 15px;
            }
            .sidebar {
                padding: 25px;
            }
            .sidebar .title {
                font-size: 1.6rem;
                margin-bottom: 35px;
            }
            .sidebar ul li a {
                font-size: 1.05rem;
                padding: 12px 20px;
            }
            .header h1 {
                font-size: 1.8rem;
            }
            .header .user-info span {
                font-size: 1.1rem;
            }
            .header .user-info .avatar {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }
            .card {
                padding: 25px;
            }
            .card h3 {
                font-size: 1.4rem;
            }
            .card p {
                font-size: 1rem;
            }
            .card .stat {
                font-size: 1.8rem;
                padding: 8px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <div class="sidebar">
            <div class="title">Teacher Portal</div>
            <ul>
                <li><a href="exam_schedule.php">View Exam Schedule</a></li>
                <li><a href="upload_question.php">Upload Question Paper</a></li>
                <li><a href="upload_results.php">Upload Results</a></li>
                <li><a href="view_results.php">View Results</a></li>
                <li><a href="notifications.php">View Notifications</a></li>
                <li><a href="feedback.php">Submit Feedback</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="content">
            <div class="header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                    <div class="avatar">T</div>
                </div>
            </div>
            <div class="cards">
                <div class="card">
                    <h3>Exam Schedule</h3>
                    <p>Next scheduled exam</p>
                    <div class="stat">March 20, 2025</div>
                    <p>Subject: Physics</p>
                </div>
                <div class="card">
                    <h3>Pending Uploads</h3>
                    <p>Question papers to upload</p>
                    <div class="stat">2</div>
                    <p>Due by March 15</p>
                </div>
                <div class="card">
                    <h3>Results Status</h3>
                    <p>Last uploaded result</p>
                    <div class="stat">Uploaded</div>
                    <p>Mar 10, 2025</p>
                </div>
                <div class="card">
                    <h3>Notifications</h3>
                    <p>Unread messages</p>
                    <div class="stat">3</div>
                    <p>From Admin</p>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>