
<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

if (isset($_POST['submit'])) {
    // Retrieve the input values
    $fullName = $_POST['fullname'];
    $email = $_POST['email'];
    $studentId = $_POST['studentid'];
    $password = $_POST['password'];
    $reenterPassword = $_POST['reenterpw'];

    // Validate the passwords match
    if ($password !== $reenterPassword) {
        echo "<script>alert('Passwords do not match!');</script>";
        exit;
    }

    // Hash the password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'orgconnect'); // Replace with your database credentials

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the student_id already exists in the students table
    $checkQuery = "SELECT student_id, email FROM students WHERE student_id = ? OR email = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $studentId, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Student ID or Email already exists
        echo "<script>alert('Student ID or Email is already in the student list!');</script>";
        $stmt->close();
        $conn->close();
        exit;
    }

    // Insert the user data into the database
    $sql = "INSERT INTO users (full_name, email, student_id, password, created_at, last_login, is_verified) 
            VALUES (?, ?, ?, ?, NOW(), NULL, 0)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $fullName, $email, $studentId, $hashedPassword);

    if ($stmt->execute()) {
        echo "<script>alert('User registered successfully!');</script>";
        // Optionally redirect the user after successful registration
        echo "<script>window.location.href = 'userlogin.php';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
