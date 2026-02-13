<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $message = trim($_POST["message"]);
    
    $to = "pejmon00@gmail.com";
    $subject = "Portfolio Contact: $name";
    
    $body = "You have a new message from your portfolio site.\n\n";
    $body .= "Name: $name\n";
    $body .= "Email: $email\n\n";
    $body .= "Message:\n$message";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/plain; charset=utf-8\r\n";
    $headers .= "From: noreply@pej.me\r\n";
    $headers .= "Reply-To: $email\r\n";
    
    mail($to, $subject, $body, $headers);
    
    echo "success";
    exit;
}
?>