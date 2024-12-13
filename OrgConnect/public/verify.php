<?php
// Establish a connection to the MySQL database
$conn = new mysqli('localhost', 'root', '', 'emailverifV2');

// Check for database connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if a verification code is provided in the URL
if (isset($_GET['code']) && !empty($_GET['code'])) {
    $verification_code = $_GET['code'];

    // Prepare and execute a query to find the code in the database
    $stmt = $conn->prepare("SELECT student_id FROM students WHERE verification_code = ?");
    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }
    $stmt->bind_param("s", $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the code exists in the database
    if ($result->num_rows === 1) {
        // Code is valid; retrieve the student's data
        $row = $result->fetch_assoc();
        $student_id = $row['student_id'];

        // Optional: Update the student's record to mark as verified
        $stmt_update = $conn->prepare("UPDATE students SET is_verified = 1 WHERE student_id = ?");
        $stmt_update->bind_param("s", $student_id);
        $stmt_update->execute();
        $stmt_update->close();

        // Redirect the user to the "Create New Password" page
        header("Location: createPass.html?student_id=$student_id");
        exit();
    } else {
        // Invalid verification code
        echo "Invalid or expired verification code.";
    }

    // Close the prepared statement
    $stmt->close();
} else {
    // Verification code not provided
    echo "Verification code is missing.";
}

// Close the database connection
$conn->close();
?>
