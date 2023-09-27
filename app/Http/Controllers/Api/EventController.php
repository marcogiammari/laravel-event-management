<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // query comincia a costruire la query
        $query = Event::query();
        // array delle relazioni che l'utente può richiedere
        $relations = ['user', 'attendees', 'attendees.user'];

        foreach ($relations as $relation) {
            $query->when(
                $this->shouldIncludeRelation($relation),
                fn ($q) => $q->with($relation)
            );
        }

        // Laravel intuisce che deve inviare una response->json()
        // return Event::all();

        // raggruppa i risultati nella proprietà "data"
        return EventResource::collection(
            $query->latest()->paginate());
    }

    protected function shouldIncludeRelation(string $relation): bool 
    {
        // non è obbligatorio Request $request come parametro per accedere alla request
        // si può usare la funzione request()
        $include = request()->query('include');

        if (!$include) {
            return false;
        }

        /* array_map esegue la funzione trim, per eliminare ogni spazio vuoto, per ogni elemento ritornato dalla funzione explode, che riceve una stringa e genera un array in base a un separatore (la virgola)
        */
        $relations = array_map('trim', explode(',', $include));

        // controlla se relation è nell'array relations
        return in_array($relation, $relations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $event = Event::create([

            // spread operator: per aggiungere manualmente lo user_id 1 (WIP)
            ...$request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time'
            ]),
            'user_id' => 1
        ]);

        return $event;
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->load('user');
        $event->load('attendees');
        return new EventResource($event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $event->update(
            $request->validate([
                // sometimes attiva le regole string e max solo se 
                // il valore è presente nell'input
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'sometimes|required|date',
                'end_time' => 'sometimes|required|date|after:start_time'
            ])
        );

        return new EventResource($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response(status: 204);
    }
}
