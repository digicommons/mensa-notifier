<?php

namespace MensaNotifier;

class Notification
{
    public string $notificationBody = '';

    public function __construct(Menu $menu)
    {
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
            $this->notificationBody .= "- " . $item->name . ", " . $item->price . "€" . " "; // Add meal info
            $this->notificationBody .= $diet_emoji[$diet] . $diet_emoji[$diet_secondary] . "\r\n\r\n"; // Add ingredient emojis
        }
    }

    public function send($ntfyUrl, $ntfyId): void
    {
        file_get_contents($ntfyUrl . $ntfyId, false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' =>
                    "Content-Type: text/plain" . "\r\n" .
                    "Tags: bowl_with_spoon" . "\r\n" .
                    "Title: Heute in der Mensa:",
                'content' => $this->notificationBody
            ]
        ]));
    }
}