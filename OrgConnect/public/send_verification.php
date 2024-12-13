<?php
// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Autoload dependencies (installed via Composer)
require __DIR__ . '/../vendor/autoload.php';

// Establish a connection to the MySQL database
$conn = new mysqli('localhost', 'root', '', 'emailverifV2');

// Check for database connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify the 'student_id' field exists and is not empty
    if (isset($_POST['student_id']) && !empty($_POST['student_id'])) {
        $student_id = $_POST['student_id']; // Get student ID from form input

        // Prepare and execute the query to fetch the email address
        $stmt = $conn->prepare("SELECT email FROM students WHERE student_id = ?");
        if (!$stmt) {
            die("Query preparation failed: " . $conn->error);
        }
        $stmt->bind_param("s", $student_id); // Bind the input to the query
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the student ID exists in the database
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $email = $row['email']; // Get the email address

            // Generate a unique verification link
            $verification_code = bin2hex(random_bytes(16)); // Generate a random 16-byte string
            $verification_link = "http://localhost/OrgConnect-Website/public/createPass.html?code=$verification_code";

            // Initialize PHPMailer
            $mail = new PHPMailer(true);

            try {
                // SMTP server configuration
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Gmail's SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'orgconnect123@gmail.com'; // Your Gmail address
                $mail->Password = 'eqpo kfnq wpoh mdms';   // Google App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
                $mail->Port = 587; // TCP port for Gmail

                // Email sender and recipient settings
                $mail->setFrom('orgconnect123@gmail.com', 'Verification System'); // Sender's email
                $mail->addAddress($email); // Recipient's email

                // Email content
                $mail->isHTML(true); // Enable HTML format
                $mail->Subject = 'One-Tap Verification'; // Subject of the email
                $mail->Body = "Click the link below to verify your account:<br><br>
                               <a href='$verification_link'>$verification_link</a>"; // Email body

                // Send the email
                $mail->send();

                // Success message
                echo "Verification email sent successfully to $email.";
            } catch (Exception $e) {
                // Handle errors if email fails to send
                error_log("Mailer Error: " . $mail->ErrorInfo);
                echo "Email could not be sent. Please try again later.";
            }
        } else {
            // Handle case where student ID is not found in the database
            echo "Student ID not found.";
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        // Handle case where 'student_id' is missing or empty
        echo "Student ID is required.";
    }
} else {
    // Handle invalid request method
    echo "Invalid request. Please use the form to submit your request.";
}

// Close the database connection
$conn->close();
?>
