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
}
