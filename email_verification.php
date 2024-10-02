<?php
session_start();

// Database connection details
$dsn = 'mysql:host=localhost;dbname=nexushire_db';
$username = 'root'; // Replace with your DB username
$password = ''; // Replace with your DB password
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

// Function to sanitize input data
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to send verification email
function sendVerificationEmail($email, $token) {
    $subject = "Email Verification";
    $message = "Please verify your email by clicking the link: http://yourdomain.com/verify.php?token=$token";
    $headers = "From: no-reply@yourdomain.com";
    mail($email, $subject, $message, $headers);
}

// Handle form submission for registration (or password reset in your case)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $token = bin2hex(random_bytes(16)); // Generate a verification token

    try {
        $pdo = new PDO($dsn, $username, $password, $options);
        
        // Insert or update the user record with verification token
        $sql = "INSERT INTO users (email, verification_token) VALUES (:email, :token)
                ON DUPLICATE KEY UPDATE verification_token = :token";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email, 'token' => $token]);

        // Send the verification email
        sendVerificationEmail($email, $token);
        
        echo "Verification email sent. Please check your inbox.";
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
}
?>
