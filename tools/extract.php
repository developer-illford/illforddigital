<?php
header('Content-Type: application/json'); // Ensure the response is always JSON
ini_set('display_errors', 0); // Suppress warnings and notices in the response
ini_set('log_errors', 1); // Log errors to a file
ini_set('error_log', __DIR__ . '/error_log.txt'); // Error log file location

require 'vendor/autoload.php'; // Load PhpSpreadsheet

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

if (!isset($_POST['url']) || empty($_POST['url'])) {
    echo json_encode(['success' => false, 'error' => 'URL is required.']);
    exit;
}

$url = filter_var($_POST['url'], FILTER_VALIDATE_URL);
if (!$url) {
    echo json_encode(['success' => false, 'error' => 'Invalid URL provided.']);
    exit;
}

// Fetch the webpage content
$htmlContent = @file_get_contents($url);
if (!$htmlContent) {
    echo json_encode(['success' => false, 'error' => 'Failed to fetch the webpage.']);
    exit;
}

// Parse HTML content using DOMDocument
$dom = new DOMDocument();
@$dom->loadHTML($htmlContent);
$tags = ['h1', 'h2', 'h3', 'p', 'a'];
$data = [];

foreach ($tags as $tag) {
    $elements = $dom->getElementsByTagName($tag);
    foreach ($elements as $element) {
        $text = trim($element->textContent);
        if (!empty($text)) {
            $data[] = ['Tag' => $tag, 'Content' => $text];
        }
    }
}

if (empty($data)) {
    echo json_encode(['success' => false, 'error' => 'No content found on the webpage.']);
    exit;
}

// Create Excel file
$filename = 'extracted_content_' . time() . '.xlsx';
$filepath = __DIR__ . '/' . $filename;

try {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Add headers
    $sheet->setCellValue('A1', 'Tag');
    $sheet->setCellValue('B1', 'Content');

    // Add data
    $row = 2;
    foreach ($data as $item) {
        $sheet->setCellValue("A$row", $item['Tag']);
        $sheet->setCellValue("B$row", $item['Content']);
        $row++;
    }

    // Save the file
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save($filepath);

    // Return success with the file URL
    echo json_encode(['success' => true, 'fileUrl' => $filename]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to create Excel file.']);
}
