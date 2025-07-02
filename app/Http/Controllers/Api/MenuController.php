<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    /**
     * Obtener menú dinámico según el rol del usuario
     */
    public function obtener(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([]);
        }

        // Obtener el tipo de participante del usuario
        $tipoParticipante = $user->tipo_participante ?? 'PARTICIPANTE';

        // Buscar menús activos que el usuario puede ver
        $menuItems = MenuItem::where('activo', true)
            ->whereRaw("FIND_IN_SET(?, roles_permitidos) > 0 OR FIND_IN_SET('ALL', roles_permitidos) > 0", [$tipoParticipante])
            ->orderBy('orden')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nombre' => $item->nombre,
                    'icono' => $item->icono,
                    'ruta' => $item->ruta,
                    'descripcion' => $item->descripcion,
                    'orden' => $item->orden,
                    'es_activo' => $item->activo,
                    'parent_id' => $item->parent_id,
                    'roles_permitidos' => $item->roles_permitidos,
                ];
            });

        return response()->json($menuItems);
    }

    /**
     * Crear estructura jerárquica del menú
     */
    private function buildMenuTree($items, $parentId = null)
    {
        $tree = [];

        foreach ($items as $item) {
            if ($item->parent_id == $parentId) {
                $children = $this->buildMenuTree($items, $item->id);
                if (!empty($children)) {
                    $item->children = $children;
                }
                $tree[] = $item;
            }
        }

        return $tree;
    }
}
