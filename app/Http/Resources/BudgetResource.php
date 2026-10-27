<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'month' => $this->month,
            'limit_amount' => $this->limit_amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'user' => UserResource::make($this->whenLoaded('user')),
            'category' => CategoryResource::make($this->whenLoaded('category')),
        ];
    }
}
