<?php
session_start();


include 'db_config.php';
if (!isset($_SESSION['role'])) header("Location: login.php");

if (isset($_POST['submit_feedback']) && in_array($_SESSION['role'], ['teacher', 'student'])) {
    $message = $_POST['message'];
    $user_id = $_SESSION['user_id'];
    
    $sql = "INSERT INTO feedback (user_id, message) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
}

$feedbacks = $conn->query("SELECT f.message, f.submitted_at, u.name, u.role FROM feedback f JOIN users u ON f.user_id = u.id ORDER BY f.submitted_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Feedback</title>
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
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Header styling */
        h2 {
            font-size: 2rem;
            color: #60a5fa;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 0 10px rgba(96, 165, 250, 0.5);
            margin-bottom: 30px;
            text-align: center;
        }

        /* Form styling (for teacher/student) */
        form {
            background: #2a2a4a;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            transition: transform 0.3s ease;
        }

        form:hover {
            transform: translateY(-5px);
        }

        textarea {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 8px;
            background: #3b3b5a;
            color: #ffffff;
            font-size: 1rem;
            outline: none;
            resize: vertical;
            min-height: 150px;
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }

        textarea::placeholder {
            color: #b0b0c0;
        }

        textarea:focus {
            background: #454570;
            box-shadow: 0 0 8px rgba(96, 165, 250, 0.5);
        }

        button[type="submit"] {
            padding: 12px;
            background: #60a5fa;
            color: #1e1e2f;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        button[type="submit"]:hover {
            background: #3b82f6;
            transform: scale(1.05);
        }

        button[type="submit"]:active {
            transform: scale(0.98);
        }

        /* Table styling (for admin) */
        table {
            width: 100%;
            max-width: 900px;
            border-collapse: collapse;
            margin-top: 20px;
            background: #2a2a4a;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border: 1px solid #3b3b5a;
        }

        th {
            background: #60a5fa;
            color: #1e1e2f;
            font-weight: bold;
            text-transform: uppercase;
        }

        td {
            color: #d1d5db;
        }

        tr:nth-child(even) {
            background: #3b3b5a;
        }

        tr:hover {
            background: #454570;
            transition: background 0.3s ease;
        }

        /* Back link */
        a {
            margin-top: 30px;
            color: #60a5fa;
            text-decoration: none;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #3b82f6;
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            form {
                max-width: 100%;
            }
            table {
                font-size: 0.9rem;
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            th, td {
                padding: 10px;
            }
        }

        @media (max-width: 480px) {
            h2 {
                font-size: 1.8rem;
            }
            form {
                padding: 20px;
            }
            textarea, button[type="submit"] {
                font-size: 0.9rem;
                padding: 10px;
            }
            table {
                font-size: 0.8rem;
            }
            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <?php if (in_array($_SESSION['role'], ['teacher', 'student'])) { ?>
        <h2>Submit Feedback</h2>
        <form method="POST">
            <textarea name="message" placeholder="Your Feedback" required></textarea>
            <button type="submit" name="submit_feedback">Submit</button>
        </form>
    <?php } ?>
    <?php if ($_SESSION['role'] == 'admin') { ?>
        <h2>View Feedback</h2>
        <table border="1">
            <tr><th>Message</th><th>Submitted By</th><th>Role</th><th>Date</th></tr>
            <?php while ($feedback = $feedbacks->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $feedback['message']; ?></td>
                    <td><?php echo $feedback['name']; ?></td>
                    <td><?php echo $feedback['role']; ?></td>
                    <td><?php echo $feedback['submitted_at']; ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>
    <a href="<?php echo $_SESSION['role'] == 'admin' ? 'admin_dashboard.php' : ($_SESSION['role'] == 'teacher' ? 'teacher_dashboard.php' : 'student_dashboard.php'); ?>">Back</a>
    <?php include 'footer.php'; ?>
</body>
</html>
