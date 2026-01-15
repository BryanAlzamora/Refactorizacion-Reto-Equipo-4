<?php

namespace App\Http\Controllers;

use App\Models\CompetenciasTec;
use App\Models\CompetenciasTrans;

use Illuminate\Http\Request;

class CompetenciasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $competenciasTec = CompetenciasTec::all()->map(function ($c) {
            return [
                'id' => $c->id,
                'descripcion' => $c->descripcion,
                'tipo' => 'TECNICA',
            ];
        });

        $competenciasTrans = CompetenciasTrans::all()->map(function ($c) {
            return [
                'id' => $c->id,
                'descripcion' => $c->descripcion,
                'tipo' => 'TRANSVERSAL',
            ];
        });

        return response()->json(
            $competenciasTec
                ->merge($competenciasTrans)
                ->values()
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetenciasTec $competencias)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompetenciasTec $competencias)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CompetenciasTec $competencias)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetenciasTec $competencias)
    {
        //
    }
}
