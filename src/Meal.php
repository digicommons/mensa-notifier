<?php

namespace MensaNotifier;

class Meal
{
    /**
     * @param array<int, string> $diet
     */
    public function __construct(
        public string $name,
        public string $price,
        public array $diet
    )
    {
    }
}