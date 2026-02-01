# 04 - Migración del Dashboard de Resultados Electorales

## 📊 Plan 05-Dashboard-Resultados-Eleccion en React

Este documento migra el plan completo del Dashboard de Resultados Electorales de Vue a React, adaptado para la rama `electoral`.

---

## 🎯 Objetivos del Dashboard

1. ✅ Dashboard público accesible para observadores
2. ✅ Actualización automática en tiempo real (sin recarga completa)
3. ✅ Rendimiento óptimo para alto tráfico
4. ✅ Estadísticas claras y visuales
5. ✅ Filtros por cargo y geografía
6. ✅ Stack consistente: React + Inertia.js

---

## 📁 Estructura de Archivos del Dashboard

```
resources/js/
├── Pages/
│   └── Results/
│       └── Index.jsx           # Página principal del dashboard
├── Components/
│   └── Results/
│       ├── LiveClock.jsx       # Reloj en tiempo real
│       ├── ConnectionIndicator.jsx  # Indicador online/offline
│       ├── ResultsStats.jsx    # Grid de estadísticas
│       ├── StatCard.jsx        # Tarjeta individual
│       ├── ResultsFilters.jsx  # Filtros de búsqueda
│       ├── ResultsTable.jsx    # Tabla de resultados
│       ├── ResultsChart.jsx    # Gráfico de votos
│       ├── ResultsGeoTable.jsx # Tabla por geografía
│       ├── AlertError.jsx      # Alerta de errores
│       └── LoadingBar.jsx      # Barra de carga superior
├── Layouts/
│   └── ResultsLayout.jsx       # Layout del dashboard
└── Hooks/
    ├── useElectionResults.js   # Lógica de resultados
    └── useChartData.js         # Lógica de gráficos
```

---

## 🔌 FASE 1: Infraestructura del Dashboard (Backend)

### 1.1 Crear Rutas Públicas

**Archivo:** `routes/web.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResultsController;

// ... rutas existentes ...

// ──────────────── DASHBOARD DE RESULTADOS (PÚBLICO) ────────────────
Route::prefix('results')
    ->middleware(['web', \App\Http\Middleware\HandleInertiaRequests::class])
    ->group(function () {
        
        // Página principal del dashboard
        Route::get('/', [ResultsController::class, 'index'])
            ->name('results.index');
        
        // Filtrar por cargo
        Route::get('/cargo/{cargoId}', [ResultsController::class, 'showByCargo'])
            ->name('results.cargo');
        
        // Filtrar por geografía
        Route::get('/geografia/{geografiaId}', [ResultsController::class, 'showByGeografia'])
            ->name('results.geografia');
    });

// API para datos en vivo (sin Inertia)
Route::prefix('results/api')->group(function () {
    
    // Datos en vivo con filtros
    Route::get('/live', [ResultsController::class, 'liveData'])
        ->name('results.api.live');
    
    // Health check
    Route::get('/health', [ResultsController::class, 'healthCheck'])
        ->name('results.health');
});
```

### 1.2 Crear ResultsController

