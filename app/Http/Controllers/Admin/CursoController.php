<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CursoController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Cursos/Index', [
            'cursos' => []
        ]);
    }
}
