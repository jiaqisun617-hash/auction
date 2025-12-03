<?php
$to = "jiaqisun617@gmail.com"; 
$subject = "Test email from PHP";
$message = "If you see this, PHP mail() works!";
$headers = "From: Your Name jiaqisun617@gmail.com>";

if (mail($to, $subject, $message, $headers)) {
    echo "Mail sent successfully!";
} else {
    echo "Mail failed.";
}
?>