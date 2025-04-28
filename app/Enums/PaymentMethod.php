<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Qris = 'qris';
    case Transfer = 'transfer';

    /**
     * Get the payment method values
     * 
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            fn(self $paymentMethod): string => $paymentMethod->value,
            self::cases()
        );
    }

    /**
     * Get the payment method for select input
     * 
     * @return array<string, string>
     */
    public static function select(): array
    {
        return array_reduce(
            self::cases(),
            function (array $carry, self $method): array {
                $carry[$method->value] = __("messages.payment_method.$method->value");
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
            self::Cash => __('messages.payment_method.cash'),
            self::Qris => __('messages.payment_method.qris'),
            self::Transfer => __('messages.payment_method.transfer'),
        };
    }
}
