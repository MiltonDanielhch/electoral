<?php

namespace App\Observers;

use App\Models\Candidato;
use Illuminate\Support\Facades\Storage;

/**
 * Observer de Limpieza para Candidatos
 * 
 * MEJORA DE SEGURIDAD: Asegura que cuando un candidato se elimina 
 * permanentemente (forceDeleted), su imagen asociada también se 
 * elimine del almacenamiento, evitando archivos "zombie".
 */
class CandidatoObserver
{
    /**
     * Handle the Candidato "forceDeleted" event.
     * Elimina la imagen del almacenamiento cuando el candidato se borra permanentemente.
     *
     * @param  \App\Models\Candidato  $candidato
     * @return void
     */
    public function forceDeleted(Candidato $candidato)
    {
        if ($candidato->imagen) {
            Storage::disk('public')->delete($candidato->imagen);
        }
    }

    /**
     * Handle the Candidato "deleted" event.
     * Opcional: También podríamos manejar el soft delete si es necesario.
     *
     * @param  \App\Models\Candidato  $candidato
     * @return void
     */
    public function deleted(Candidato $candidato)
    {
        // En soft delete no eliminamos la imagen, 
        // solo cuando se hace forceDelete
    }
}