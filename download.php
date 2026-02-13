<?php
$filename = 'curriculo_PHP_Juliano_Fernando_da_Silva.pdf';
$localPath = __DIR__ . '/assets/' . $filename;
$remoteUrl = 'https://llfl-my.sharepoint.com/personal/juliano_llfl_onmicrosoft_com/_layouts/15/onedrive.aspx?id=%2Fpersonal%2Fjuliano%5Fllfl%5Fonmicrosoft%5Fcom%2FDocuments%2FCurriculo%5FDEV%5FJULIANO%2Epdf&parent=%2Fpersonal%2Fjuliano%5Fllfl%5Fonmicrosoft%5Fcom%2FDocuments&ga=1';

function outputFileHeaders($filename, $length = null) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    if ($length !== null) header('Content-Length: ' . $length);
    header('Cache-Control: private, max-age=0, must-revalidate');
}

// Marca via cookie que o download foi iniciado/sucesso
setcookie('fileDownloaded', '1', time() + 60, '/');

if (file_exists($localPath)) {
    outputFileHeaders($filename, filesize($localPath));
    readfile($localPath);
    exit;
}

// Fallback remoto (pode requerer autenticação do provedor)
$content = false;
$code = 0;
if (function_exists('curl_init')) {
    $ch = curl_init($remoteUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $content = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
} else {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: Mozilla/5.0\r\n",
            'ignore_errors' => true
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    $content = @file_get_contents($remoteUrl, false, $context);
    $code = $content !== false ? 200 : 0;
}

if ($code === 200 && $content !== false) {
    outputFileHeaders($filename, strlen($content));
    echo $content;
    exit;
}

http_response_code(502);
header('Content-Type: text/plain; charset=utf-8');
echo 'Falha ao obter o arquivo.';
exit;
