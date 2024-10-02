<?php
// Database connection details
$servername = "localhost";
$username = "root"; // Default username for XAMPP
$password = ""; // Default password for XAMPP
$dbname = "cococomp"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form data
$firstName = $_POST['first-name'];
$lastName = $_POST['last-name'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

// Check if the email already exists
$sql_check = "SELECT id FROM users WHERE email = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $email);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    echo "Account already exists with this email.";
} else {
    // Insert data into the database
    $sql_insert = "INSERT INTO users (first_name, last_name, phone, email, password) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("sssss", $firstName, $lastName, $phone, $email, $password);

    if ($stmt_insert->execute()) {
        // Redirect to the login page after successful signup
        header("Location: login.html");
        exit(); // Ensure no further code is executed after the redirection
    } else {
        echo "Error: " . $sql_insert . "<br>" . $conn->error;
    }

    $stmt_insert->close();
}

$stmt_check->close();
$conn->close();
?>
