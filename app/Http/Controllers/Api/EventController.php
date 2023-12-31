<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    use CanLoadRelationships;

    private array $relations = ['user', 'attendees', 'attendees.user'];

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);

        // ogni metodo della policy relativa viene chiamata prima di ogni action
        /* 
        Se il controller è un RESOURCE CONTROLLER e se i metodi del controller combaciano con i nomi dei metodi della policy, Laravel invocherà automaticamente i metodi della policy prima di eseguire qualsiasi action del controller. Controlla sempre nella documentazione le corrispondenze tra i nomi dei metodi.
        */
        // il secondo argomento è il parametro della rotta {event}
        $this->authorizeResource(Event::class, 'event');

        // throttling -> setta il rate limiting a 60 request in 1 minuto per ogni action
        // il throttling si usa soprattutto per le heavy actions (create/update/delete), a maggior ragione se sono pubbliche
        // $this->middleware('throttle:60,1')
        //     ->only(['store', 'update', 'destroy']);        
            
        // modo alternativo: settare requests/minuti in RouteServiceProvider e richiamare il nome del RateLimiter qui
        $this->middleware('throttle:api')
            ->only(['store', 'update', 'destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // array delle relazioni che l'utente può richiedere

        // query comincia a costruire la query
        $query = $this->loadRelationships(Event::query());

        // Laravel intuisce che deve inviare una response->json()
        // return Event::all();

        // raggruppa i risultati nella proprietà "data"
        return EventResource::collection(
            $query->latest()->paginate()
        );
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
            'user_id' => $request->user()->id
        ]);

        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        // se non passa il gate update-event -> 403 forbidden
        // if (Gate::denies('update-event', $event)) {
        //     abort(403, 'You are not authorized to update this event');
        // }

        // fa la stessa identica cosa del blocco commentato sopra
        // $this->authorize('update-event', $event);

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

        return new EventResource($this->loadRelationships($event));
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
