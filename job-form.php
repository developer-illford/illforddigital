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
$recaptchaSecret = "6Lc7SRcqAAAAAJAvUW-_vuwnS2eM0xPN40X35j-p";  // Replace with your actual secret key.
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
    return isset(json_decode($result, true)['success']) && json_decode($result, true)['success'] === true;
}

if (!validateRecaptcha($recaptchaSecret, $_POST['g-recaptcha-response'])) {
    showError("reCAPTCHA validation failed. Please try again.");
}

// --- HONEYPOT VALIDATION ---
if (!empty($_POST['honeypot'])) {
    showError("Spam detected.");
}

// --- SANITIZE AND VALIDATE INPUTS ---
$jobId         = htmlspecialchars($_POST['jobId']);
$jobTitle      = htmlspecialchars($_POST['jobTitle']);
$applicantName = htmlspecialchars(trim($_POST['applicantName']));
$email         = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
$phoneNumber   = preg_replace('/\D/', '', $_POST['phoneNumber']);
$message       = htmlspecialchars(trim($_POST['message']));

if (!$email) {
    showError("Invalid email address.");
}
if (!preg_match('/^\d{10}$/', $phoneNumber)) {
    showError("Invalid phone number. Please enter a 10-digit number.");
}

// --- FILE UPLOAD SECURITY ---
$allowedExtensions = ['pdf', 'doc', 'docx'];
$allowedMimeTypes = [
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];
$maxFileSize = 2 * 1024 * 1024; // 2 MB

if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
    showError("Error uploading file. Please try again.");
}

$resume = $_FILES['resume'];
$resumeName = $resume['name'];
$resumeTmp = $resume['tmp_name'];
$resumeSize = $resume['size'];

// Validate file size.
if ($resumeSize > $maxFileSize) {
    showError("File size exceeds the allowed limit of 2MB.");
}

// Validate file extension.
$fileExt = strtolower(pathinfo($resumeName, PATHINFO_EXTENSION));
if (!in_array($fileExt, $allowedExtensions)) {
    showError("Invalid file type. Only PDF, DOC, and DOCX files are allowed.");
}

// Validate MIME Type using finfo.
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($resumeTmp);
if (!in_array($mimeType, $allowedMimeTypes)) {
    showError("Invalid file content. Please upload a valid document.");
}

// --- PREPARE EMAIL ---
$recipients = ["career@illforddigital.com", "contact@illforddigital.com", "illforddigital@gmail.com"];
$confirmationFrom = "career@illforddigital.com";
$subject = "Job Application: $jobTitle (Job ID: $jobId)";

// Construct the email message body.
$messageBody = "Applicant Name: $applicantName\n";
$messageBody .= "Job Title: $jobTitle\n";
$messageBody .= "Job ID: $jobId\n";
$messageBody .= "Email: $email\n";
$messageBody .= "Phone Number: $phoneNumber\n";
$messageBody .= "Message:\n$message\n";

// Create a MIME boundary.
$boundary = md5(time());

// Build the email headers.
$cc = "dm.illforddigital@gmail.com, edb@illforddigital.com";
$headers = "From: $email\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

// Build the MIME email body.
$body  = "--$boundary\r\n";
$body .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
$body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$body .= $messageBody . "\r\n\r\n";

// Attach the resume file.
$fileContent = file_get_contents($resumeTmp);
$encodedFile = chunk_split(base64_encode($fileContent));

$body .= "--$boundary\r\n";
$body .= "Content-Type: application/octet-stream; name=\"$resumeName\"\r\n";
$body .= "Content-Disposition: attachment; filename=\"$resumeName\"\r\n";
$body .= "Content-Transfer-Encoding: base64\r\n\r\n";
$body .= $encodedFile . "\r\n";
$body .= "--$boundary--";

// --- SEND EMAIL TO RECIPIENTS ---
foreach ($recipients as $recipient) {
    if (!mail($recipient, $subject, $body, $headers)) {
        showError("Error sending email to $recipient.");
    }
}

// --- SEND CONFIRMATION EMAIL TO APPLICANT ---
$confirmationSubject = "Application Confirmation";
$confirmationMessage = "Dear $applicantName,\n\nThank you for submitting your job application for '$jobTitle'. We have received your application and will review it shortly.\n\nBest regards,\nThe Illford Digital Hiring Team";
$confirmationHeaders = "From: $confirmationFrom\r\nReply-To: $confirmationFrom\r\n";

if (!mail($email, $confirmationSubject, $confirmationMessage, $confirmationHeaders)) {
    showError("Error sending confirmation email to $email.");
}

// Store submission time for rate limiting.
$_SESSION['last_submission'] = time();

// On success, display a success message and redirect.
echo "<html><head><script>alert('Your application has been submitted successfully.'); window.location.href='alert.html';</script></head><body></body></html>";
exit;
?>
