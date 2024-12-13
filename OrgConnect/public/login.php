<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Database connection
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'orgconnect';

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (!$con) {
    die('Database connection failed: ' . mysqli_connect_error());
}

if (isset($_POST['login'])) {
    $studentId = $_POST['studentid'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($studentId) || empty($password)) {
        echo "<script>
                alert('Student ID and password are required.');
                window.location.href = 'userLogin.php';
              </script>";
        exit();
    }

    // Secure database query to check user credentials
    $stmt = $con->prepare('SELECT id, password, is_verified FROM users WHERE student_id = ?');
    if ($stmt) {
        $stmt->bind_param('s', $studentId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password, $is_verified);
            $stmt->fetch();
            
            if (password_verify($password, $hashed_password)) {
                if ($is_verified) {
                    // Check if the student ID exists in the students table
                    $checkStudentStmt = $con->prepare('SELECT id FROM students WHERE student_id = ?');
                    $checkStudentStmt->bind_param('s', $studentId);
                    $checkStudentStmt->execute();
                    $checkStudentStmt->store_result();

                    if ($checkStudentStmt->num_rows > 0) {
                        // Set session and redirect
                        $_SESSION['loggedin'] = true;
                        $_SESSION['id'] = $id;
                        $_SESSION['studentid'] = $studentId;

                        // Update last login time
                        $updateStmt = $con->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
                        $updateStmt->bind_param('i', $id);
                        $updateStmt->execute();

                        // Redirect to the organization list page
                        header('Location: /OrgConnect/public/listOrgs.html');
                        exit();
                    } else {
                        echo "<script>alert('Your Student ID is not found in the students table.');</script>";
                    }
                    $checkStudentStmt->close();
                } else {
                    echo "<script>alert('Your account is not verified.');</script>";
                }
            } else {
                echo "<script>alert('Incorrect Student ID or password.');</script>";
            }
        } else {
            echo "<script>alert('No user found with that Student ID.');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Database query failed.');</script>";
    }
}
?>