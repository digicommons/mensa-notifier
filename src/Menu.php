<?php

namespace MensaNotifier;

class Menu
{
    /** @var Meal[] $meals  */
    public array $meals;

    /**
     * @param array<mixed> $menu
     */
    public function __construct(array $menu)
    {
        $this->meals = $this->filterInvalidMeals($menu);
    }

    /**
     * @param array<mixed> $menu
     * @return Meal[]
     */
    private function filterInvalidMeals(array $menu): array
    {
        $meals = [];

        foreach ($menu as $meal) {
            $name = $meal->beschreibung;
            if ($name === '.') continue;

            $price = $meal->preis_g;

            $labels = $meal->labels->label;

            // There are multiple dietary labels
            if (is_array($labels)) {
                $diet = array_map(fn($item) => $item['@attributes']['name'], json_decode(json_encode($labels), true));
                $meals[] = new Meal($name, $price, $diet);
            }

            // There is only one dietary label
            if ($labels instanceof \stdClass) {
                $diet = [$labels->{'@attributes'}->name];
                $meals[] = new Meal($name, $price, $diet);
            }
        }

        return $meals;
    }
}