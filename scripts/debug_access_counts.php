<?php
require_once __DIR__ . '/../app/services/AccessLogger.php';
date_default_timezone_set('UTC');
$today = new DateTimeImmutable('today');
$from = $today->modify('-29 days')->format('Y-m-d');
$to = $today->format('Y-m-d');
$counts = AccessLogger::dailyCounts($from, $to);
echo json_encode(['from' => $from, 'to' => $to, 'counts' => $counts], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
