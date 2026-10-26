<?php

$chatUrl = getenv('CHAT_URL');
if ($chatUrl === false || trim($chatUrl) === '') {
    $chatUrl = 'app.php';
}

$chatUrl = trim($chatUrl);
$chatUrlParts = parse_url($chatUrl);
$absoluteChatUrl = $chatUrlParts
    && isset($chatUrlParts['scheme'], $chatUrlParts['host'])
    && in_array(strtolower($chatUrlParts['scheme']), array('http', 'https'), true);
$relativeChatUrl = $chatUrlParts
    && !isset($chatUrlParts['scheme'])
    && !isset($chatUrlParts['host'])
    && strpos($chatUrl, '/') !== 0
    && strpos($chatUrl, '..') === false;
if (!$absoluteChatUrl && !$relativeChatUrl) {
    http_response_code(500);
    exit('La dirección del chat configurada en CHAT_URL no es válida.');
}

$isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off';
$scheme = $isHttps ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$directory = isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '';
$directory = str_replace('\\', '/', $directory);
$directory = $directory === '/' || $directory === '.' ? '' : rtrim($directory, '/');
$returnUrl = $host !== '' ? $scheme . '://' . $host . $directory . '/index.php' : '';

if ($returnUrl !== '') {
    $chatUrl .= (strpos($chatUrl, '?') === false ? '?' : '&')
        . http_build_query(array('return_url' => $returnUrl), '', '&', PHP_QUERY_RFC3986);
}

header('Location: ' . $chatUrl, true, 302);
exit;