**Archivo:** `app/Http/Controllers/ResultsController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ResumenVoto;
use App\Models\Cargo;
use App\Models\Geografia;
use App\Models\Mesa;
use App\Models\ActaEscrutinio;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ResultsController extends Controller
{
    /**
     * Dashboard principal
     */
    public function index()
    {
        $cargos = Cache::remember('cargos:all', 3600, function () {
            return Cargo::all(['id_cargo', 'descripcion', 'nivel']);
        });

        $geografias = Cache::remember('geografias:municipios', 3600, function () {
            return Geografia::where('nivel', 'municipio')
                ->get(['id_geografia', 'nombre']);
        });

        return Inertia::render('Results/Index', [
            'cargos' => $cargos,
            'geografias' => $geografias,
        ]);
    }

    /**
     * Filtrar por cargo
     */
    public function showByCargo($cargoId)
    {
        $cargos = Cache::remember('cargos:all', 3600, function () {
            return Cargo::all(['id_cargo', 'descripcion', 'nivel']);
        });

        $geografias = Cache::remember('geografias:municipios', 3600, function () {
            return Geografia::where('nivel', 'municipio')
                ->get(['id_geografia', 'nombre']);
        });

        return Inertia::render('Results/Index', [
            'cargos' => $cargos,
            'geografias' => $geografias,
            'cargoId' => $cargoId,
        ]);
    }

    /**
     * Filtrar por geografía
     */
    public function showByGeografia($geografiaId)
    {
        $cargos = Cache::remember('cargos:all', 3600, function () {
            return Cargo::all(['id_cargo', 'descripcion', 'nivel']);
        });

        $geografias = Cache::remember('geografias:municipios', 3600, function () {
            return Geografia::where('nivel', 'municipio')
                ->get(['id_geografia', 'nombre']);
        });

        return Inertia::render('Results/Index', [
            'cargos' => $cargos,
            'geografias' => $geografias,
            'geografiaId' => $geografiaId,
        ]);
    }

    /**
     * API: Datos en vivo
     */
    public function liveData(Request $request)
    {
        $cacheKey = 'live_results_' . md5($request->fullUrl());
        
        $data = Cache::remember($cacheKey, 10, function () use ($request) {
            $query = ResumenVoto::query()
                ->with([
                    'partido:id_partido,nombre,sigla,color_hex',
                    'cargo:id_cargo,descripcion',
                    'geografia:id_geografia,nombre'
                ]);
            
            if ($request->cargo_id) {
                $query->where('id_cargo', $request->cargo_id);
            }
            
            if ($request->geografia_id) {
                $query->where('id_geografia', $request->geografia_id);
            }

            $results = $query->get();

            $totalVotos = $results->sum('total_votos');
            $totalMesas = Mesa::count();
            $mesasEscrutadas = $results->max('total_mesas_escrutadas') ?? 0;

            return [
                'results' => $results->map(function ($r) use ($totalVotos) {
                    return [
                        'id_partido' => $r->partido->id_partido,
                        'sigla' => $r->partido->sigla,
                        'nombre' => $r->partido->nombre,
                        'color_hex' => $r->partido->color_hex,
                        'total_votos' => $r->total_votos,
                        'porcentaje_votos' => $totalVotos > 0 
                            ? round(($r->total_votos / $totalVotos) * 100, 2) 
                            : 0,
                    ];
                })->sortByDesc('total_votos')->values(),
                
                'resultsByGeografia' => $results->groupBy('id_geografia')->map(function ($group) use ($totalMesas) {
                    $geo = $group->first()->geografia;
                    return [
                        'id_geografia' => $geo->id_geografia,
                        'nombre' => $geo->nombre,
                        'total_votos' => $group->sum('total_votos'),
                        'mesas_escrutadas' => $group->max('total_mesas_escrutadas'),
                        'total_mesas' => $totalMesas,
                        'resultados' => $group->map(function ($r) {
                            return [
                                'id_partido' => $r->partido->id_partido,
                                'sigla' => $r->partido->sigla,
                                'total_votos' => $r->total_votos,
                                'color_hex' => $r->partido->color_hex,
                            ];
                        })->sortByDesc('total_votos')->values()->take(5),
                    ];
                })->values(),
                
                'stats' => [
                    'totalMesas' => $totalMesas,
                    'mesasEscrutadas' => $mesasEscrutadas,
                    'porcentajeEscrutado' => $totalMesas > 0 
                        ? round(($mesasEscrutadas / $totalMesas) * 100, 2) 
                        : 0,
                    'totalVotos' => $totalVotos,
                    'actasRecibidas' => ActaEscrutinio::where('created_at', '>=', now()->subHour())->count(),
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * API: Health check
     */
    public function healthCheck()
    {
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
        } catch (\Exception $e) {
            $dbConnected = false;
        }

        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'cache_driver' => config('cache.default'),
            'redis_connected' => Cache::store('redis')->get('health_check', false),
            'database_connected' => $dbConnected,
        ]);
    }
}
```

---

## 🎨 FASE 2: Layout del Dashboard

### ResultsLayout.jsx

