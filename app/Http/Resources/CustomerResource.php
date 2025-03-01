<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'password'=> $this->password,
            'email'   => $this->email,
            'phone_number' => $this->phone_number,
            'verification_code' => $this->verification_code,
            'date_of_birth' => $this->date_of_birth,
            'city' => $this->city,
            'address' => $this->address,
            'photo' => $this->photo,
        ];
    }
}