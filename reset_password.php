<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS Hire - Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Josefin Sans', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: #add8e6;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            margin: 20px;
        }

        .content {
            text-align: center;
            margin-bottom: 20px;
        }

        .content h2 {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        form {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        form label {
            align-self: flex-start;
            margin-bottom: 5px;
            color: #333;
        }

        form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        form button {
            width: 100%;
            background-color: #000;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }

        form button:hover {
            background-color: #444;
        }

        .terms {
            font-size: 0.875rem;
            color: #999;
            margin-top: 10px;
            text-align: center;
        }

        #error-msg, #success-msg {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <h2>Reset Your Password</h2>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">

            <!-- Email input field -->
            <label for="email">Enter Your Email</label>
            <input type="email" id="email" name="email" required>

            <!-- Send OTP Button -->
            <button type="submit" name="action" value="generate_otp">Send Link (Generate OTP)</button>

            <!-- OTP input field -->
            <div>
                <label for="otp">Enter OTP</label>
                <input type="text" id="otp" name="otp" placeholder="Enter the OTP" required>
            </div>

            <!-- New Password Fields -->
            <label for="new-password">Enter New Password</label>
            <input type="password" id="new-password" name="new_password" required>

            <label for="confirm-password">Re-Enter Password</label>
            <input type="password" id="confirm-password" name="confirm_password" required>

            <button type="submit" name="action" value="reset_password">Confirm</button>
        </form>
        <p class="terms">By continuing, you agree to our Terms of Service and Privacy Policy</p>
        
        <p id="error-msg">
            <?php if(isset($error_message)) echo $error_message; ?>
        </p>
        <p id="success-msg">
            <?php if(isset($success_message)) echo $success_message; ?>
        </p>
    </div>

    <?php
    session_start();

    // Include database configuration (change these values accordingly)
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

    // Handle form submission for OTP generation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'generate_otp') {
            $email = sanitize($_POST['email']);

            // Generate OTP and save in session
            $generated_otp = rand(100000, 999999); // Generate a 6-digit OTP
            $_SESSION['generated_otp'] = $generated_otp; // Store OTP in session

            // Here, you can add the logic to send the OTP to the user's email address

            echo "<script>alert('Your OTP is: " . $generated_otp . "');</script>";
        } elseif ($_POST['action'] === 'reset_password') {
            $email = sanitize($_POST['email']);
            $otp = sanitize($_POST['otp']);
            $new_password = sanitize($_POST['new_password']);
            $confirm_password = sanitize($_POST['confirm_password']);

            // Check if OTP matches the one stored in session
            if (!isset($_SESSION['generated_otp']) || $_SESSION['generated_otp'] !== $otp) {
                $error_message = "Invalid OTP.";
            } elseif ($new_password !== $confirm_password) {
                $error_message = "Passwords do not match.";
            } else {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update password in the database
                try {
                    $pdo = new PDO($dsn, $username, $password, $options);
                    $sql = "UPDATE users SET password = :password WHERE email = :email";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['password' => $hashed_password, 'email' => $email]);

                    if ($stmt->rowCount() > 0) {
                        $success_message = "Password successfully updated.";
                        unset($_SESSION['generated_otp']); // Clear OTP after use
                    } else {
                        $error_message = "No account found for this email.";
                    }
                } catch (PDOException $e) {
                    $error_message = "Database error: " . $e->getMessage();
                }
            }
        }
    }
    ?>
</body>
</html>
