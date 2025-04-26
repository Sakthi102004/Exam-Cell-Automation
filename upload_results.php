<?php
session_start();
include 'header.php';

include 'db_config.php';

// Session Timeout Mechanism
$timeout_duration = 1800; // 30 minutes in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
$_SESSION['last_activity'] = time(); // Update last activity timestamp

// Role Check (already present, but verified for consistency)
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
    header("Location: login.php");
    exit();
}

// Generate CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission for uploading marks
if (isset($_POST['upload_result'])) {
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $exam_id = $_POST['exam_id'];
    $subject = $_POST['subject'];
    $class = $_POST['class'];

    // Fetch exam_name from exam_schedule based on exam_id (fix 1: improve error handling)
    $stmt = $conn->prepare("SELECT exam_name FROM exam_schedule WHERE id = ?");
    if (!$stmt) {
        echo "<script>alert('Error preparing exam fetch statement: " . addslashes($conn->error) . "'); window.location.href='upload_results.php';</script>";
        exit();
    }
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $exam = $stmt->get_result()->fetch_assoc();
    if (!$exam) {
        echo "<script>alert('No exam found for the selected exam ID. Please select a valid exam.'); window.location.href='upload_results.php';</script>";
        exit();
    }
    $exam_name = $exam['exam_name'];

    // Fetch students for the selected class to validate marks (fix 2: ensure all students have marks)
    $stmt = $conn->prepare("SELECT id, name, roll_number FROM students WHERE class = ?");
    $stmt->bind_param("s", $class);
    $stmt->execute();
    $students = $stmt->get_result();

    $missing_marks = [];
    while ($student = $students->fetch_assoc()) {
        $student_id = $student['id'];
        if (!isset($_POST['marks'][$student_id]) || trim($_POST['marks'][$student_id]) === '') {
            $missing_marks[] = "Student: " . htmlspecialchars($student['name']) . " (Roll Number: " . htmlspecialchars($student['roll_number']) . ")";
        }
    }

    if (!empty($missing_marks)) {
        echo "<script>alert('Marks are missing for the following students:\\n" . implode("\\n", $missing_marks) . "\\nPlease enter marks for all students.'); window.history.back();</script>";
        exit();
    }

    // Loop through the marks for each student
    foreach ($_POST['marks'] as $student_id => $marks) {
        if (is_numeric($marks) && $marks >= 0 && $marks <= 50) {
            // Insert or update marks for each student
            $sql = "INSERT INTO results (student_id, exam_name, subject, marks) 
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE marks = VALUES(marks)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                echo "<script>alert('Error preparing statement: " . addslashes($conn->error) . "'); window.history.back();</script>";
                exit();
            }
            $stmt->bind_param("issi", $student_id, $exam_name, $subject, $marks);
            $stmt->execute();
        } else {
            echo "<script>alert('Invalid marks for student ID $student_id. Marks must be a number between 0 and 50.'); window.history.back();</script>";
            exit();
        }
    }
    echo "<script>alert('Marks uploaded successfully!'); window.location.href = '" . ($_SESSION['role'] == 'admin' ? 'admin_dashboard.php' : 'teacher_dashboard.php') . "';</script>";
    exit(); // Ensure no further code is executed after redirect
}

// Fetch exams and classes
$exams = $conn->query("SELECT * FROM exam_schedule");
$classes = $conn->query("SELECT DISTINCT class FROM exam_schedule");

// Fetch students based on selected class
$students = null;
if (isset($_POST['class']) && !empty($_POST['class'])) {
    $stmt = $conn->prepare("SELECT * FROM students WHERE class = ?");
    $stmt->bind_param("s", $_POST['class']);
    $stmt->execute();
    $students = $stmt->get_result();
}