```jsx
// resources/js/Layouts/ResultsLayout.jsx
import React from 'react';
import { Head, Link } from '@inertiajs/react';
import LiveClock from '@/Components/Results/LiveClock';
import ConnectionIndicator from '@/Components/Results/ConnectionIndicator';

export default function ResultsLayout({ children, title = 'Resultados Electorales' }) {
    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-900">
            <Head title={title} />
            
            {/* Navbar */}
            <nav className="bg-gradient-to-r from-blue-900 to-blue-700 text-white shadow-lg">
                <div className="max-w-7xl mx-auto px-4 py-4">
                    <div className="flex items-center justify-between">
                        <div>
                            <Link href="/results" className="text-2xl font-bold hover:text-blue-200 transition-colors">
                                📊 Resultados Electorales
                            </Link>
                            <p className="text-blue-200 text-sm">Actualización en tiempo real</p>
                        </div>
                        <div className="flex items-center gap-4">
                            <LiveClock />
                            <ConnectionIndicator />
                        </div>
                    </div>
                </div>
            </nav>

            {/* Main Content */}
            <main className="max-w-7xl mx-auto px-4 py-6">
                {children}
            </main>

            {/* Footer */}
            <footer className="bg-gray-800 text-gray-400 py-4 mt-12">
                <div className="max-w-7xl mx-auto px-4 text-center text-sm">
                    <p>Sistema Electoral © {new Date().getFullYear()} - Datos actualizados automáticamente</p>
                </div>
            </footer>
        </div>
    );
}
```

---

## ⏰ FASE 3: Componentes Base

### LiveClock.jsx

```jsx
// resources/js/Components/Results/LiveClock.jsx
import React, { useState, useEffect } from 'react';

export default function LiveClock() {
    const [currentTime, setCurrentTime] = useState('');

    useEffect(() => {
        const updateTime = () => {
            setCurrentTime(new Date().toLocaleTimeString('es-BO', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            }));
        };

        updateTime();
        const timer = setInterval(updateTime, 1000);

        return () => clearInterval(timer);
    }, []);

    return (
        <div className="text-lg font-mono bg-blue-800 px-4 py-2 rounded">
            {currentTime}
        </div>
    );
}
```

### ConnectionIndicator.jsx

```jsx
// resources/js/Components/Results/ConnectionIndicator.jsx
import React, { useState, useEffect } from 'react';

export default function ConnectionIndicator() {
    const [isOnline, setIsOnline] = useState(navigator.onLine);

    useEffect(() => {
        const handleOnline = () => setIsOnline(true);
        const handleOffline = () => setIsOnline(false);

        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);

        return () => {
            window.removeEventListener('online', handleOnline);
            window.removeEventListener('offline', handleOffline);
        };
    }, []);

    return (
        <span className="flex items-center gap-2">
            <span 
                className={`w-3 h-3 rounded-full ${
                    isOnline 
                        ? 'bg-green-400 animate-pulse' 
                        : 'bg-red-400'
                }`}
            />
            <span className="text-sm">
                {isOnline ? 'En vivo' : 'Desconectado'}
            </span>
        </span>
    );
}
```

---

## 📊 FASE 4: Hooks del Dashboard

### useElectionResults.js

```jsx
// resources/js/Hooks/useElectionResults.js
import { useState, useEffect, useRef, useCallback } from 'react';
import api from '@/Services/api';

export function useElectionResults(initialCargo = '', initialGeografia = '') {
    const [results, setResults] = useState([]);
    const [resultsByGeografia, setResultsByGeografia] = useState([]);
    const [stats, setStats] = useState({
        totalMesas: 0,
        mesasEscrutadas: 0,
        porcentajeEscrutado: 0,
        totalVotos: 0,
        actasRecibidas: 0,
    });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [selectedCargo, setSelectedCargo] = useState(initialCargo);
    const [selectedGeografia, setSelectedGeografia] = useState(initialGeografia);
    const [refreshInterval, setRefreshInterval] = useState(5000);
    
    const autoRefreshTimer = useRef(null);

    const loadResults = useCallback(async () => {
        setLoading(true);
        setError(null);
        
        try {
            const params = {};
            if (selectedCargo) params.cargo_id = selectedCargo;
            if (selectedGeografia) params.geografia_id = selectedGeografia;

            const response = await api.getResults(params);
            const data = response.data.data;
            
            setResults(data.results);
            setResultsByGeografia(data.resultsByGeografia);
            setStats(data.stats);
        } catch (err) {
            setError(err.message || 'Error al cargar resultados');
            console.error('Error loading results:', err);
        } finally {
            setLoading(false);
        }
    }, [selectedCargo, selectedGeografia]);

    const startAutoRefresh = useCallback(() => {
        // Clear existing timer
        if (autoRefreshTimer.current) {
            clearInterval(autoRefreshTimer.current);
        }
        
        // Start new timer if interval > 0
        if (refreshInterval > 0) {
            autoRefreshTimer.current = setInterval(loadResults, refreshInterval);
        }
    }, [refreshInterval, loadResults]);

    const stopAutoRefresh = useCallback(() => {
        if (autoRefreshTimer.current) {
            clearInterval(autoRefreshTimer.current);
            autoRefreshTimer.current = null;
        }
    }, []);

    const restartAutoRefresh = useCallback(() => {
        stopAutoRefresh();
        startAutoRefresh();
    }, [stopAutoRefresh, startAutoRefresh]);

    // Cleanup on unmount
    useEffect(() => {
        return () => {
            stopAutoRefresh();
        };
    }, [stopAutoRefresh]);

    return {
        results,
        resultsByGeografia,
        stats,
        loading,
        error,
        selectedCargo,
        setSelectedCargo,
        selectedGeografia,
        setSelectedGeografia,
        refreshInterval,
        setRefreshInterval,
        loadResults,
        startAutoRefresh,
        stopAutoRefresh,
        restartAutoRefresh,
    };
}
```

