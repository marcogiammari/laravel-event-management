<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);

        // puoi selezionare cosa inviare nella response api
        // o aggiungere informazioni customizzate (es. il nome dello user)
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            // whenLoaded: aggiunge i dati dell'utente solo quando viene 
            // caricato il model user
            'user' => new UserResource($this->whenLoaded('user')),
            'attendees' => AttendeeResource::collection(
                $this->whenLoaded('attendees')
            )
        ];
    }
}
