<?php
session_start();
include 'header.php';

include 'db_config.php';

// Validate session and role
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
    header("Location: login.php");
    exit();
}

// CSRF token generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle file upload
$message = '';
if (isset($_POST['upload_paper']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $exam_id = $_POST['exam_id'];
    $uploaded_by = $_SESSION['user_id']; // Assuming user_id is set during login
    $file = $_FILES['question_paper']['name'];
    $target = "uploads/" . basename($file);
    $fileType = strtolower(pathinfo($target, PATHINFO_EXTENSION));
    
    // Validate file type (e.g., allow only PDF)
    $allowedTypes = ['pdf'];
    if (!in_array($fileType, $allowedTypes)) {
        $message = "Error: Only PDF files are allowed.";
    } elseif (move_uploaded_file($_FILES['question_paper']['tmp_name'], $target)) {
        $sql = "INSERT INTO question_papers (exam_id, file_path, uploaded_by) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("isi", $exam_id, $target, $uploaded_by);
            if ($stmt->execute()) {
                $message = "Question paper uploaded successfully!";
            } else {
                $message = "Error: Failed to save to database.";
                unlink($target); // Remove file if DB insert fails
            }
            $stmt->close();
        } else {
            $message = "Error: Database prepare failed.";
            unlink($target);
        }
    } else {
        $message = "Error: Failed to upload file.";
    }
}

// Delete question paper (admin only)
if (isset($_GET['delete']) && $_SESSION['role'] == 'admin') {
    $id = (int)$_GET['delete'];
    $sql = "SELECT file_path FROM question_papers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if ($result && file_exists($result['file_path'])) {
            unlink($result['file_path']); // Delete file from server
        }
        
        $sql = "DELETE FROM question_papers WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $message = "Question paper deleted successfully!";
            $stmt->close();
        } else {
            $message = "Error: Failed to delete from database.";
        }
    } else {
        $message = "Error: Failed to retrieve file path.";
    }
}

// Fetch exams and papers
$exams = $conn->query("SELECT * FROM exam_schedule");
if ($exams === false) die("Error fetching exams: " . $conn->error);

$papers = $conn->query("SELECT qp.*, e.exam_name, e.subject, u.name 
                        FROM question_papers qp 
                        JOIN exam_schedule e ON qp.exam_id = e.id 
                        JOIN users u ON qp.uploaded_by = u.id");
if ($papers === false) die("Error fetching papers: " . $conn->error);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Question Paper</title>
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
            gap: 20px;
            transition: transform 0.3s ease;
        }
        form:hover {
            transform: translateY(-5px);
        }
        select, input[type="file"] {
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
        select:focus, input[type="file"]:focus {
            background: #454570;
            box-shadow: 0 0 8px rgba(96, 165, 250, 0.5);
        }
        input[type="file"] {
            padding: 10px 15px;
        }
        input[type="file"]::-webkit-file-upload-button {
            background: #60a5fa;
            color: #1e1e2f;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        input[type="file"]::-webkit-file-upload-button:hover {
            background: #3b82f6;
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
        a {
            color: #60a5fa;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        a:hover {
            color: #3b82f6;
            text-decoration: underline;
        }
        .back-link {
            margin-top: 30px;
            font-size: 1rem;
            display: inline-block;
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
            table {
                font-size: 0.9rem;
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
                max-width: 100%;
            }
            select, input[type="file"], button[type="submit"] {
                font-size: 0.9rem;
                padding: 10px;
            }
            table {
                font-size: 0.8rem;
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <h2>Upload Question Paper</h2>
    <?php if ($message) { ?>
        <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php } ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <select name="exam_id" required>
            <option value="">Select Exam</option>
            <?php while ($exam = $exams->fetch_assoc()) { ?>
                <option value="<?php echo $exam['id']; ?>">
                    <?php echo htmlspecialchars($exam['exam_name'] . " - " . $exam['subject']); ?>
                </option>
            <?php } ?>
        </select>
        <input type="file" name="question_paper" accept=".pdf" required>
        <button type="submit" name="upload_paper">Upload</button>
    </form>
    <?php if ($_SESSION['role'] == 'admin') { ?>
        <h3>Manage Question Papers</h3>
        <table border="1">
            <tr><th>Exam Name</th><th>Subject</th><th>File</th><th>Uploaded By</th><th>Action</th></tr>
            <?php while ($paper = $papers->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($paper['exam_name']); ?></td>
                    <td><?php echo htmlspecialchars($paper['subject']); ?></td>
                    <td><a href="<?php echo htmlspecialchars($paper['file_path']); ?>" download>Download</a></td>
                    <td><?php echo htmlspecialchars($paper['name']); ?></td>
                    <td><a href="?delete=<?php echo $paper['id']; ?>" onclick="return confirm('Are you sure you want to delete this question paper?');">Delete</a></td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>
    <a href="<?php echo $_SESSION['role'] == 'admin' ? 'admin_dashboard.php' : 'teacher_dashboard.php'; ?>" class="back-link">Back</a>
    <?php include 'footer.php'; ?>
</body>
</html>