### useChartData.js (con Recharts)

```jsx
// resources/js/Hooks/useChartData.js
import { useState, useCallback } from 'react';

export function useChartData() {
    const [chartData, setChartData] = useState([]);

    const updateChartData = useCallback((results) => {
        if (!results || results.length === 0) {
            setChartData([]);
            return;
        }

        const data = results.map(r => ({
            name: r.sigla,
            votos: r.total_votos,
            color: r.color_hex,
            porcentaje: r.porcentaje_votos,
        }));

        setChartData(data);
    }, []);

    const clearChartData = useCallback(() => {
        setChartData([]);
    }, []);

    return {
        chartData,
        updateChartData,
        clearChartData,
    };
}
```

---

## 🎯 FASE 5: Componentes del Dashboard

### ResultsStats.jsx + StatCard.jsx

```jsx
// resources/js/Components/Results/StatCard.jsx
import React from 'react';

export default function StatCard({ title, value, subtitle, color, icon }) {
    return (
        <div className={`bg-gradient-to-br ${color} rounded-lg shadow-md p-6 text-white`}>
            <div className="flex items-center justify-between mb-2">
                <span className="text-2xl">{icon}</span>
            </div>
            <div className="text-sm opacity-80">{title}</div>
            <div className="text-3xl font-bold my-1">{value}</div>
            <div className="text-sm opacity-80">{subtitle}</div>
        </div>
    );
}
```

```jsx
// resources/js/Components/Results/ResultsStats.jsx
import React, { useState, useEffect } from 'react';
import StatCard from './StatCard';

export default function ResultsStats({ stats }) {
    const [lastUpdate, setLastUpdate] = useState('--:--:--');

    useEffect(() => {
        const updateTime = () => {
            setLastUpdate(new Date().toLocaleTimeString('es-BO', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            }));
        };

        updateTime();
        const timer = setInterval(updateTime, 1000);

        return () => clearInterval(timer);
    }, []);

    const formatNumber = (num) => {
        return num ? num.toLocaleString('es-BO') : '0';
    };

    return (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <StatCard
                title="Mesas Escrutadas"
                value={stats.mesasEscrutadas || 0}
                subtitle={`${stats.porcentajeEscrutado || 0}%`}
                color="from-blue-500 to-blue-600"
                icon="🗳️"
            />

            <StatCard
                title="Total Votos"
                value={formatNumber(stats.totalVotos)}
                subtitle="Válidos emitidos"
                color="from-green-500 to-green-600"
                icon="📊"
            />

            <StatCard
                title="Última Actualización"
                value={lastUpdate}
                subtitle="Hace menos de 1 minuto"
                color="from-yellow-500 to-yellow-600"
                icon="🕐"
            />

            <StatCard
                title="Actas Recibidas"
                value={stats.actasRecibidas || 0}
                subtitle="En última hora"
                color="from-purple-500 to-purple-600"
                icon="📥"
            />
        </div>
    );
}
```

### ResultsFilters.jsx

