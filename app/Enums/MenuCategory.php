<?php

namespace App\Enums;

enum MenuCategory: string
{
    case Food = 'food';
    case Beverage = 'beverage';

    /**
     * Get the menu category values
     * 
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            fn(self $category): string => $category->value,
            self::cases()
        );
    }

    /**
     * Get the menu category for select options
     * 
     * @return array<string, string>
     */
    public static function select(): array
    {
        return array_reduce(
            self::cases(),
            function (array $carry, self $category): array {
                $carry[$category->value] = __("messages.menu_category.$category->value");
                return $carry;
            },
            []
        );
    }

    /**
     * Get the label for the menu category
     * 
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Food => __('messages.menu_category.food'),
            self::Beverage => __('messages.menu_category.beverage'),
        };
    }
}