// Fetch subjects based on selected exam
$subjects = null;
if (isset($_POST['exam_id']) && !empty($_POST['exam_id'])) {
    $stmt = $conn->prepare("SELECT DISTINCT subject FROM exam_schedule WHERE id = ?");
    $stmt->bind_param("i", $_POST['exam_id']);
    $stmt->execute();
    $subjects = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Results</title>
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
            margin-top: 20px;
        }
        form {
            background: #2a2a4a;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 600px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            transition: transform 0.3s ease;
        }
        form:hover {
            transform: translateY(-5px);
        }
        label {
            color: #d1d5db;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        select, input[type="number"], input[type="hidden"] {
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
        select {
            appearance: none;
            background: #3b3b5a url('data:image/svg+xml;utf8,<svg fill="%23b0b0c0" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 10px center;
            cursor: pointer;
        }
        select:focus, input[type="number"]:focus {
            background: #454570;
            box-shadow: 0 0 8px rgba(96, 165, 250, 0.5);
        }
        input[type="number"] {
            max-width: 100px;
        }
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            opacity: 1;
        }
        table {
            width: 100%;
            max-width: 800px;
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
        p {
            color: #d1d5db;
            font-size: 1rem;
            text-align: center;
        }
        @media (max-width: 768px) {
            form { max-width: 100%; }
            table { font-size: 0.9rem; display: block; overflow-x: auto; white-space: nowrap; }
            th, td { padding: 10px; }
            input[type="number"] { max-width: 80px; }
        }
        @media (max-width: 480px) {
            h2 { font-size: 1.8rem; }
            h3 { font-size: 1.3rem; }
            form { padding: 20px; }
            select, input[type="number"], button[type="submit"] { font-size: 0.9rem; padding: 10px; }
            table { font-size: 0.8rem; }
            th, td { padding: 8px; }
        }
    </style>
    <script>
        function updateSubjects() {
            document.getElementById("filterForm").submit();
        }
    </script>
</head>
<body>
    <h2>Upload Results</h2>
    <form method="POST" id="filterForm">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <!-- Exam Selection -->
        <label for="exam_id">Select Exam:</label>
        <select name="exam_id" id="exam_id" required onchange="updateSubjects()">
            <option value="">Select Exam</option>
            <?php while ($exam = $exams->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($exam['id']); ?>" <?php echo isset($_POST['exam_id']) && $_POST['exam_id'] == $exam['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($exam['exam_name']); ?>
                </option>
            <?php } ?>
        </select>

        <!-- Subject Selection -->
        <?php if ($subjects && $subjects->num_rows > 0) { ?>
            <label for="subject">Select Subject:</label>
            <select name="subject" id="subject" required>
                <option value="">Select Subject</option>
                <?php while ($subject = $subjects->fetch_assoc()) { ?>
                    <option value="<?php echo htmlspecialchars($subject['subject']); ?>" <?php echo isset($_POST['subject']) && $_POST['subject'] == $subject['subject'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($subject['subject']); ?>
                    </option>
                <?php } ?>
            </select>
        <?php } else { ?>
            <p>No subjects found for the selected exam.</p>
        <?php } ?>

        <!-- Class Selection -->
        <label for="class">Select Class:</label>
        <select name="class" id="class" required onchange="this.form.submit()">
            <option value="">Select Class</option>
            <?php while ($class = $classes->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($class['class']); ?>" <?php echo isset($_POST['class']) && $_POST['class'] == $class['class'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($class['class']); ?>
                </option>
            <?php } ?>
        </select>

        <!-- Display Students and Marks Input -->
        <?php if ($students && $students->num_rows > 0 && isset($_POST['class']) && isset($_POST['subject'])) { ?>
            <h3>Students in Class: <?php echo htmlspecialchars($_POST['class']); ?></h3>
            <table border="1">
                <thead>
                    <tr>
                        <th>Roll Number</th>
                        <th>Student Name</th>
                        <th>Marks (Max: 50)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = $students->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td>
                            <input type="number" name="marks[<?php echo htmlspecialchars($student['id']); ?>]" placeholder="Enter Marks" min="0" max="50" required oninput="if (this.value > 50) this.value = 50">
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <button type="submit" name="upload_result">Upload Marks</button>
        <?php } elseif (isset($_POST['class']) && isset($_POST['subject'])) { ?>
            <p>No students found for the selected class.</p>
        <?php } ?>
    </form>
    <br>
    <a href="<?php echo htmlspecialchars($_SESSION['role'] == 'admin' ? 'admin_dashboard.php' : 'teacher_dashboard.php'); ?>">Back</a>
    <?php include 'footer.php'; ?>

</body>
</html>