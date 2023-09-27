<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

trait CanLoadRelationships
{
    public function loadRelationships(
        Model|Builder|QueryBuilder $for,
        ?array $relations = null
    ): Model|Builder|QueryBuilder
    {
        /* se il parametro relations è null (il punto interrogativo lo rende nullable), cerca di ricavarlo dalla classe in cui il trait è usato, se anche là è null, assegna un array vuoto e il for successivo non sarà eseguito */
        $relations = $relations ?? $this->relations ?? [];
        foreach ($relations as $relation) {
            $for->when(
                $this->shouldIncludeRelation($relation),
                fn ($q) => $for instanceof Model ? $for->load($relation) : $q->with($relation)
            );
        }

        return $for;
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
}