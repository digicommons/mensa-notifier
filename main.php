<?php

use MensaNotifier\Menu;

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$ntfy_url = $_ENV['NTFY_SERVER_URL'];
$ntfy_id = $_ENV['NTFY_ID'];
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
    file_get_contents($ntfy_url . $ntfy_id, false, stream_context_create([
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
    file_get_contents($ntfy_url . $ntfy_id, false, stream_context_create([
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

$notification_body = '';
foreach ($menu->meals as $item) {
    $diet = $item->diet[0] ?? 'empty';
    $diet_secondary = $item->diet[1] ?? 'empty';
    $diet_emoji = [
        'empty' => '',
        'vegan' => '🌿',
        'vegetarisch' => '	🧀',
        'Fisch' => '🐟',
        'Geflügel' => '🐔',
        'Lamm' => '🐑',
        'Rind' => '🐄',
        'Schwein' => '🐷',
        'Wild' => '🦌',
        'Knoblauch' => '🧄',
        'Alkohol' => '🍸',
        'regional' => '🚜'
    ];
    $notification_body .= "- " . $item->name . ", " . $item->price . "€" . " "; // Add meal info
    $notification_body .= $diet_emoji[$diet] . $diet_emoji[$diet_secondary] . "\r\n\r\n"; // Add ingredient emojis
}

file_get_contents($ntfy_url . $ntfy_id, false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' =>
            "Content-Type: text/plain" . "\r\n" .
            "Tags: bowl_with_spoon" . "\r\n" .
            "Title: Heute in der Mensa:",
        'content' => $notification_body
    ]
]));
