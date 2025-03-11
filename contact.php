<?php

session_start();
include 'config.php';


require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$messages = []; 

if (isset($_POST['send'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $number = mysqli_real_escape_string($conn, $_POST['number']);
    $msg = mysqli_real_escape_string($conn, $_POST['message']);

    $query = "SELECT * FROM `message` WHERE name = ? AND email = ? AND number = ? AND message = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $number, $msg);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $messages[] = 'Message already sent!';
    } else {
       
        $insert_query = "INSERT INTO `message` (user_id, name, email, number, message) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "issss", $user_id, $name, $email, $number, $msg);
        mysqli_stmt_execute($stmt);

        
        $mail = new PHPMailer(true);
        try {
         
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sumitshukl2024@gmail.com';

           
            $mail->Password = $smtp_password; 

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            
            $mail->setFrom('sumitshukl2024@gmail.com', 'sumit shukla');
            $mail->addAddress($email, $name);

           
            $mail->isHTML(true);
            $mail->Subject = 'Thank you for contacting us!';
            $mail->Body = "<h3>Hi $name,</h3><p>Thank you for reaching out! We have received your message and will get back to you soon.</p><p><strong>Your Message:</strong> $msg</p>";

            $mail->send();
            $messages[] = 'Message sent successfully, and email notification sent!';
        } catch (Exception $e) {
            $messages[] = "Message stored, but email not sent. Error: " . htmlspecialchars($mail->ErrorInfo);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="heading">
    <h3>Contact Us</h3>
    <p><a href="home.php">Home</a> / Contact</p>
</div>

<section class="contact">
    <form action="" method="post">
        <h3>Say Something!</h3>
        <?php 
        if (!empty($messages)) {
            foreach ($messages as $msg) {
                echo "<p class='message'>$msg</p>";
            }
        }
        ?>
        <input type="text" name="name" required placeholder="Enter your name" class="box">
        <input type="email" name="email" required placeholder="Enter your email" class="box">
        <input type="number" name="number" required placeholder="Enter your number" class="box">
        <textarea name="message" class="box" placeholder="Enter your message" cols="30" rows="10"></textarea>
        <input type="submit" value="Send Message" name="send" class="btn">
    </form>
</section>

<?php include 'footer.php'; ?>

<script src="script.js"></script>

</body>
</html>
