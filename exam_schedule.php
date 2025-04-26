<?php
session_start();

include 'header.php';

include 'db_config.php';

// Validate session and role
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'student', 'teacher'])) {
    header("Location: login.php");
    exit();
}

// CSRF token generation (for admin form submission)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Automatically delete expired schedules (runs on every page load)
$current_date = date('Y-m-d'); // Current date in YYYY-MM-DD format
$sql = "DELETE FROM exam_schedule WHERE exam_date < ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("s", $current_date);
    $stmt->execute();
    $stmt->close();
} else {
    // Log error if needed, but don't interrupt the page
    error_log("Failed to prepare delete statement: " . $conn->error);
}

// Handle adding schedule (admin only)
$message = '';
if (isset($_POST['add_schedule']) && $_SESSION['role'] === 'admin' && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $exam_name = $_POST['exam_name'];
    $subject = $_POST['subject'];
    $exam_date = $_POST['exam_date'];
    $class = $_POST['class'];
    $department = $_POST['department'];

    $sql = "INSERT INTO exam_schedule (exam_name, subject, exam_date, class, department) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssss", $exam_name, $subject, $exam_date, $class, $department);
        if ($stmt->execute()) {
            $message = "Schedule added successfully!";
        } else {
            $message = "Error: Failed to add schedule.";
        }
        $stmt->close();
    } else {
        $message = "Error: Database prepare failed.";
    }
}

// Fetch schedules (viewable by all roles)
$schedules = $conn->query("SELECT * FROM exam_schedule");
if ($schedules === false) {
    die("Error fetching schedules: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Exam Schedule</title>
    <style>
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
        h2, h3 {
            font-size: 2rem;
            color: #60a5fa;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 0 10px rgba(96, 165, 250, 0.5);
            margin-bottom: 30px;
            text-align: center;
        }
        h3 {
            font-size: 1.5rem;
            margin-top: 40px;
        }
        form {
            background: #2a2a4a;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 450px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            transition: transform 0.3s ease;
            margin-bottom: 40px;
        }
        form:hover {
            transform: translateY(-5px);
        }
        input[type="text"],
        input[type="date"],
        select {
            width: 100%;
            padding: 12px 15px;
            border: none;
            border-radius: 8px;
            background: #3b3b5a;
            color: #ffffff;
            font-size: 1rem;
            outline: none;
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }
        input[type="text"]::placeholder {
            color: #b0b0c0;
        }
        input[type="text"]:focus,
        input[type="date"]:focus,
        select:focus {
            background: #454570;
            box-shadow: 0 0 8px rgba(96, 165, 250, 0.5);
        }
        input[type="date"] {
            appearance: none;
            cursor: pointer;
        }
        select {
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="%23b0b0c0" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 15px center;
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
        table {
            width: 100%;
            max-width: 900px;
            border-collapse: collapse;
            background: #2a2a4a;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            margin-bottom: 30px;
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
        a {
            color: #60a5fa;
            text-decoration: none;
            font-size: 1rem;
            transition: color 0.3s ease;
        }
        a:hover {
            color: #3b82f6;
            text-decoration: underline;
        }
        .message {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            width: 100%;
            max-width: 450px;
        }
        .success {
            background: #34c759;
            color: #ffffff;
        }
        .error {
            background: #ff4444;
            color: #ffffff;
        }
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
            h3 {
                font-size: 1.3rem;
            }
            form {
                padding: 20px;
            }
            input[type="text"],
            input[type="date"],
            select,
            button[type="submit"] {
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
    <h2>Exam Schedule</h2>
    <?php if ($message && $_SESSION['role'] === 'admin') { ?>
        <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php } ?>
    <?php if ($_SESSION['role'] === 'admin') { ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <select name="exam_name" required>
                <option value="" disabled selected>Select Exam</option>
                <option value="CIE1">CIE1</option>
                <option value="CIE2">CIE2</option>
                <option value="CIE3">CIE3</option>
            </select>
            <input type="text" name="subject" placeholder="Subject" required>
            <input type="date" name="exam_date" required>
            <select name="department" id="department" required>
                <option value="" disabled selected>Select Department</option>
                <option value="Computer Science">Computer Science</option>
                <option value="Computer Application">Computer Application</option>
                <option value="Mathematics">Mathematics</option>
                <option value="Commerce">Commerce</option>
                <option value="Business Administration">Business Administration</option>
                <option value="English">English</option>
            </select>
            <select name="class" id="class" required>
                <option value="" disabled selected>Select Class</option>
            </select>
            <button type="submit" name="add_schedule">Add Schedule</button>
        </form>
    <?php } ?>
    <h3>Schedule List</h3>
    <table border="1">
        <tr><th>Exam Name</th><th>Subject</th><th>Date</th><th>Class</th><th>Department</th></tr>
        <?php while ($row = $schedules->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['exam_name']); ?></td>
                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                <td><?php echo htmlspecialchars($row['exam_date']); ?></td>
                <td><?php echo htmlspecialchars($row['class']); ?></td>
                <td><?php echo htmlspecialchars($row['department']); ?></td>
            </tr>
        <?php } ?>
    </table>
    <a href="<?php echo $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : ($_SESSION['role'] === 'teacher' ? 'teacher_dashboard.php' : 'student_dashboard.php'); ?>">Back</a>

    <script>
        const departmentSelect = document.getElementById('department');
        const classSelect = document.getElementById('class');

        const classOptions = {
            'Computer Science': ['1CS', '2CS', '3CS'],
            'Computer Application': ['1CA', '2CA', '3CA'],
            'Mathematics': ['1MATH', '2MATH', '3MATH'],
            'Commerce': ['1COM', '2COM', '3COM'],
            'Business Administration': ['1BBA', '2BBA', '3BBA'],
            'English': ['1EN', '2EN', '3EN']
        };

        departmentSelect.addEventListener('change', function() {
            const selectedDept = this.value;
            classSelect.innerHTML = '<option value="" disabled selected>Select Class</option>';

            if (classOptions[selectedDept]) {
                classOptions[selectedDept].forEach(className => {
                    const option = document.createElement('option');
                    option.value = className;
                    option.textContent = className;
                    classSelect.appendChild(option);
                });
            }
        });

        // Prevent form submission if dropdowns are not selected
        document.querySelector('form').addEventListener('submit', function(e) {
            const examName = document.querySelector('select[name="exam_name"]').value;
            const department = document.querySelector('select[name="department"]').value;
            const classField = document.querySelector('select[name="class"]').value;

            if (!examName || !department || !classField) {
                e.preventDefault();
                alert('Please select an Exam Name, Department, and Class.');
            }
        });
    </script>
    <?php include 'footer.php'; ?>

</body>
</html>