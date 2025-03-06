<?php
session_start();

// Rate Limiting: Allow only one submission per 30 seconds
if (isset($_SESSION['last_submission']) && time() - $_SESSION['last_submission'] < 60*60*24) {
    header('Location: oops.html');
    exit;
}

// Google reCAPTCHA Secret Key
$recaptchaSecret = "6Lc7SRcqAAAAAJAvUW-_vuwnS2eM0xPN40X35j-p";

// Function to validate reCAPTCHA
function validateRecaptcha($recaptchaSecret, $recaptchaResponse) {
    $url = "https://www.google.com/recaptcha/api/siteverify";
    $data = [
        "secret" => $recaptchaSecret,
        "response" => $recaptchaResponse
    ];
    
    $options = [
        "http" => [
            "header" => "Content-type: application/x-www-form-urlencoded",
            "method" => "POST",
            "content" => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $responseKeys = json_decode($result, true);

    return $responseKeys["success"];
}

// Validate POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!empty($_POST['xid'])) {
        header('Location: oops.html');
        exit;
    }

    // Validate reCAPTCHA server-side
    if (!validateRecaptcha($recaptchaSecret, $_POST['g-recaptcha-response'])) {
        header('Location: oops.html');
        exit;
    }

    // Sanitize & Validate Inputs
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $phone = preg_replace('/\D/', '', $_POST['phone']);
    $countryCode = htmlspecialchars(trim($_POST['countryCode']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Validate fields
    if (empty($name) || !$email || strlen($phone) < 10 || empty($message)) {
        header('Location: oops.html');
        exit;
    }

    // Email sending logic
    $to = "contact@illforddigital.com, illforddigital@gmail.com";
    $cc = "dm.illforddigital@gmail.com, edb@illforddigital.com"; 
    $subject = "Enquiry for Digital Marketing Services - Home Page";
    $headers = "From: " . $email . "\r\n";
    $headers .= "CC: " . $cc . "\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    $email_body = "You have received a new message:\n\n" .
                  "Full Name: $name\n" .
                  "Email: $email\n" .
                  "Phone: $countryCode $phone\n" .
                  "Message:\n$message";

    if (mail($to, $subject, $email_body, $headers)) {
        // Thank-you email
        $responder_subject = "Thank you for contacting us!";
        $responder_message = "Dear $name,\n\nThank you for reaching out. We will get back to you soon.\n\nBest regards,\nThe Illford Digital Team";
        $responder_headers = "From: illforddigital@gmail.com";
        mail($email, $responder_subject, $responder_message, $responder_headers);

        // Store last submission time
        $_SESSION['last_submission'] = time();

        header('Location: alert.html');
        exit;
    } else {
        header('Location: oops.html');
        exit;
    }
}
?>