```jsx
// resources/js/Components/Results/ResultsFilters.jsx
import React from 'react';

export default function ResultsFilters({
    filters,
    onFilterChange,
    cargos = [],
    geografias = [],
    loading,
    onRefresh,
}) {
    const handleCargoChange = (e) => {
        onFilterChange({ ...filters, cargo: e.target.value });
    };

    const handleGeografiaChange = (e) => {
        onFilterChange({ ...filters, geografia: e.target.value });
    };

    const handleIntervalChange = (e) => {
        onFilterChange({ ...filters, interval: parseInt(e.target.value) });
    };

    return (
        <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 mb-6">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Cargo
                    </label>
                    <select
                        value={filters.cargo}
                        onChange={handleCargoChange}
                        className="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todos los cargos</option>
                        {cargos.map(cargo => (
                            <option key={cargo.id_cargo} value={cargo.id_cargo}>
                                {cargo.descripcion}
                            </option>
                        ))}
                    </select>
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Geografía
                    </label>
                    <select
                        value={filters.geografia}
                        onChange={handleGeografiaChange}
                        className="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Toda la geografía</option>
                        {geografias.map(geo => (
                            <option key={geo.id_geografia} value={geo.id_geografia}>
                                {geo.nombre}
                            </option>
                        ))}
                    </select>
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Actualizar cada
                    </label>
                    <select
                        value={filters.interval}
                        onChange={handleIntervalChange}
                        className="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value={5000}>5 segundos</option>
                        <option value={10000}>10 segundos</option>
                        <option value={30000}>30 segundos</option>
                        <option value={60000}>1 minuto</option>
                        <option value={0}>Manual</option>
                    </select>
                </div>

                <div className="flex items-end">
                    <button
                        onClick={onRefresh}
                        disabled={loading}
                        className="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                    >
                        {loading ? 'Actualizando...' : 'Actualizar ahora'}
                    </button>
                </div>
            </div>
        </div>
    );
}
```

### ResultsTable.jsx

```jsx
// resources/js/Components/Results/ResultsTable.jsx
import React, { useMemo } from 'react';

export default function ResultsTable({ results }) {
    const formatNumber = (num) => {
        return num ? num.toLocaleString('es-BO') : '0';
    };

    const totalVotos = useMemo(() => {
        return results.reduce((sum, r) => sum + r.total_votos, 0);
    }, [results]);

    if (results.length === 0) {
        return (
            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 text-center text-gray-500">
                No hay resultados disponibles
            </div>
        );
    }

    return (
        <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
            <div className="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-b dark:border-gray-600">
                <h2 className="text-lg font-semibold text-gray-800 dark:text-white">
                    Resultados por Partido
                </h2>
            </div>
            <div className="overflow-x-auto">
                <table className="w-full">
                    <thead className="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th className="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">#</th>
                            <th className="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Partido</th>
                            <th className="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">Votos</th>
                            <th className="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">%</th>
                            <th className="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Gráfico</th>
                        </tr>
                    </thead>
                    <tbody>
                        {results.map((result, index) => (
                            <tr 
                                key={result.id_partido} 
                                className="border-b dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            >
                                <td className="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{index + 1}</td>
                                <td className="px-4 py-3">
                                    <div className="flex items-center gap-2">
                                        <div
                                            className="w-4 h-4 rounded"
                                            style={{ backgroundColor: result.color_hex }}
                                        />
                                        <span className="font-medium text-gray-900 dark:text-white">
                                            {result.sigla}
                                        </span>
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">
                                    {formatNumber(result.total_votos)}
                                </td>
                                <td className="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                    {result.porcentaje_votos}%
                                </td>
                                <td className="px-4 py-3">
                                    <div className="w-32 bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                        <div
                                            className="h-2 rounded-full transition-all duration-500"
                                            style={{
                                                width: `${result.porcentaje_votos}%`,
                                                backgroundColor: result.color_hex,
                                            }}
                                        />
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                    <tfoot>
                        <tr className="bg-gray-100 dark:bg-gray-700 font-bold">
                            <td colSpan="2" className="px-4 py-3 text-gray-900 dark:text-white">TOTAL</td>
                            <td className="px-4 py-3 text-right text-gray-900 dark:text-white">
                                {formatNumber(totalVotos)}
                            </td>
                            <td className="px-4 py-3 text-right text-gray-900 dark:text-white">100%</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    );
}
```

### ResultsChart.jsx (con Recharts)

