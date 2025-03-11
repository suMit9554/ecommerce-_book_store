<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);


include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
    exit();
}

if (isset($_POST['order_btn'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $number = isset($_POST['number']) ? $_POST['number'] : '';
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $method = mysqli_real_escape_string($conn, $_POST['method']);

  
    $flat = isset($_POST['flat']) ? $_POST['flat'] : 'N/A';
    $street = isset($_POST['street']) ? $_POST['street'] : 'N/A';
    $city = isset($_POST['city']) ? $_POST['city'] : 'N/A';
    $state = isset($_POST['state']) ? $_POST['state'] : 'N/A';
    $country = isset($_POST['country']) ? $_POST['country'] : 'N/A';
    $pin_code = isset($_POST['pin_code']) ? $_POST['pin_code'] : 'N/A';

    $address = mysqli_real_escape_string($conn, "Flat No. $flat, $street, $city, $state, $country - $pin_code");
    $placed_on = date('d-M-Y');

    $cart_total = 0;
    $cart_products = [];

    $cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('Query failed');
    if (mysqli_num_rows($cart_query) > 0) {
        while ($cart_item = mysqli_fetch_assoc($cart_query)) {
            $cart_products[] = $cart_item['name'] . ' (' . $cart_item['quantity'] . ') ';
            $sub_total = ($cart_item['price'] * $cart_item['quantity']);
            $cart_total += $sub_total;
        }
    }

    if ($cart_total == 0) {
        $message[] = 'Your cart is empty';
    } else {
        $total_products = implode(', ', $cart_products);

        // Insert order
        mysqli_query($conn, "INSERT INTO `orders` (user_id, name, number, email, method, address, total_products, total_price, placed_on) 
                             VALUES ('$user_id', '$name', '$number', '$email', '$method', '$address', '$total_products', '$cart_total', '$placed_on')")
                             or die('Query failed');

        // Clear cart
        mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('Query failed');

        // Send order email
        sendOrderEmail($email, $name, $total_products, $cart_total, $placed_on, $method, $address);

        $message[] = 'Order placed successfully! Check your email for confirmation.';
    }
}

// Email Function
function sendOrderEmail($email, $name, $products, $total, $date, $method, $address) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'sumitshukl2024@gmail.com'; 
        $mail->Password = 'byeo huiu urmp oeya'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('sumitshukl2024@gmail.com', 'book store');
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = 'Order Confirmation - Your Store';
        $mail->Body = "
            <h2>Thank You for Your Order, $name!</h2>
            <p><strong>Order Date:</strong> $date</p>
            <p><strong>Payment Method:</strong> $method</p>
            <p><strong>Delivery Address:</strong> $address</p>
            <h3>Order Details:</h3>
            <p>$products</p>
            <p><strong>Total Amount:</strong> $$total</p>
            <p>We will notify you when your order is shipped.</p>
            <br>
            <p>Thanks for shopping with us!</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout</title>
   <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="heading">
   <h3>Checkout</h3>
   <p> <a href="home.php">Home</a> / Checkout </p>
</div>

<section class="display-order">
   <?php  
      $grand_total = 0;
      $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('Query failed');
      if(mysqli_num_rows($select_cart) > 0){
         while($fetch_cart = mysqli_fetch_assoc($select_cart)){
            $total_price = ($fetch_cart['price'] * $fetch_cart['quantity']);
            $grand_total += $total_price;
   ?>
   <p> <?php echo $fetch_cart['name']; ?> <span>(<?php echo '$'.$fetch_cart['price'].' x '. $fetch_cart['quantity']; ?>)</span> </p>
   <?php
      }
   } else {
      echo '<p class="empty">Your cart is empty</p>';
   }
   ?>
   <div class="grand-total"> Grand Total: <span>$<?php echo $grand_total; ?></span> </div>
</section>

<section class="checkout">
   <form action="" method="post">
      <h3>Place Your Order</h3>
      <div class="flex">
         <div class="inputBox">
            <span>Your Name :</span>
            <input type="text" name="name" required placeholder="Enter your name">
         </div>
         <div class="inputBox">
            <span>Your Number :</span>
            <input type="number" name="number" required placeholder="Enter your number">
         </div>
         <div class="inputBox">
            <span>Your Email :</span>
            <input type="email" name="email" required placeholder="Enter your email">
         </div>
         <div class="inputBox">
            <span>Payment Method :</span>
            <select name="method">
               <option value="cash on delivery">Cash on Delivery</option>
               <option value="credit card">Credit Card</option>
               <option value="paypal">PayPal</option>
               <option value="paytm">Paytm</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Flat No. :</span>
            <input type="text" name="flat" required placeholder="Enter flat no.">
         </div>
         <div class="inputBox">
            <span>Street :</span>
            <input type="text" name="street" required placeholder="Enter street">
         </div>
         <div class="inputBox">
            <span>City :</span>
            <input type="text" name="city" required placeholder="Enter city">
         </div>
         <div class="inputBox">
            <span>State :</span>
            <input type="text" name="state" required placeholder="Enter state">
         </div>
         <div class="inputBox">
            <span>Country :</span>
            <input type="text" name="country" required placeholder="Enter country">
         </div>
         <div class="inputBox">
            <span>Pin Code :</span>
            <input type="number" name="pin_code" required placeholder="Enter pin code">
         </div>
      </div>
      <input type="submit" value="Order Now" class="btn" name="order_btn">
   </form>
</section>

<?php include 'footer.php'; ?>
</body>
</html>
