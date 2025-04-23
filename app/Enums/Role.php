<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Customer = 'customer';
    
    /**
     * Get the role user values
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
     * Get the role user for select input
     */
    public static function select(): array
    {
        return array_column(self::cases(), 'name', 'value');
    }
}
