<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Menu;
use TCG\Voyager\Models\MenuItem;

class ElectoralMenuSeeder extends Seeder
{
    protected $tree = [
        [
            'title'      => 'Personas',
            'order'      => 2,
            'icon_class' => 'voyager-person',
            'route'      => 'admin.people.index',
            'url'        => '',
        ],
        [
            'title'      => 'Catálogos Electorales',
            'order'      => 3,
            'icon_class' => 'fa-solid fa-folder-tree',
            'route'      => null,
            'url'        => '',
            'children'   => [
                ['title' => 'Cargos',                     'route' => 'admin.cargos.index',                    'icon_class' => 'fa-solid fa-briefcase',           'order' => 1],
                ['title' => 'Organizaciones Políticas',  'route' => 'admin.organizaciones_politicas.index', 'icon_class' => 'fa-solid fa-flag',                'order' => 2],
                ['title' => 'Geografías',                  'route' => 'admin.geografias.index',                 'icon_class' => 'fa-solid fa-map',                 'order' => 3],
                ['title' => 'Departamentos',               'route' => null,                                  'icon_class' => 'fa-solid fa-map-location-dot',    'order' => 4],
                ['title' => 'Provincias',                  'route' => null,                                  'icon_class' => 'fa-solid fa-map-pin',            'order' => 5],
                ['title' => 'Municipios',                  'route' => null,                                  'icon_class' => 'fa-solid fa-city',                'order' => 6],
            ],
        ],
        [
            'title'      => 'Infraestructura Electoral',
            'order'      => 4,
            'icon_class' => 'fa-solid fa-building-columns',
            'route'      => null,
            'url'        => '',
            'children'   => [
                ['title' => 'Recintos', 'route' => 'admin.recintos.index', 'icon_class' => 'fa-solid fa-location-dot', 'order' => 1],
                ['title' => 'Mesas',    'route' => 'admin.mesas.index',     'icon_class' => 'fa-solid fa-table',        'order' => 2],
            ],
        ],
        [
            'title'      => 'Candidatos',
            'order'      => 5,
            'icon_class' => 'voyager-people',
            'route'      => 'admin.candidatos.index',
            'url'        => '',
        ],
        [
            'title'      => 'Escrutinio',
            'order'      => 6,
            'icon_class' => 'fa-solid fa-boxes-packing',
            'route'      => null,
            'url'        => '',
            'children'   => [
                ['title' => 'Actas de Escrutinio', 'route' => null, 'icon_class' => 'fa-solid fa-file-lines',          'order' => 1],
                ['title' => 'Resumen de Votos',    'route' => null, 'icon_class' => 'fa-solid fa-chart-pie',           'order' => 2],
                ['title' => 'Auditoría',           'route' => null, 'icon_class' => 'fa-solid fa-clipboard-check',       'order' => 3],
            ],
        ],
        [
            'title'      => 'Reportes',
            'order'      => 7,
            'icon_class' => 'voyager-bar-chart',
            'route'      => null,
            'url'        => '',
        ],
    ];

    public function run()
    {
        $menu = Menu::where('name', 'admin')->firstOrFail();

        foreach ($this->tree as $root) {
            $this->createRecursive($menu, $root);
        }
    }

    private function createRecursive($menu, $item, $parentId = null)
    {
        $data = [
            'menu_id'   => $menu->id,
            'parent_id' => $parentId,
            'title'     => $item['title'],
            'url'       => $item['url'] ?? '',
            'route'     => $item['route'] ?? null,
            'parameters'=> '',
            'target'    => '_self',
            'icon_class'=> $item['icon_class'],
            'color'     => null,
            'order'     => $item['order'],
        ];

        $dbItem = MenuItem::firstOrCreate(
            ['menu_id' => $menu->id, 'title' => $item['title'], 'parent_id' => $parentId],
            $data
        );

        foreach ($item['children'] ?? [] as $child) {
            $this->createRecursive($menu, $child, $dbItem->id);
        }
    }
}
