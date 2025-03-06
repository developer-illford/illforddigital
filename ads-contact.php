<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize user input
    $firstname = htmlspecialchars($_POST['firstname']);
    $lastname = htmlspecialchars($_POST['lastname']);
    $email = htmlspecialchars($_POST['email']);
    $countryCode = htmlspecialchars($_POST['countryCode']);
    $phone = htmlspecialchars($_POST['phone']);
    $message = htmlspecialchars($_POST['message']);

    // Validate email and phone number formats
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Handle invalid email address
        header('Location: oops.html');
        exit;
    }

    if (!preg_match('/^\+\d{1,3}$/', $countryCode)) {
        // Handle invalid country code
        header('Location: oops.html');
        exit;
    }

    if (!preg_match('/^\d{10}$/', $phone)) {
        // Handle invalid phone number
        header('Location: oops.html');
        exit;
    }

    // Set recipient email addresses
    $to = "contact@illforddigital.com, illforddigital@gmail.com";

 // Set CC email addresses
 $cc = "dm.illforddigital@gmail.com, edb@illforddigital.com"; 

    // Set email subject
    $subject = "Enquiry for Digital Marketing Course";

    // Set email headers
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "CC: $cc\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Construct email body
    $email_body = "You have received a new message from $firstname $lastname.\n".
                  "Email address: $email\n".
                  "Mobile Number: $countryCode $phone\n".
                  "Message: $message\n";

    // Use mail() function to send the email
    if (mail($to, $subject, $email_body, $headers)) {
        // Send thank-you email to the responder
        $responder_subject = "Thank you for contacting us!";
        $responder_message = "Dear $firstname,\n\nThank you for contacting us. We have received your message and will get back to you shortly.\n\nBest regards,\nThe Illford Digital Team";

        $responder_headers = "From: illforddigital@gmail.com"; // Change this to your sender email

        mail($email, $responder_subject, $responder_message, $responder_headers);
        header('Location: dm-courses-thank-you.html');
        exit;
    } else {
        header('Location: oops.html');
        exit;
    }
}
?>
