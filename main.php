<?php

use MensaNotifier\Menu;
use MensaNotifier\Notification;

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$ntfyUrl = $_ENV['NTFY_SERVER_URL'];
$ntfyId = $_ENV['NTFY_ID'];
$url = $_ENV['MENSA_URL'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FAILONERROR, true);

$result = curl_exec($ch);

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
}
curl_close($ch);
if (isset($error_msg)) {
    file_get_contents($ntfyUrl . $ntfyId, false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' =>
                "Content-Type: text/plain" . "\r\n" .
                "Title: Heute in der Mensa:",
            'content' => $error_msg
        ]
    ]));
    exit;
}

$xml = simplexml_load_string($result);

if (!$xml) {
    file_get_contents($ntfyUrl . $ntfyId, false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' =>
                "Content-Type: text/plain" . "\r\n" .
                "Title: Heute in der Mensa:",
            'content' => "Could not parse today's meals :/"
        ]
    ]));
    exit;
}

$menu = new Menu(json_decode(json_encode($xml))->datum[0]->angebotnr);

$notification = new Notification($menu);
$notification->send($ntfyUrl, $ntfyId);
