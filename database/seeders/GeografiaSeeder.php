<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeografiaSeeder extends Seeder
{
    public function run()
    {
        // 1. Limpieza de datos previa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('geografias')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. DEPARTAMENTO: BENI
        $beniId = DB::table('geografias')->insertGetId([
            'codigo_tse' => '08',
            'nombre' => 'Beni',
            'tipo' => 'Departamento',
            'parent_id' => null,
            'latitud' => -14.50000000,
            'longitud' => -65.50000000,
        ]);

        // 3. PROVINCIAS (Sintonizadas con códigos TSE de 4 dígitos)
        $provincias = [
            ['cod' => '0801', 'nom' => 'Cercado', 'lat' => -14.8333, 'lng' => -64.9167],
            ['cod' => '0802', 'nom' => 'Vaca Díez', 'lat' => -11.0167, 'lng' => -66.0667],
            ['cod' => '0803', 'nom' => 'José Ballivián', 'lat' => -14.5000, 'lng' => -66.5000],
            ['cod' => '0804', 'nom' => 'Yacuma', 'lat' => -13.5000, 'lng' => -65.5000],
            ['cod' => '0805', 'nom' => 'Moxos', 'lat' => -15.0833, 'lng' => -65.7500],
            ['cod' => '0806', 'nom' => 'Marbán', 'lat' => -15.6667, 'lng' => -64.3333],
            ['cod' => '0807', 'nom' => 'Mamoré', 'lat' => -13.0000, 'lng' => -64.7500],
            ['cod' => '0808', 'nom' => 'Iténez', 'lat' => -13.6667, 'lng' => -63.6667],
        ];

        $provIds = [];
        foreach ($provincias as $p) {
            $provIds[$p['nom']] = DB::table('geografias')->insertGetId([
                'codigo_tse' => $p['cod'],
                'nombre' => $p['nom'],
                'tipo' => 'Provincia',
                'parent_id' => $beniId,
                'latitud' => $p['lat'],
                'longitud' => $p['lng'],
            ]);
        }

        // 4. MUNICIPIOS (Los 19 municipios del Beni con coordenadas y códigos reales)
        $municipios = [
            // CERCADO
            ['cod' => '080101', 'nom' => 'Trinidad', 'prov' => 'Cercado', 'lat' => -14.8333, 'lng' => -64.9167],
            ['cod' => '080102', 'nom' => 'San Javier', 'prov' => 'Cercado', 'lat' => -14.6000, 'lng' => -64.8833],
            ['cod' => '080103', 'nom' => 'San Andrés', 'prov' => 'Cercado', 'lat' => -15.5833, 'lng' => -64.4167],

            // VACA DÍEZ
            ['cod' => '080201', 'nom' => 'Riberalta', 'prov' => 'Vaca Díez', 'lat' => -11.0167, 'lng' => -66.0667],
            ['cod' => '080202', 'nom' => 'Guayaramerín', 'prov' => 'Vaca Díez', 'lat' => -10.8167, 'lng' => -65.3667],

            // JOSÉ BALLIVIÁN
            ['cod' => '080301', 'nom' => 'Reyes', 'prov' => 'José Ballivián', 'lat' => -14.2958, 'lng' => -67.3358],
            ['cod' => '080302', 'nom' => 'San Borja', 'prov' => 'José Ballivián', 'lat' => -14.8167, 'lng' => -66.8500],
            ['cod' => '080303', 'nom' => 'Santa Rosa de Yacuma', 'prov' => 'José Ballivián', 'lat' => -13.2833, 'lng' => -65.9333],
            ['cod' => '080304', 'nom' => 'Rurrenabaque', 'prov' => 'José Ballivián', 'lat' => -14.4333, 'lng' => -67.5333],

            // YACUMA
            ['cod' => '080401', 'nom' => 'Santa Ana del Yacuma', 'prov' => 'Yacuma', 'lat' => -13.5000, 'lng' => -65.5000],
            ['cod' => '080402', 'nom' => 'Exaltación', 'prov' => 'Yacuma', 'lat' => -13.2667, 'lng' => -65.2333],

            // MOXOS
            ['cod' => '080501', 'nom' => 'San Ignacio de Moxos', 'prov' => 'Moxos', 'lat' => -15.0833, 'lng' => -65.7500],

            // MARBÁN
            ['cod' => '080601', 'nom' => 'Loreto', 'prov' => 'Marbán', 'lat' => -15.1917, 'lng' => -64.7583],
            ['cod' => '080602', 'nom' => 'San Andrés (Marbán)', 'prov' => 'Marbán', 'lat' => -15.1500, 'lng' => -64.4000],

            // MAMORÉ
            ['cod' => '080701', 'nom' => 'San Joaquín', 'prov' => 'Mamoré', 'lat' => -13.0000, 'lng' => -64.7500],
            ['cod' => '080702', 'nom' => 'San Ramón', 'prov' => 'Mamoré', 'lat' => -13.2833, 'lng' => -64.7167],
            ['cod' => '080703', 'nom' => 'Puerto Siles', 'prov' => 'Mamoré', 'lat' => -12.8333, 'lng' => -64.9167],

            // ITÉNEZ
            ['cod' => '080801', 'nom' => 'Magdalena', 'prov' => 'Iténez', 'lat' => -13.2667, 'lng' => -64.0500],
            ['cod' => '080802', 'nom' => 'Baures', 'prov' => 'Iténez', 'lat' => -13.5833, 'lng' => -63.5833],
            ['cod' => '080803', 'nom' => 'Huacaraje', 'prov' => 'Iténez', 'lat' => -13.6667, 'lng' => -63.6667],
        ];

        foreach ($municipios as $m) {
            DB::table('geografias')->insert([
                'codigo_tse' => $m['cod'],
                'nombre' => $m['nom'],
                'tipo' => 'Municipio',
                'parent_id' => $provIds[$m['prov']],
                'latitud' => $m['lat'],
                'longitud' => $m['lng'],
                // 'created_at' => now(),
            ]);
        }
    }
}
