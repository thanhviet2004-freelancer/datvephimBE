<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'customer_name' => $this->customer_name,
            'total' => $this->total,
            'payment_status' => 'pay_at_counter',
            'showtime' => $this->showtime,
            'seats' => $this->seats->pluck('seat')->values(),
        ];
    }
}
