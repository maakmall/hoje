<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Paid = 'paid';
    case Pending = 'pending';
    case Failed = 'failed';
    
    /**
     * Get the payment status values
     * 
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            fn(self $paymentStatus): string => $paymentStatus->value,
            self::cases()
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
            self::Paid => __('messages.payment_status.paid'),
            self::Pending => __('messages.payment_status.pending'),
            self::Failed => __('messages.payment_status.failed'),
        };
    }
}
