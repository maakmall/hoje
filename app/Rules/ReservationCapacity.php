<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReservationCapacity implements ValidationRule, DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $location = DB::table('locations')->find($this->data['data']['location_id']);
        if (!$location) {
            $fail('Lokasi tidak ditemukan.');
        }

        $datetime = Carbon::parse($this->data['data']['datetime']);
        $totalBooked = DB::table('reservations')
            ->whereDate('datetime', $datetime)
            ->where('location_id', $this->data['data']['location_id'])
            ->sum('number_of_people');

        $availableCapacity = $location->capacity - $totalBooked;

        if ($value > $availableCapacity) {
            $fail("Kapasitas penuh. Tersisa $availableCapacity kursi.");
        }
    }
}