```jsx
// resources/js/Components/Results/ResultsChart.jsx
import React from 'react';
import {
    BarChart,
    Bar,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    ResponsiveContainer,
    Cell,
} from 'recharts';

const CustomTooltip = ({ active, payload, label }) => {
    if (active && payload && payload.length) {
        return (
            <div className="bg-white dark:bg-gray-800 p-3 border rounded shadow-lg">
                <p className="font-semibold text-gray-900 dark:text-white">{label}</p>
                <p className="text-gray-600 dark:text-gray-400">
                    Votos: {payload[0].value.toLocaleString('es-BO')}
                </p>
                <p className="text-gray-600 dark:text-gray-400">
                    Porcentaje: {payload[0].payload.porcentaje}%
                </p>
            </div>
        );
    }
    return null;
};

export default function ResultsChart({ results }) {
    if (!results || results.length === 0) {
        return (
            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 text-center text-gray-500">
                No hay datos para mostrar
            </div>
        );
    }

    const data = results.map(r => ({
        name: r.sigla,
        votos: r.total_votos,
        color: r.color_hex,
        porcentaje: r.porcentaje_votos,
    }));

    return (
        <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div className="mb-4">
                <h2 className="text-lg font-semibold text-gray-800 dark:text-white">
                    Distribución de Votos
                </h2>
            </div>
            <div className="h-80">
                <ResponsiveContainer width="100%" height="100%">
                    <BarChart data={data} margin={{ top: 20, right: 30, left: 20, bottom: 5 }}>
                        <CartesianGrid strokeDasharray="3 3" stroke="#374151" />
                        <XAxis 
                            dataKey="name" 
                            tick={{ fill: '#9CA3AF' }}
                            axisLine={{ stroke: '#4B5563' }}
                        />
                        <YAxis 
                            tick={{ fill: '#9CA3AF' }}
                            axisLine={{ stroke: '#4B5563' }}
                            tickFormatter={(value) => value.toLocaleString('es-BO')}
                        />
                        <Tooltip content={<CustomTooltip />} />
                        <Bar dataKey="votos" radius={[4, 4, 0, 0]}>
                            {data.map((entry, index) => (
                                <Cell key={`cell-${index}`} fill={entry.color} />
                            ))}
                        </Bar>
                    </BarChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
}
```

**Nota:** Para usar Recharts, instálalo con:
```bash
npm install recharts
```

### ResultsGeoTable.jsx

```jsx
// resources/js/Components/Results/ResultsGeoTable.jsx
import React, { useMemo } from 'react';

export default function ResultsGeoTable({ resultsByGeografia }) {
    const formatNumber = (num) => {
        return num ? num.toLocaleString('es-BO') : '0';
    };

    // Extraer todos los partidos únicos para las columnas
    const topPartidos = useMemo(() => {
        const partidosSet = new Set();
        resultsByGeografia.forEach(geo => {
            geo.resultados.forEach(r => {
                partidosSet.add(JSON.stringify({
                    id_partido: r.id_partido,
                    sigla: r.sigla,
                    color_hex: r.color_hex,
                }));
            });
        });
        return Array.from(partidosSet)
            .map(p => JSON.parse(p))
            .slice(0, 5); // Top 5 partidos
    }, [resultsByGeografia]);

    if (resultsByGeografia.length === 0) {
        return null;
    }

    return (
        <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden mt-6">
            <div className="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-b dark:border-gray-600">
                <h2 className="text-lg font-semibold text-gray-800 dark:text-white">
                    Resultados por Geografía
                </h2>
            </div>
            <div className="overflow-x-auto">
                <table className="w-full">
                    <thead className="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th className="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                Geografía
                            </th>
                            <th className="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                                Mesas
                            </th>
                            <th className="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                                Votos
                            </th>
                            {topPartidos.map(partido => (
                                <th 
                                    key={partido.id_partido}
                                    className="px-4 py-3 text-right text-sm font-semibold"
                                    style={{ color: partido.color_hex }}
                                >
                                    {partido.sigla}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody>
                        {resultsByGeografia.map(geo => (
                            <tr 
                                key={geo.id_geografia}
                                className="border-b dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            >
                                <td className="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                    {geo.nombre}
                                </td>
                                <td className="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                    {geo.mesas_escrutadas}/{geo.total_mesas}
                                </td>
                                <td className="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">
                                    {formatNumber(geo.total_votos)}
                                </td>
                                {topPartidos.map(partido => {
                                    const resultado = geo.resultados.find(
                                        r => r.id_partido === partido.id_partido
                                    );
                                    return (
                                        <td 
                                            key={partido.id_partido}
                                            className="px-4 py-3 text-right text-gray-700 dark:text-gray-300"
                                        >
                                            {resultado ? formatNumber(resultado.total_votos) : '-'}
                                        </td>
                                    );
                                })}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
```

### AlertError.jsx

```jsx
// resources/js/Components/Results/AlertError.jsx
import React from 'react';

export default function AlertError({ message }) {
    if (!message) return null;

    return (
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <div className="flex items-center">
                <span className="text-xl mr-2">⚠️</span>
                <span>{message}</span>
            </div>
        </div>
    );
}
```

