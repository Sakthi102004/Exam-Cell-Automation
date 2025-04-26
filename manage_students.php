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

// Role Check
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Generate CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Add single student
if (isset($_POST['add_student'])) {
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $roll_number = $_POST['roll_number'];
    $name = $_POST['name'];
    $class = $_POST['class'];
    $department = $_POST['department'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check for duplicate username
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo "<script>alert('Username already exists!'); window.history.back();</script>";
        exit();
    }

    // Proceed with insertion
    $password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, password, role, name) VALUES (?, ?, 'student', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $password, $name);
    $stmt->execute();
    $user_id = $conn->insert_id;

    $sql = "INSERT INTO students (roll_number, name, class, department) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $roll_number, $name, $class, $department);
    $stmt->execute();

    // Set success message
    $_SESSION['success_message'] = "Student added successfully!";

    // Redirect to avoid form resubmission
    header("Location: manage_students.php");
    exit();
}

// Bulk add via CSV
if (isset($_FILES['csv_file'])) {
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    // Validate file type and size
    $file = $_FILES['csv_file'];
    $allowed_types = ['text/csv', 'application/csv', 'text/plain'];
    $max_size = 5 * 1024 * 1024; // 5MB
    $errors = [];

    if (!in_array($file['type'], $allowed_types) || pathinfo($file['name'], PATHINFO_EXTENSION) !== 'csv') {
        $errors[] = "Invalid file type. Only CSV files are allowed.";
    }
    if ($file['size'] > $max_size) {
        $errors[] = "File size exceeds the maximum limit of 5MB.";
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "File upload error: " . $file['error'];
    }

    if (empty($errors)) {
        $handle = fopen($file['tmp_name'], "r");
        if ($handle === false) {
            $errors[] = "Failed to open the CSV file.";
        } else {
            $header = fgetcsv($handle); // Read header
            $expected_header = ['roll_number', 'name', 'class', 'department', 'username', 'password'];
            if ($header !== $expected_header) {
                $errors[] = "Invalid CSV format. Expected header: " . implode(',', $expected_header);
            } else {
                while (($data = fgetcsv($handle)) !== false) {
                    // Validate CSV row
                    if (count($data) !== 6) {
                        $errors[] = "Invalid row format: " . implode(',', $data);
                        continue;
                    }

                    $roll_number = trim($data[0]);
                    $name = trim($data[1]);
                    $class = trim($data[2]);
                    $department = trim($data[3]);
                    $username = trim($data[4]);
                    $password = trim($data[5]);

                    // Validate required fields
                    if (empty($roll_number) || empty($name) || empty($class) || empty($department) || empty($username) || empty($password)) {
                        $errors[] = "Missing required fields in row: " . implode(',', $data);
                        continue;
                    }

                    // Check for duplicate username
                    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    if ($stmt->get_result()->num_rows > 0) {
                        $errors[] = "Username '$username' already exists for student '$name'.";
                        continue;
                    }

                    $password = password_hash($password, PASSWORD_DEFAULT);

                    $sql = "INSERT INTO users (username, password, role, name) VALUES (?, ?, 'student', ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sss", $username, $password, $name);
                    $stmt->execute();

                    $sql = "INSERT INTO students (roll_number, name, class, department) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssss", $roll_number, $name, $class, $department);
                    $stmt->execute();
                }
                // Set success message
                $_SESSION['success_message'] = "Students added successfully!";
            }
            fclose($handle);
        }
    }

    // Display errors if any, then redirect
    if (!empty($errors)) {
        echo "<script>alert('" . implode("\\n", array_map('addslashes', $errors)) . "');</script>";
    }

    // Redirect to avoid form resubmission
    header("Location: manage_students.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
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
        h2, h3 {
            font-size: 2rem;
            color: #60a5fa;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 0 10px rgba(96, 165, 250, 0.5);
            margin-bottom: 20px;
            text-align: center;
        }

        h3 {
            font-size: 1.5rem;
            margin-top: 40px;
        }

        /* Form styling */
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
            margin-bottom: 30px;
        }

        form:hover {
            transform: translateY(-5px);
        }

        input[type="text"],
        input[type="password"],
        input[type="file"],
        input[type="hidden"] {
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

        input[type="text"]::placeholder,
        input[type="password"]::placeholder {
            color: #b0b0c0;
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="file"]:focus {
            background: #454570;
            box-shadow: 0 0 8px rgba(96, 165, 250, 0.5);
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

        small {
            color: #b0b0c0;
            font-size: 0.9rem;
            text-align: center;
        }

        /* Links */
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
            margin-top: 20px;
            font-size: 1rem;
            display: inline-block;
        }

        /* Success message styling */
        .success-message {
            background: #4caf50;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            form {
                max-width: 100%;
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
            input[type="password"],
            input[type="file"],
            button[type="submit"] {
                font-size: 0.9rem;
                padding: 10px;
            }
        }

        select {
            width: 100%;
            padding: 12px 15px;
            border: none;
            border-radius: 8px;
            background: #3b3b5a;
            color: rgb(255, 255, 255);
            font-size: 1rem;
            outline: none;
            transition: background 0.3s ease, box-shadow 0.3s ease;
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="%23b0b0c0" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 15px center;
        }

        select:focus {
            background: #454570;
            box-shadow: 0 0 8px rgba(96, 165, 250, 0.5);
        }
    </style>
</head>
<body>
    <h2>Manage Students</h2>
    
    <?php if (isset($_SESSION['success_message'])) {
        echo "<div class='success-message'>" . $_SESSION['success_message'] . "</div>";
        unset($_SESSION['success_message']);
    } ?>

    <h3>Add Single Student</h3>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="text" name="roll_number" placeholder="Roll Number" required>
        <input type="text" name="name" placeholder="Name" required>
        <input type="text" name="class" placeholder="Class" required>
        <select name="department" required>
            <option value="" disabled selected>Select Department</option>
            <option value="Computer Science">Computer Science</option>
            <option value="Computer Application">Computer Application</option>
            <option value="Mathematics">Mathematics</option>
            <option value="Commerce">Commerce</option>
            <option value="Business Administration">Business Administration</option>
            <option value="English">English</option>
        </select>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="add_student">Add Student</button>
    </form>

    <h3>Bulk Add via CSV</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="file" name="csv_file" accept=".csv" required>
        <small>CSV Format: roll_number,name,class,department,username,password</small>
        <button type="submit">Upload CSV</button>
    </form>

    <a href="admin_dashboard.php" class="back-link">Back</a>
</body>
<script>
    // Prevent form submission if department is not selected
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const department = this.querySelector('select[name="department"]');
            if (department && department.value === "") {
                e.preventDefault();
                alert('Please select a department.');
                department.focus();
            }
        });
    });

    // Add a subtle animation on department selection
    document.querySelectorAll('select[name="department"]').forEach(select => {
        select.addEventListener('change', function() {
            this.style.transition = 'box-shadow 0.3s ease';
            this.style.boxShadow = '0 0 10px rgba(96, 165, 250, 0.8)';
            setTimeout(() => {
                this.style.boxShadow = '0 0 8px rgba(96, 165, 250, 0.5)';
            }, 300);
        });
    });
</script>
<?php include 'footer.php'; ?>
</html>