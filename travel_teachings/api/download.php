<?php
/**
 * api/download.php — Secure file download with stats tracking
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$file     = Security::get('file');
$noteName = Security::get('name', $file);

if (empty($file)) {
    http_response_code(400);
    die('Invalid request.');
}

$path = Notes::getNotePath($file);
if (!$path) {
    http_response_code(404);
    die('File not found.');
}

// Track download
Stats::recordDownload($file, $noteName);

// Serve file
$filename = $noteName ?: pathinfo($file, PATHINFO_FILENAME);
$filename = preg_replace('/[^a-zA-Z0-9 ._-]/', '', $filename);
if (!str_ends_with(strtolower($filename), '.pdf')) $filename .= '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('X-Content-Type-Options: nosniff');

readfile($path);
exit;
