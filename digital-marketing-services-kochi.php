<?php
session_start();

// Function to output an error alert and exit
function showError($message) {
    echo "<html><head><script>alert('{$message}'); window.history.back();</script></head><body></body></html>";
    exit;
}

// RATE LIMITING: Allow one submission every 60 seconds per session.
if (isset($_SESSION['last_submission']) && time() - $_SESSION['last_submission'] < 60*60*24) {
    showError("Please wait a moment before submitting again.");
}

// --- RECAPTCHA VALIDATION ---
// Replace with your actual secret key.
$recaptchaSecret = "6Lc7SRcqAAAAAJAvUW-_vuwnS2eM0xPN40X35j-p";
if (!isset($_POST['g-recaptcha-response'])) {
    showError("reCAPTCHA response missing.");
}

function validateRecaptcha($secret, $token) {
    $url = "https://www.google.com/recaptcha/api/siteverify";
    $data = ['secret' => $secret, 'response' => $token];
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $resultJson = json_decode($result, true);
    return isset($resultJson['success']) && $resultJson['success'] === true;
}

if (!validateRecaptcha($recaptchaSecret, $_POST['g-recaptcha-response'])) {
    showError("reCAPTCHA validation failed. Please try again.");
}

// --- HONEYPOT VALIDATION ---
// Include a hidden field in your HTML form (e.g., <input type="text" name="honeypot" style="display:none;">)
if (!empty($_POST['honeypot'])) {
    showError("Spam detected.");
}

// --- SANITIZE AND VALIDATE INPUTS ---
$name     = htmlspecialchars(trim($_POST['name']));
$email    = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
$country  = htmlspecialchars(trim($_POST['country']));
$phone    = preg_replace('/\D/', '', $_POST['phone']); // Remove non-digit characters
$service  = htmlspecialchars(trim($_POST['service']));
$scale    = htmlspecialchars(trim($_POST['scale']));
$goal     = htmlspecialchars(trim($_POST['goal']));
$startDate= htmlspecialchars(trim($_POST['startDate']));
$message  = htmlspecialchars(trim($_POST['message']));

if (!$email) {
    showError("Invalid email address.");
}

if (!preg_match('/^\d{10}$/', $phone)) {
    showError("Invalid phone number. Please enter a 10-digit number.");
}

// --- PREPARE EMAIL ---
$to = "contact@illforddigital.com, illforddigital@gmail.com";
$cc = "dm.illforddigital@gmail.com, edb@illforddigital.com";
$subject = "Enquiry for Digital Marketing Services";

// Construct the email body
$email_body = "You have received a new message from $name.\n" .
    "Email address: $email\n" .
    "Mobile Number: $phone\n" .
    "Nationality: $country\n\n" .
    "Questionnaire:\n" .
    "Q) Which service do you want?\nAns) $service\n\n" .
    "Q) What is the scope or scale of your project?\nAns) $scale\n\n" .
    "Q) What is your primary goal for seeking our services?\nAns) $goal\n\n" .
    "Q) When do you aim to start this project?\nAns) $startDate\n\n" .
    "Responder's Message: $message\n";

// Set email headers
$headers = "From: $email\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "CC: $cc\r\n";

// --- SEND EMAIL ---
if (mail($to, $subject, $email_body, $headers)) {
    // Send thank-you email to the responder
    $responder_subject = "Thank you for contacting us!";
    $responder_message = "Dear $name,\n\nThank you for contacting us. We have received your message and will get back to you shortly.\n\nBest regards,\nThe Illford Digital Team";
    $responder_headers = "From: illforddigital@gmail.com"; // Change to your sender email
    mail($email, $responder_subject, $responder_message, $responder_headers);
    
    // Store submission time for rate limiting.
    $_SESSION['last_submission'] = time();
    
    // Redirect to the thank-you page.
    header('Location: dm-services-thank-you.html');
    exit;
} else {
    showError("Error sending email. Please try again later.");
}
?>