### LoadingBar.jsx

```jsx
// resources/js/Components/Results/LoadingBar.jsx
import React from 'react';

export default function LoadingBar({ loading }) {
    if (!loading) return null;

    return (
        <div className="fixed top-0 left-0 right-0 h-1 bg-blue-500 animate-pulse z-50" />
    );
}
```

---

## 📄 FASE 6: Página Principal del Dashboard

### Index.jsx (Página Completa)

```jsx
// resources/js/Pages/Results/Index.jsx
import React, { useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import ResultsLayout from '@/Layouts/ResultsLayout';
import ResultsFilters from '@/Components/Results/ResultsFilters';
import ResultsStats from '@/Components/Results/ResultsStats';
import ResultsTable from '@/Components/Results/ResultsTable';
import ResultsChart from '@/Components/Results/ResultsChart';
import ResultsGeoTable from '@/Components/Results/ResultsGeoTable';
import AlertError from '@/Components/Results/AlertError';
import LoadingBar from '@/Components/Results/LoadingBar';
import { useElectionResults } from '@/Hooks/useElectionResults';

export default function Index() {
    const { props } = usePage();
    const { cargos = [], geografias = [], cargoId = '', geografiaId = '' } = props;

    const {
        results,
        resultsByGeografia,
        stats,
        loading,
        error,
        selectedCargo,
        setSelectedCargo,
        selectedGeografia,
        setSelectedGeografia,
        refreshInterval,
        setRefreshInterval,
        loadResults,
        startAutoRefresh,
        restartAutoRefresh,
    } = useElectionResults(cargoId, geografiaId);

    // Initialize from URL params
    useEffect(() => {
        if (cargoId) {
            setSelectedCargo(cargoId);
        }
        if (geografiaId) {
            setSelectedGeografia(geografiaId);
        }
    }, [cargoId, geografiaId, setSelectedCargo, setSelectedGeografia]);

    // Initial load
    useEffect(() => {
        loadResults();
        startAutoRefresh();
    }, []); // Run once on mount

    // Watch for filter changes
    useEffect(() => {
        loadResults();
    }, [selectedCargo, selectedGeografia]);

    // Watch for interval changes
    useEffect(() => {
        restartAutoRefresh();
    }, [refreshInterval]);

    const handleFilterChange = (newFilters) => {
        setSelectedCargo(newFilters.cargo);
        setSelectedGeografia(newFilters.geografia);
        setRefreshInterval(newFilters.interval);
    };

    const handleRefresh = () => {
        loadResults();
    };

    return (
        <ResultsLayout title="Dashboard de Resultados">
            <LoadingBar loading={loading} />

            <ResultsFilters
                filters={{
                    cargo: selectedCargo,
                    geografia: selectedGeografia,
                    interval: refreshInterval,
                }}
                onFilterChange={handleFilterChange}
                cargos={cargos}
                geografias={geografias}
                loading={loading}
                onRefresh={handleRefresh}
            />

            <ResultsStats stats={stats} />

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <ResultsTable results={results} />
                <ResultsChart results={results} />
            </div>

            <ResultsGeoTable resultsByGeografia={resultsByGeografia} />

            <AlertError message={error} />
        </ResultsLayout>
    );
}
```

---

## 🧪 FASE 7: Testing

### Tests Unitarios para Hooks

```jsx
// tests/unit/hooks/useElectionResults.test.js
import { renderHook, act, waitFor } from '@testing-library/react';
import { useElectionResults } from '@/Hooks/useElectionResults';
import api from '@/Services/api';

// Mock API
jest.mock('@/Services/api');

describe('useElectionResults', () => {
    beforeEach(() => {
        jest.clearAllMocks();
    });

    it('should initialize with empty state', () => {
        const { result } = renderHook(() => useElectionResults());
        
        expect(result.current.results).toEqual([]);
        expect(result.current.loading).toBe(false);
        expect(result.current.error).toBeNull();
    });

    it('should load results successfully', async () => {
        const mockData = {
            data: {
                results: [
                    { id_partido: 1, sigla: 'MAS', total_votos: 1000 },
                ],
                resultsByGeografia: [],
                stats: { totalVotos: 1000 },
            },
        };

        api.getResults.mockResolvedValueOnce(mockData);

        const { result } = renderHook(() => useElectionResults());

        await act(async () => {
            await result.current.loadResults();
        });

        expect(result.current.results).toHaveLength(1);
        expect(result.current.results[0].sigla).toBe('MAS');
    });

    it('should handle errors', async () => {
        api.getResults.mockRejectedValueOnce(new Error('Network error'));

        const { result } = renderHook(() => useElectionResults());

        await act(async () => {
            await result.current.loadResults();
        });

        expect(result.current.error).toBe('Network error');
    });
});
```

