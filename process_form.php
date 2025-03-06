<?php
session_start();

// Rate Limiting: Limit submissions to one every 30 seconds
if (isset($_SESSION['last_submission']) && time() - $_SESSION['last_submission'] < 60*60*24) {
    header('Location: oops.html');
    exit;
}

// Your Google reCAPTCHA Secret Key (replace with your own)
$recaptchaSecret = "6Lc7SRcqAAAAAJAvUW-_vuwnS2eM0xPN40X35j-p";

// Function to validate reCAPTCHA via Google API
function validateRecaptcha($secret, $responseToken) {
    $url = "https://www.google.com/recaptcha/api/siteverify";
    $data = [
        "secret" => $secret,
        "response" => $responseToken
    ];

    $options = [
        "http" => [
            "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
            "method"  => "POST",
            "content" => http_build_query($data)
        ]
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $resultJson = json_decode($result, true);
    return $resultJson["success"];
}

// Process the form submission if method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check idx field (should be empty)
    if (!empty($_POST['idx'])) {
        header('Location: oops.html');
        exit;
    }
    
    // Validate reCAPTCHA (server-side)
    if (!isset($_POST['g-recaptcha-response']) || !validateRecaptcha($recaptchaSecret, $_POST['g-recaptcha-response'])) {
        header('Location: oops.html');
        exit;
    }

    // Sanitize inputs
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $phone = preg_replace('/\D/', '', $_POST['phone']);
    $countryCode = htmlspecialchars(trim($_POST['countryCode']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Validate required fields
    if (empty($name) || !$email || strlen($phone) !== 10 || empty($message)) {
        header('Location: oops.html');
        exit;
    }

    // Validate country code (allowing plus sign and 1-3 digits)
    if (!preg_match('/^\+\d{1,3}$/', $countryCode)) {
        header('Location: oops.html');
        exit;
    }

    // Set recipient emails
    $to = "contact@illforddigital.com, illforddigital@gmail.com";
    $cc = "dm.illforddigital@gmail.com, edb@illforddigital.com";
    $subject = "Enquiry for Digital Marketing Services - contact page";

    // Prepare email headers
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "CC: $cc\r\n";

    // Construct the email body
    $email_body = "You have received a new message from:\n\n" .
                  "Full Name: $name\n" .
                  "Email: $email\n" .
                  "Mobile Number: $countryCode $phone\n" .
                  "Message:\n$message";

    // Attempt to send the email
    if (mail($to, $subject, $email_body, $headers)) {
        // Send thank-you email to the user
        $responder_subject = "Thank you for contacting us!";
        $responder_message = "Dear $name,\n\nThank you for contacting us. We have received your message and will get back to you shortly.\n\nBest regards,\nThe Illford Digital Team";
        $responder_headers = "From: illforddigital@gmail.com";
        mail($email, $responder_subject, $responder_message, $responder_headers);

        // Record submission time to enforce rate limiting
        $_SESSION['last_submission'] = time();

        header('Location: alert.html');
        exit;
    } else {
        header('Location: oops.html');
        exit;
    }
} else {
    header('Location: oops.html');
    exit;
}
?>
