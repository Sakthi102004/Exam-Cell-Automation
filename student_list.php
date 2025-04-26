<?php
session_start();
include 'header.php';

include 'db_config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch student list
$s_list = $conn->query("SELECT s.*, u.username FROM students s LEFT JOIN users u ON s.user_id = u.id");
if ($s_list === false) {
    die("Error fetching students: " . $conn->error);
}

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header("Location: student_list.php?message=Student deleted successfully");
        exit();
    } else {
        $error = "Error deleting student: " . $conn->error;
    }
    $stmt->close();
}

// Handle edit action
$edit_student = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT s.*, u.username FROM students s LEFT JOIN users u ON s.user_id = u.id WHERE s.id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_student = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Handle edit form submission
if (isset($_POST['update_student'])) {
    $id = $_POST['id'];
    $roll_number = $_POST['roll_number'];
    $name = $_POST['name'];
    $class = $_POST['class'];
    $department = $_POST['department'];

    $stmt = $conn->prepare("UPDATE students SET roll_number = ?, name = ?, class = ?, department = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $roll_number, $name, $class, $department, $id);
    if ($stmt->execute()) {
        header("Location: student_list.php?message=Student updated successfully");
        exit();
    } else {
        $error = "Error updating student: " . $conn->error;
    }
    $stmt->close();
}

$message = isset($_GET['message']) ? $_GET['message'] : '';
$error = isset($error) ? $error : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student List</title>
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
        h2 {
            font-size: 2rem;
            color: #60a5fa;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 0 10px rgba(96, 165, 250, 0.5);
            margin-bottom: 30px;
            text-align: center;
        }
        table {
            width: 100%;
            max-width: 900px;
            border-collapse: collapse;
            background: #2a2a4a;
            border-radius: 10px;
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
            transition: color 0.3s ease;
        }
        a:hover {
            color: #3b82f6;
            text-decoration: underline;
        }
        .action-links a {
            margin-right: 10px;
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
        form {
            background: #2a2a4a;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 450px;
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        input[type="text"] {
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #3b3b5a;
            color: #ffffff;
            font-size: 1rem;
            outline: none;
            transition: background 0.3s ease;
        }
        input[type="text"]:focus {
            background: #454570;
        }
        select {
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #3b3b5a;
            color: #ffffff;
            font-size: 1rem;
            outline: none;
            transition: background 0.3s ease;
        }
        select:focus {
            background: #454570;
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
            transition: background 0.3s ease;
        }
        button[type="submit"]:hover {
            background: #3b82f6;
        }
        @media (max-width: 768px) {
            table {
                font-size: 0.9rem;
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            th, td {
                padding: 10px;
            }
            form {
                max-width: 100%;
            }
        }
        @media (max-width: 480px) {
            h2 {
                font-size: 1.8rem;
            }
            table {
                font-size: 0.8rem;
            }
            th, td {
                padding: 8px;
            }
            input[type="text"],
            select,
            button[type="submit"] {
                font-size: 0.9rem;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <br><br><br><h2>Student List</h2>
    <?php if ($message) { ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php } elseif ($error) { ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <?php if ($edit_student) { ?>
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $edit_student['id']; ?>">
            <input type="text" name="roll_number" value="<?php echo htmlspecialchars($edit_student['roll_number']); ?>" required>
            <input type="text" name="name" value="<?php echo htmlspecialchars($edit_student['name']); ?>" required>
            <input type="text" name="class" value="<?php echo htmlspecialchars($edit_student['class']); ?>" required>
            <select name="department" required>
                <option value="Computer Science" <?php echo $edit_student['department'] === 'Computer Science' ? 'selected' : ''; ?>>Computer Science</option>
                <option value="Computer Application" <?php echo $edit_student['department'] === 'Computer Application' ? 'selected' : ''; ?>>Computer Application</option>
                <option value="Mathematics" <?php echo $edit_student['department'] === 'Mathematics' ? 'selected' : ''; ?>>Mathematics</option>
                <option value="Commerce" <?php echo $edit_student['department'] === 'Commerce' ? 'selected' : ''; ?>>Commerce</option>
                <option value="Business Administration" <?php echo $edit_student['department'] === 'Business Administration' ? 'selected' : ''; ?>>Business Administration</option>
                <option value="English" <?php echo $edit_student['department'] === 'English' ? 'selected' : ''; ?>>English</option>
            </select>
            <button type="submit" name="update_student">Update Student</button>
        </form>
    <?php } ?>

    <table><br><br><br>
        <tr>
            <th>Roll Number</th>
            <th>Name</th>
            <th>Class</th>
            <th>Department</th>
            <th>Username</th>
            <th>Actions</th>
        </tr>
        <?php while ($student = $s_list->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                <td><?php echo htmlspecialchars($student['name']); ?></td>
                <td><?php echo htmlspecialchars($student['class']); ?></td>
                <td><?php echo htmlspecialchars($student['department']); ?></td>
                <td><?php echo htmlspecialchars($student['username'] ?? 'N/A'); ?></td>
                <td class="action-links">
                    <a href="student_list.php?edit=<?php echo $student['id']; ?>">Edit</a>
                    <a href="student_list.php?delete=<?php echo $student['id']; ?>" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>
    <a href="admin_dashboard.php">Back to Dashboard</a>
    <?php include 'footer.php'; ?>
</body>
</html>