---

## ⚡ FASE 8: Optimización

### Instalar Recharts

```bash
npm install recharts
```

### Optimizaciones de Rendimiento

1. **Memoización:** Usar `useMemo` para cálculos costosos
2. **Callback memoization:** Usar `useCallback` para funciones pasadas a hijos
3. **Virtualización:** Para tablas grandes (react-window)
4. **Code Splitting:** Lazy load del dashboard

```jsx
// Ejemplo de lazy loading
import React, { lazy, Suspense } from 'react';

const ResultsChart = lazy(() => import('@/Components/Results/ResultsChart'));

// En el render:
<Suspense fallback={<div>Cargando gráfico...</div>}>
    <ResultsChart results={results} />
</Suspense>
```

---

## 📊 API del Dashboard

### Endpoints

#### GET /results/api/live

**Parámetros:**
- `cargo_id` (opcional): Filtrar por cargo
- `geografia_id` (opcional): Filtrar por geografía

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "results": [
      {
        "id_partido": 1,
        "sigla": "MAS",
        "nombre": "Movimiento al Socialismo",
        "color_hex": "#0047AB",
        "total_votos": 150000,
        "porcentaje_votos": 45.5
      }
    ],
    "resultsByGeografia": [
      {
        "id_geografia": 1,
        "nombre": "La Paz",
        "total_votos": 50000,
        "mesas_escrutadas": 100,
        "total_mesas": 150,
        "resultados": [...]
      }
    ],
    "stats": {
      "totalMesas": 5000,
      "mesasEscrutadas": 3500,
      "porcentajeEscrutado": 70,
      "totalVotos": 1000000,
      "actasRecibidas": 50
    }
  }
}
```

#### GET /results/api/health

**Respuesta:**
```json
{
  "status": "healthy",
  "timestamp": "2026-02-01T12:00:00Z",
  "cache_driver": "redis",
  "redis_connected": true,
  "database_connected": true
}
```

---

## ✅ Checklist del Dashboard

### Backend
- [ ] ResultsController creado
- [ ] Rutas configuradas en web.php
- [ ] API endpoints funcionando
- [ ] Caché implementado (Redis)
- [ ] Health check endpoint

### Frontend
- [ ] ResultsLayout.jsx
- [ ] LiveClock.jsx funciona
- [ ] ConnectionIndicator.jsx funciona
- [ ] useElectionResults.js carga datos
- [ ] ResultsStats.jsx muestra stats
- [ ] ResultsFilters.jsx filtra correctamente
- [ ] ResultsTable.jsx muestra tabla
- [ ] ResultsChart.jsx con Recharts
- [ ] ResultsGeoTable.jsx muestra geografías
- [ ] Index.jsx integra todo
- [ ] Auto-refresh funciona
- [ ] Filtros por URL funcionan

### Testing
- [ ] Tests unitarios para hooks
- [ ] Tests de integración
- [ ] Tests E2E del dashboard

### Optimización
- [ ] Lazy loading de componentes
- [ ] Memoización aplicada
- [ ] Caché configurado

---

## 🚀 Comandos de Desarrollo

```bash
# Instalar Recharts
npm install recharts

# Iniciar servidor
php artisan serve

# Compilar assets
npm run dev

# Acceder al dashboard
open http://localhost:8000/results

# Probar API
curl http://localhost:8000/results/api/live

# Health check
curl http://localhost:8000/results/api/health
```

---

## 📚 Próximo Paso

Continuar con:
- [05-MIGRACION-COMPOSABLES.md](./05-MIGRACION-COMPOSABLES.md) - Si necesitas más detalles sobre hooks
- [07-TESTING-REACT.md](./07-TESTING-REACT.md) - Testing completo
- [09-DEPLOY-PRODUCCION.md](./09-DEPLOY-PRODUCCION.md) - Deploy

---

**Tiempo estimado:** 6 horas  
**Complejidad:** Alta  
**Dependencias:** FASE 2 (Setup) y FASE 3 (Componentes base) completadas
