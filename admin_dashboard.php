<?php
session_start();

include 'header.php';


include 'db_config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch the total number of registered students
$stmt = $conn->prepare("SELECT COUNT(*) as total_students FROM students");
$stmt->execute();
$result = $stmt->get_result();
$total_students = $result->fetch_assoc()['total_students'];
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        /* Reset default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: url('background.jpeg') no-repeat center center/cover;
            color: #ffffff;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Header styling */
        h2 {
            text-align: center;
            padding: 20px;
            font-size: 2rem;
            color: #ffd700;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
        }

        /* Sidebar Menu */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100%;
            background: #151521;
            padding-top: 80px;
            box-shadow: 5px 0 15px rgba(0, 0, 0, 0.5);
            transition: 0.3s ease;
        }

        .sidebar:hover {
            width: 270px;
        }

        .link-container a {
            display: block;
            padding: 15px 20px;
            color: #d3d3d3;
            text-decoration: none;
            font-size: 1.1rem;
            transition: 0.3s ease;
            border-left: 4px solid transparent;
        }

        .link-container a:hover {
            color: #ffd700;
            background: rgba(255, 215, 0, 0.1);
            border-left: 4px solid #ffd700;
            transform: translateX(10px);
        }

        /* Main content area */
        .main-content {
            margin-left: 270px;
            padding: 40px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        /* Dashboard Cards */
        .card {
            background: #252540;
            padding: 20px;
            border-radius: 10px;
            width: 300px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-10px);
        }

        .card h3 {
            color: #ffd700;
            margin-bottom: 10px;
            font-size: 1.3rem;
        }

        .card p {
            color: #b0b0c0;
            font-size: 0.95rem;
        }

        .card .stat {
            font-size: 1.8rem;
            color: #00ffcc;
            margin-top: 10px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 220px;
            }
            .card {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Welcome, Admin</h2>
        <div class="link-container">
            <a href="manage_students.php">Manage Students</a>
            <a href="student_list.php">Student List</a>
            <a href="seating_arrangement.php">Generate Seating</a>
            <a href="exam_schedule.php">Create/View Exam Schedule</a>
            <a href="upload_question.php">Upload/Manage Question Papers</a>
            <a href="upload_results.php">Upload Results</a>
            <a href="view_results.php">View Results</a>
            <a href="feedback.php">View Feedback</a>
            <a href="notifications.php">Send Notifications</a>
            <a href="reports.php">Result Reports</a>
        </div>
    </div>

    <div class="main-content">
        <div class="card"><br><br><br><br><br>
            <h3>Student Overview</h3>
            <p>Total Registered Students</p>
            <div class="stat"><?php echo htmlspecialchars($total_students); ?></div>
        </div>
        <div class="card"><br><br><br><br><br>
            <h3>Exam Status</h3>
            <p>Upcoming Exams This Month</p>
            <div class="stat">8</div>
        </div>
        <div class="card"><br><br><br><br><br>
            <h3>Pending Tasks</h3>
            <p>Results to Upload</p>
            <div class="stat">3</div>
        </div>
        <div class="card">
            <h3>Feedback Summary</h3>
            <p>New Feedback Received</p>
            <div class="stat">15</div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>