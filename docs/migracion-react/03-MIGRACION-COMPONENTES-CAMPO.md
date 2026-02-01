# 03 - Migración de Componentes de Campo

## 🎯 Objetivo

Migrar todos los componentes de la aplicación de campo de Vue a React manteniendo 100% de funcionalidad.

---

## 📋 Orden de Migración Recomendado

1. **ThemeToggle.jsx** - Más simple, buen calentamiento
2. **useTheme.js** - Hook necesario para ThemeToggle
3. **AppLayout.jsx** - Layout base, usa ThemeToggle
4. **useNotification.js** - Hook para notificaciones
5. **SyncStatus.jsx** - Componente de UI
6. **useOfflineSync.js** - Hook complejo
7. **useImageCompression.js** - Hook de utilidad
8. **api.js** - Servicio API
9. **storage.js** - Servicio IndexedDB
10. **Home.jsx** - Página simple
11. **Acta.jsx** - Página intermedia
12. **ActaForm.jsx** - Componente más complejo

---

## 1. ThemeToggle.jsx (Primero - Simple)

### Vue Original
```vue
<template>
  <button
    @click="toggleTheme"
    class="p-2 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
    :aria-label="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
  >
    <span v-if="isDark">☀️</span>
    <span v-else>🌙</span>
  </button>
</template>

<script setup>
import { useTheme } from '@/Composables/useTheme'

const { isDark, toggleTheme } = useTheme()
</script>
```

### React Equivalente
```jsx
// resources/js/Components/ThemeToggle.jsx
import React from 'react';
import { useTheme } from '@/Hooks/useTheme';

export default function ThemeToggle() {
    const { isDark, toggleTheme } = useTheme();

    return (
        <button
            onClick={toggleTheme}
            className="p-2 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
            aria-label={isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'}
        >
            {isDark ? '☀️' : '🌙'}
        </button>
    );
}
```

---

## 2. useTheme.js (Hook)

### Vue Original
```javascript
import { ref, onMounted } from 'vue'

export function useTheme() {
    const isDark = ref(false)

    const toggleTheme = () => {
        isDark.value = !isDark.value
        updateTheme()
    }

    const setTheme = (dark) => {
        isDark.value = dark
        updateTheme()
    }

    const updateTheme = () => {
        if (isDark.value) {
            document.documentElement.classList.add('dark')
            localStorage.setItem('theme', 'dark')
        } else {
            document.documentElement.classList.remove('dark')
            localStorage.setItem('theme', 'light')
        }
    }

    onMounted(() => {
        const saved = localStorage.getItem('theme')
        if (saved) {
            isDark.value = saved === 'dark'
        } else {
            isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches
        }
        updateTheme()
    })

    return {
        isDark,
        toggleTheme,
        setTheme,
    }
}
```

### React Equivalente
```jsx
// resources/js/Hooks/useTheme.js
import { useState, useEffect, useCallback } from 'react';

export function useTheme() {
    const [isDark, setIsDark] = useState(false);

    const updateTheme = useCallback((dark) => {
        if (dark) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    }, []);

    const toggleTheme = useCallback(() => {
        setIsDark(prev => {
            const newValue = !prev;
            updateTheme(newValue);
            return newValue;
        });
    }, [updateTheme]);

    const setTheme = useCallback((dark) => {
        setIsDark(dark);
        updateTheme(dark);
    }, [updateTheme]);

    useEffect(() => {
        const saved = localStorage.getItem('theme');
        if (saved) {
            const darkMode = saved === 'dark';
            setIsDark(darkMode);
            updateTheme(darkMode);
        } else {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            setIsDark(prefersDark);
            updateTheme(prefersDark);
        }
    }, [updateTheme]);

    return {
        isDark,
        toggleTheme,
        setTheme,
    };
}
```

---

## 3. AppLayout.jsx (Layout Principal)

### Vue Original
```vue
<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <Head :title="title" />
    
    <nav class="bg-blue-600 dark:bg-blue-800 text-white shadow-lg">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center">
            <Link href="/campo" class="text-xl font-bold">
              🗳️ Electoral
            </Link>
          </div>
          <div class="flex items-center gap-4">
            <SyncStatus />
            <ThemeToggle />
          </div>
        </div>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import ThemeToggle from '@/Components/ThemeToggle.vue'
import SyncStatus from '@/Components/SyncStatus.vue'

defineProps({
  title: {
    type: String,
    default: ''
  }
})
</script>
```

### React Equivalente
```jsx
// resources/js/Layouts/AppLayout.jsx
import React from 'react';
import { Head, Link } from '@inertiajs/react';
import ThemeToggle from '@/Components/ThemeToggle';
import SyncStatus from '@/Components/SyncStatus';

export default function AppLayout({ children, title = '' }) {
    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
            <Head title={title} />
            
            <nav className="bg-blue-600 dark:bg-blue-800 text-white shadow-lg">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center justify-between h-16">
                        <div className="flex items-center">
                            <Link href="/campo" className="text-xl font-bold">
                                🗳️ Electoral
                            </Link>
                        </div>
                        <div className="flex items-center gap-4">
                            <SyncStatus />
                            <ThemeToggle />
                        </div>
                    </div>
                </div>
            </nav>

            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {children}
            </main>
        </div>
    );
}
```

---

## 4. useNotification.js (Hook)

### Vue Original
```javascript
import { ref } from 'vue'

const notifications = ref([])

export function useNotification() {
    const showSuccess = (message, duration = 5000) => {
        addNotification('success', message, duration)
    }

    const showError = (message, duration = 5000) => {
        addNotification('error', message, duration)
    }

    const showInfo = (message, duration = 5000) => {
        addNotification('info', message, duration)
    }

    const addNotification = (type, message, duration) => {
        const id = Date.now()
        notifications.value.push({ id, type, message })
        
        setTimeout(() => {
            removeNotification(id)
        }, duration)
    }

    const removeNotification = (id) => {
        const index = notifications.value.findIndex(n => n.id === id)
        if (index > -1) {
            notifications.value.splice(index, 1)
        }
    }

    const clear = () => {
        notifications.value = []
    }

    return {
        notifications,
        showSuccess,
        showError,
        showInfo,
        removeNotification,
        clear,
    }
}
```

### React Equivalente
```jsx
// resources/js/Hooks/useNotification.js
import { useState, useCallback } from 'react';

export function useNotification() {
    const [notifications, setNotifications] = useState([]);

    const removeNotification = useCallback((id) => {
        setNotifications(prev => prev.filter(n => n.id !== id));
    }, []);

    const addNotification = useCallback((type, message, duration = 5000) => {
        const id = Date.now();
        const newNotification = { id, type, message };
        
        setNotifications(prev => [...prev, newNotification]);
        
        setTimeout(() => {
            removeNotification(id);
        }, duration);
    }, [removeNotification]);

    const showSuccess = useCallback((message, duration) => {
        addNotification('success', message, duration);
    }, [addNotification]);

    const showError = useCallback((message, duration) => {
        addNotification('error', message, duration);
    }, [addNotification]);

    const showInfo = useCallback((message, duration) => {
        addNotification('info', message, duration);
    }, [addNotification]);

    const clear = useCallback(() => {
        setNotifications([]);
    }, []);

    return {
        notifications,
        showSuccess,
        showError,
        showInfo,
        removeNotification,
        clear,
    };
}

// Componente para renderizar notificaciones
export function NotificationContainer({ notifications, onRemove }) {
    const getStyles = (type) => {
        switch (type) {
            case 'success':
                return 'bg-green-100 border-green-400 text-green-700';
            case 'error':
                return 'bg-red-100 border-red-400 text-red-700';
            case 'info':
                return 'bg-blue-100 border-blue-400 text-blue-700';
            default:
                return 'bg-gray-100 border-gray-400 text-gray-700';
        }
    };

    if (notifications.length === 0) return null;

    return (
        <div className="fixed top-4 right-4 z-50 space-y-2">
            {notifications.map(notification => (
                <div
                    key={notification.id}
                    className={`border px-4 py-3 rounded shadow-lg ${getStyles(notification.type)}`}
                >
                    <div className="flex items-center justify-between">
                        <span>{notification.message}</span>
                        <button
                            onClick={() => onRemove(notification.id)}
                            className="ml-4 font-bold hover:text-opacity-75"
                        >
                            ×
                        </button>
                    </div>
                </div>
            ))}
        </div>
    );
}
```

---

## 5. useImageCompression.js (Hook)

### Vue Original
```javascript
export function useImageCompression() {
    const compressImage = async (file, maxWidth = 1920, quality = 0.75) => {
        return new Promise((resolve, reject) => {
            const reader = new FileReader()
            
            reader.onload = (event) => {
                const img = new Image()
                
                img.onload = () => {
                    const canvas = document.createElement('canvas')
                    let width = img.width
                    let height = img.height
                    
                    if (width > maxWidth) {
                        height = Math.round((height * maxWidth) / width)
                        width = maxWidth
                    }
                    
                    canvas.width = width
                    canvas.height = height
                    
                    const ctx = canvas.getContext('2d')
                    ctx.drawImage(img, 0, 0, width, height)
                    
                    canvas.toBlob(
                        (blob) => {
                            if (blob) {
                                const compressedFile = new File([blob], file.name, {
                                    type: 'image/jpeg',
                                    lastModified: Date.now(),
                                })
                                resolve(compressedFile)
                            } else {
                                reject(new Error('Error al comprimir imagen'))
                            }
                        },
                        'image/jpeg',
                        quality
                    )
                }
                
                img.onerror = () => {
                    reject(new Error('Error al cargar imagen'))
                }
                
                img.src = event.target.result
            }
            
            reader.onerror = () => {
                reject(new Error('Error al leer archivo'))
            }
            
            reader.readAsDataURL(file)
        })
    }

    return {
        compressImage,
    }
}
```

### React Equivalente
```jsx
// resources/js/Hooks/useImageCompression.js
import { useCallback } from 'react';

export function useImageCompression() {
    const compressImage = useCallback(async (file, maxWidth = 1920, quality = 0.75) => {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            
            reader.onload = (event) => {
                const img = new Image();
                
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;
                    
                    if (width > maxWidth) {
                        height = Math.round((height * maxWidth) / width);
                        width = maxWidth;
                    }
                    
                    canvas.width = width;
                    canvas.height = height;
                    
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    canvas.toBlob(
                        (blob) => {
                            if (blob) {
                                const compressedFile = new File([blob], file.name, {
                                    type: 'image/jpeg',
                                    lastModified: Date.now(),
                                });
                                resolve(compressedFile);
                            } else {
                                reject(new Error('Error al comprimir imagen'));
                            }
                        },
                        'image/jpeg',
                        quality
                    );
                };
                
                img.onerror = () => {
                    reject(new Error('Error al cargar imagen'));
                };
                
                img.src = event.target.result;
            };
            
            reader.onerror = () => {
                reject(new Error('Error al leer archivo'));
            };
            
            reader.readAsDataURL(file);
        });
    }, []);

    return {
        compressImage,
    };
}
```

---

## 6. api.js (Servicio)

### React Equivalente (similar, solo cambia export)
```jsx
// resources/js/Services/api.js
import axios from 'axios';

const api = axios.create({
    baseURL: '/api/v1',
    timeout: 10000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Request interceptor
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token');
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

export default {
    // Mesas
    getMesa: (codigo) => api.get(`/mesas/${codigo}`),
    
    // Catálogos
    getCatalogos: (params) => api.get('/catalogos', { params }),
    
    // Actas
    sendActa: (formData) => api.post('/actas', formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
    }),
    
    checkActaStatus: (mesaCodigo, cargoId) => 
        api.get('/actas/status', { params: { mesa: mesaCodigo, cargo: cargoId } }),
    
    // Results (Dashboard)
    getResults: (params) => api.get('/results/api/live', { params }),
    
    // Health check
    healthCheck: () => api.get('/results/api/health'),
};
```

---

## 7. storage.js (Servicio IndexedDB)

### React Equivalente (igual, es vanilla JS)
```jsx
// resources/js/Services/storage.js
import { openDB } from 'idb';

const DB_NAME = 'electoral_db';
const DB_VERSION = 1;

let dbPromise = null;

const initDB = () => {
    if (!dbPromise) {
        dbPromise = openDB(DB_NAME, DB_VERSION, {
            upgrade(db) {
                if (!db.objectStoreNames.contains('pending_actas')) {
                    db.createObjectStore('pending_actas', { keyPath: 'id', autoIncrement: true });
                }
                if (!db.objectStoreNames.contains('settings')) {
                    db.createObjectStore('settings');
                }
            },
        });
    }
    return dbPromise;
};

export const storageService = {
    async saveActa(acta) {
        const db = await initDB();
        return db.add('pending_actas', {
            ...acta,
            createdAt: new Date().toISOString(),
        });
    },

    async getPendingActas() {
        const db = await initDB();
        return db.getAll('pending_actas');
    },

    async deleteActa(id) {
        const db = await initDB();
        return db.delete('pending_actas', id);
    },

    async clearAll() {
        const db = await initDB();
        const tx = db.transaction('pending_actas', 'readwrite');
        await tx.store.clear();
        await tx.done;
    },

    async saveSetting(key, value) {
        const db = await initDB();
        return db.put('settings', value, key);
    },

    async getSetting(key) {
        const db = await initDB();
        return db.get('settings', key);
    },
};
```

---

## 8. useOfflineSync.js (Hook Complejo)

Este es uno de los más complejos. Requiere cuidado en la conversión.

```jsx
// resources/js/Hooks/useOfflineSync.js
import { useState, useEffect, useCallback, useRef } from 'react';
import { storageService } from '@/Services/storage';
import api from '@/Services/api';

export function useOfflineSync() {
    const [isOnline, setIsOnline] = useState(navigator.onLine);
    const [pendingCount, setPendingCount] = useState(0);
    const [failedActas, setFailedActas] = useState([]);
    const [lastSync, setLastSync] = useState(null);
    const [syncing, setSyncing] = useState(false);
    const 

[timeout: 0.001s]

Auto-refresh timer ref = useRef(null);
    
    // Update pending count
    const updatePendingCount = useCallback(async () => {
        const actas = await storageService.getPendingActas();
        setPendingCount(actas.length);
    }, []);

    // Sync pending actas
    const sync = useCallback(async () => {
        if (!isOnline || syncing) return;
        
        setSyncing(true);
        
        try {
            const pendingActas = await storageService.getPendingActas();
            
            for (const acta of pendingActas) {
                try {
                    const formData = new FormData();
                    Object.keys(acta).forEach(key => {
                        if (key !== 'id' && key !== 'createdAt') {
                            formData.append(key, acta[key]);
                        }
                    });
                    
                    await api.sendActa(formData);
                    await storageService.deleteActa(acta.id);
                } catch (error) {
                    console.error('Error syncing acta:', error);
                    setFailedActas(prev => [...prev, acta]);
                }
            }
            
            setLastSync(new Date().toISOString());
            await updatePendingCount();
        } finally {
            setSyncing(false);
        }
    }, [isOnline, syncing, updatePendingCount]);

    // Add acta to pending queue
    const addPendingActa = useCallback(async (acta) => {
        await storageService.saveActa(acta);
        await updatePendingCount();
        
        if (isOnline) {
            sync();
        }
    }, [isOnline, sync, updatePendingCount]);

    // Clear failed actas
    const clearFailedActas = useCallback(() => {
        setFailedActas([]);
    }, []);

    // Retry failed acta
    const retryFailedActa = useCallback(async (acta) => {
        setFailedActas(prev => prev.filter(a => a.id !== acta.id));
        await addPendingActa(acta);
    }, [addPendingActa]);

    // Online/offline event handlers
    useEffect(() => {
        const handleOnline = () => {
            setIsOnline(true);
            sync();
        };
        
        const handleOffline = () => {
            setIsOnline(false);
        };

        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);

        return () => {
            window.removeEventListener('online', handleOnline);
            window.removeEventListener('offline', handleOffline);
        };
    }, [sync]);

    // Auto-sync every 60 seconds when online
    useEffect(() => {
        if (isOnline) {
            autoRefreshTimer.current = setInterval(() => {
                sync();
            }, 60000);
        }

        return () => {
            if (autoRefreshTimer.current) {
                clearInterval(autoRefreshTimer.current);
            }
        };
    }, [isOnline, sync]);

    // Initial load
    useEffect(() => {
        updatePendingCount();
    }, [updatePendingCount]);

    return {
        isOnline,
        pendingCount,
        failedActas,
        lastSync,
        syncing,
        sync,
        addPendingActa,
        clearFailedActas,
        retryFailedActa,
    };
}
```

---

## 9. SyncStatus.jsx (Componente)

```jsx
// resources/js/Components/SyncStatus.jsx
import React from 'react';
import { useOfflineSync } from '@/Hooks/useOfflineSync';

export default function SyncStatus() {
    const {
        isOnline,
        pendingCount,
        failedActas,
        syncing,
        sync,
        clearFailedActas,
        retryFailedActa,
    } = useOfflineSync();

    return (
        <div className="flex items-center gap-2">
            {/* Connection status */}
            <div className={`flex items-center gap-1 px-2 py-1 rounded ${
                isOnline ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
            }`}>
                <span className={`w-2 h-2 rounded-full ${
                    isOnline ? 'bg-green-500' : 'bg-red-500'
                }`}></span>
                <span className="text-xs font-medium">
                    {isOnline ? 'Online' : 'Offline'}
                </span>
            </div>

            {/* Pending count */}
            {pendingCount > 0 && (
                <div className="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">
                    {pendingCount} pendiente{pendingCount > 1 ? 's' : ''}
                </div>
            )}

            {/* Sync button */}
            <button
                onClick={sync}
                disabled={!isOnline || syncing || pendingCount === 0}
                className="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded hover:bg-blue-200 disabled:opacity-50"
            >
                {syncing ? 'Sincronizando...' : 'Sincronizar'}
            </button>

            {/* Failed actas */}
            {failedActas.length > 0 && (
                <div className="relative group">
                    <div className="bg-red-100 text-red-800 px-2 py-1 rounded text-xs cursor-pointer">
                        {failedActas.length} fallida{failedActas.length > 1 ? 's' : ''}
                    </div>
                    <div className="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg p-4 hidden group-hover:block z-50">
                        <h4 className="font-bold mb-2">Actas Fallidas</h4>
                        {failedActas.map(acta => (
                            <div key={acta.id} className="mb-2 p-2 bg-gray-50 rounded">
                                <p className="text-xs">Mesa: {acta.mesa_codigo}</p>
                                <div className="flex gap-2 mt-1">
                                    <button
                                        onClick={() => retryFailedActa(acta)}
                                        className="text-xs text-blue-600 hover:underline"
                                    >
                                        Reintentar
                                    </button>
                                </div>
                            </div>
                        ))}
                        <button
                            onClick={clearFailedActas}
                            className="text-xs text-red-600 hover:underline mt-2"
                        >
                            Limpiar todas
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}
```

---

## 10. Home.jsx (Página)

```jsx
// resources/js/Pages/Home.jsx
import React, { useState } from 'react';
import { Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { useNotification, NotificationContainer } from '@/Hooks/useNotification';
import api from '@/Services/api';

export default function Home() {
    const [codigoMesa, setCodigoMesa] = useState('');
    const [loading, setLoading] = useState(false);
    const [mesa, setMesa] = useState(null);
    const [error, setError] = useState('');
    const { notifications, showError, showInfo, removeNotification } = useNotification();

    const buscarMesa = async () => {
        if (codigoMesa.length !== 4) {
            setError('El código debe tener 4 dígitos');
            return;
        }

        setLoading(true);
        setError('');
        setMesa(null);

        try {
            const response = await api.getMesa(codigoMesa);
            setMesa(response.data);
            showInfo(`Mesa ${codigoMesa} encontrada`);
        } catch (err) {
            const message = err.response?.data?.message || 'Error al buscar mesa';
            setError(message);
            showError(message);
        } finally {
            setLoading(false);
        }
    };

    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('es-BO', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    return (
        <AppLayout title="Buscar Mesa">
            <NotificationContainer 
                notifications={notifications} 
                onRemove={removeNotification} 
            />

            <div className="max-w-2xl mx-auto">
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h1 className="text-2xl font-bold mb-6 text-gray-800 dark:text-white">
                        Buscar Mesa Electoral
                    </h1>

                    <div className="mb-4">
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Código de Mesa (4 dígitos)
                        </label>
                        <div className="flex gap-2">
                            <input
                                type="text"
                                value={codigoMesa}
                                onChange={(e) => setCodigoMesa(e.target.value.replace(/\D/g, '').slice(0, 4))}
                                className="flex-1 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                placeholder="0000"
                                maxLength={4}
                            />
                            <button
                                onClick={buscarMesa}
                                disabled={loading || codigoMesa.length !== 4}
                                className="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition-colors"
                            >
                                {loading ? 'Buscando...' : 'Buscar'}
                            </button>
                        </div>
                        {error && (
                            <p className="text-red-500 text-sm mt-2">{error}</p>
                        )}
                    </div>

                    {mesa && (
                        <div className="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <h2 className="text-lg font-semibold mb-4 text-gray-800 dark:text-white">
                                Información de la Mesa
                            </h2>
                            <div className="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p className="text-sm text-gray-600 dark:text-gray-400">Código</p>
                                    <p className="font-medium text-gray-900 dark:text-white">{mesa.codigo}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600 dark:text-gray-400">Recinto</p>
                                    <p className="font-medium text-gray-900 dark:text-white">{mesa.recinto?.nombre || 'N/A'}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600 dark:text-gray-400">Ubicación</p>
                                    <p className="font-medium text-gray-900 dark:text-white">
                                        {mesa.recinto?.municipio?.nombre || 'N/A'}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600 dark:text-gray-400">Votantes</p>
                                    <p className="font-medium text-gray-900 dark:text-white">{mesa.votantes || 'N/A'}</p>
                                </div>
                            </div>
                            <Link
                                href={`/campo/acta/${mesa.codigo}`}
                                className="inline-block bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors"
                            >
                                Cargar Acta →
                            </Link>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
```

---

## 11. Acta.jsx (Página)

```jsx
// resources/js/Pages/Acta.jsx
import React, { useState, useEffect } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import ActaForm from '@/Components/ActaForm';
import { useNotification, NotificationContainer } from '@/Hooks/useNotification';
import api from '@/Services/api';

export default function Acta() {
    const { props } = usePage();
    const { mesa, cargos = [], partidos = [] } = props;
    
    const [actasCompletadas, setActasCompletadas] = useState(new Set());
    const [isOnline, setIsOnline] = useState(navigator.onLine);
    const { notifications, showSuccess, showError, removeNotification } = useNotification();

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

    useEffect(() => {
        const verificarActasCompletadas = async () => {
            for (const cargo of cargos) {
                try {
                    const response = await api.checkActaStatus(mesa.codigo, cargo.id);
                    if (response.data.exists) {
                        setActasCompletadas(prev => new Set([...prev, cargo.id]));
                    }
                } catch (error) {
                    console.error('Error verificando acta:', error);
                }
            }
        };

        if (cargos.length > 0) {
            verificarActasCompletadas();
        }
    }, [mesa.codigo, cargos]);

    const handleActaEnviada = (cargoId) => {
        setActasCompletadas(prev => new Set([...prev, cargoId]));
        showSuccess('Acta enviada correctamente');
    };

    return (
        <AppLayout title={`Acta - Mesa ${mesa.codigo}`}>
            <Head title={`Acta - Mesa ${mesa.codigo}`} />
            <NotificationContainer 
                notifications={notifications} 
                onRemove={removeNotification} 
            />

            <div className="max-w-4xl mx-auto">
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div className="flex items-center justify-between mb-6">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-800 dark:text-white">
                                Carga de Actas
                            </h1>
                            <p className="text-gray-600 dark:text-gray-400">
                                Mesa: {mesa.codigo} | Recinto: {mesa.recinto?.nombre}
                            </p>
                        </div>
                        <div className={`px-3 py-1 rounded-full text-sm font-medium ${
                            isOnline 
                                ? 'bg-green-100 text-green-800' 
                                : 'bg-red-100 text-red-800'
                        }`}>
                            {isOnline ? '🟢 Online' : '🔴 Offline'}
                        </div>
                    </div>

                    <div className="space-y-6">
                        {cargos.map(cargo => (
                            <div key={cargo.id} className="border rounded-lg p-4">
                                <div className="flex items-center justify-between mb-4">
                                    <h2 className="text-lg font-semibold text-gray-800 dark:text-white">
                                        {cargo.nombre}
                                    </h2>
                                    {actasCompletadas.has(cargo.id) && (
                                        <span className="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                                            ✓ Completado
                                        </span>
                                    )}
                                </div>
                                
                                {!actasCompletadas.has(cargo.id) && (
                                    <ActaForm
                                        mesa={mesa}
                                        cargo={cargo}
                                        partidos={partidos}
                                        onEnviada={() => handleActaEnviada(cargo.id)}
                                    />
                                )}
                            </div>
                        ))}
                    </div>

                    <div className="mt-6">
                        <Link
                            href="/campo"
                            className="inline-block bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors"
                        >
                            ← Volver al inicio
                        </Link>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
```

---

## 12. ActaForm.jsx (Componente Complejo)

```jsx
// resources/js/Components/ActaForm.jsx
import React, { useState, useEffect, useCallback } from 'react';
import { useImageCompression } from '@/Hooks/useImageCompression';
import { useOfflineSync } from '@/Hooks/useOfflineSync';
import api from '@/Services/api';

export default function ActaForm({ mesa, cargo, partidos, onEnviada }) {
    const [form, setForm] = useState({
        mesa_codigo: mesa.codigo,
        cargo_id: cargo.id,
        observaciones: '',
        votos: partidos.map(p => ({ partido_id: p.id, votos: '' })),
    });
    
    const [imagenFrente, setImagenFrente] = useState(null);
    const [imagenReverso, setImagenReverso] = useState(null);
    const [imagenFrentePreview, setImagenFrentePreview] = useState('');
    const [imagenReversoPreview, setImagenReversoPreview] = useState('');
    const [loading, setLoading] = useState(false);
    const [compressing, setCompressing] = useState(false);
    const [errors, setErrors] = useState({});
    
    const { compressImage } = useImageCompression();
    const { addPendingActa, isOnline } = useOfflineSync();

    // Load draft from localStorage
    useEffect(() => {
        const draftKey = `acta_draft_${mesa.codigo}_${cargo.id}`;
        const draft = localStorage.getItem(draftKey);
        if (draft) {
            try {
                const parsed = JSON.parse(draft);
                setForm(prev => ({
                    ...prev,
                    observaciones: parsed.observaciones || '',
                    votos: parsed.votos || prev.votos,
                }));
            } catch (e) {
                console.error('Error loading draft:', e);
            }
        }
    }, [mesa.codigo, cargo.id, partidos]);

    // Save draft to localStorage
    const saveDraft = useCallback(() => {
        const draftKey = `acta_draft_${mesa.codigo}_${cargo.id}`;
        localStorage.setItem(draftKey, JSON.stringify({
            observaciones: form.observaciones,
            votos: form.votos,
        }));
    }, [form, mesa.codigo, cargo.id]);

    // Auto-save draft every 30 seconds
    useEffect(() => {
        const interval = setInterval(saveDraft, 30000);
        return () => clearInterval(interval);
    }, [saveDraft]);

    const handleImageUpload = async (e, lado) => {
        const file = e.target.files[0];
        if (!file) return;

        // Validate file type
        if (!file.type.startsWith('image/')) {
            setErrors(prev => ({ ...prev, [lado]: 'El archivo debe ser una imagen' }));
            return;
        }

        // Validate file size (max 10MB before compression)
        if (file.size > 10 * 1024 * 1024) {
            setErrors(prev => ({ ...prev, [lado]: 'La imagen es demasiado grande (max 10MB)' }));
            return;
        }

        setCompressing(true);
        setErrors(prev => ({ ...prev, [lado]: null }));

        try {
            // Compress image
            const compressed = await compressImage(file, 1920, 0.75);
            
            // Create preview
            const reader = new FileReader();
            reader.onload = (event) => {
                if (lado === 'frente') {
                    setImagenFrente(compressed);
                    setImagenFrentePreview(event.target.result);
                } else {
                    setImagenReverso(compressed);
                    setImagenReversoPreview(event.target.result);
                }
            };
            reader.readAsDataURL(compressed);
        } catch (error) {
            setErrors(prev => ({ ...prev, [lado]: 'Error al procesar la imagen' }));
        } finally {
            setCompressing(false);
        }
    };

    const validarFormulario = () => {
        const newErrors = {};

        // Validate images
        if (!imagenFrente) {
            newErrors.frente = 'La imagen del frente es requerida';
        }
        if (!imagenReverso) {
            newErrors.reverso = 'La imagen del reverso es requerida';
        }

        // Validate votes
        let totalVotos = 0;
        form.votos.forEach((voto, index) => {
            const numVotos = parseInt(voto.votos) || 0;
            if (numVotos < 0) {
                newErrors[`voto_${index}`] = 'Los votos no pueden ser negativos';
            }
            totalVotos += numVotos;
        });

        if (totalVotos === 0) {
            newErrors.general = 'Debe ingresar al menos un voto';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const enviarActa = async () => {
        if (!validarFormulario()) return;

        setLoading(true);

        try {
            const formData = new FormData();
            formData.append('mesa_codigo', form.mesa_codigo);
            formData.append('cargo_id', form.cargo_id);
            formData.append('observaciones', form.observaciones);
            formData.append('imagen_frente', imagenFrente);
            formData.append('imagen_reverso', imagenReverso);
            formData.append('votos', JSON.stringify(form.votos));

            if (isOnline) {
                // Send directly to API
                await api.sendActa(formData);
            } else {
                // Save for later sync
                const actaData = {
                    ...form,
                    imagen_frente: imagenFrente,
                    imagen_reverso: imagenReverso,
                };
                await addPendingActa(actaData);
            }

            // Clear draft
            const draftKey = `acta_draft_${mesa.codigo}_${cargo.id}`;
            localStorage.removeItem(draftKey);

            // Reset form
            setForm({
                mesa_codigo: mesa.codigo,
                cargo_id: cargo.id,
                observaciones: '',
                votos: partidos.map(p => ({ partido_id: p.id, votos: '' })),
            });
            setImagenFrente(null);
            setImagenReverso(null);
            setImagenFrentePreview('');
            setImagenReversoPreview('');

            onEnviada();
        } catch (error) {
            const message = error.response?.data?.message || 'Error al enviar acta';
            setErrors(prev => ({ ...prev, general: message }));
        } finally {
            setLoading(false);
        }
    };

    const handleVotoChange = (index, value) => {
        const newVotos = [...form.votos];
        newVotos[index].votos = value.replace(/\D/g, '');
        setForm(prev => ({ ...prev, votos: newVotos }));
    };

    return (
        <div className="space-y-4">
            {/* Error general */}
            {errors.general && (
                <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {errors.general}
                </div>
            )}

            {/* Imágenes */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Imagen Frente del Acta *
                    </label>
                    <div className="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                        {imagenFrentePreview ? (
                            <div className="relative">
                                <img 
                                    src={imagenFrentePreview} 
                                    alt="Frente" 
                                    className="max-h-48 mx-auto rounded"
                                />
                                <button
                                    onClick={() => {
                                        setImagenFrente(null);
                                        setImagenFrentePreview('');
                                    }}
                                    className="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 text-sm hover:bg-red-600"
                                >
                                    ×
                                </button>
                            </div>
                        ) : (
                            <label className="cursor-pointer block">
                                <span className="text-gray-500">
                                    {compressing ? 'Comprimiendo...' : 'Click para subir imagen'}
                                </span>
                                <input
                                    type="file"
                                    accept="image/*"
                                    onChange={(e) => handleImageUpload(e, 'frente')}
                                    className="hidden"
                                    disabled={compressing}
                                />
                            </label>
                        )}
                    </div>
                    {errors.frente && (
                        <p className="text-red-500 text-sm mt-1">{errors.frente}</p>
                    )}
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Imagen Reverso del Acta *
                    </label>
                    <div className="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                        {imagenReversoPreview ? (
                            <div className="relative">
                                <img 
                                    src={imagenReversoPreview} 
                                    alt="Reverso" 
                                    className="max-h-48 mx-auto rounded"
                                />
                                <button
                                    onClick={() => {
                                        setImagenReverso(null);
                                        setImagenReversoPreview('');
                                    }}
                                    className="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 text-sm hover:bg-red-600"
                                >
                                    ×
                                </button>
                            </div>
                        ) : (
                            <label className="cursor-pointer block">
                                <span className="text-gray-500">
                                    {compressing ? 'Comprimiendo...' : 'Click para subir imagen'}
                                </span>
                                <input
                                    type="file"
                                    accept="image/*"
                                    onChange={(e) => handleImageUpload(e, 'reverso')}
                                    className="hidden"
                                    disabled={compressing}
                                />
                            </label>
                        )}
                    </div>
                    {errors.reverso && (
                        <p className="text-red-500 text-sm mt-1">{errors.reverso}</p>
                    )}
                </div>
            </div>

            {/* Votos por partido */}
            <div>
                <h3 className="text-lg font-medium mb-4 text-gray-800 dark:text-white">
                    Votos por Partido
                </h3>
                <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    {partidos.map((partido, index) => (
                        <div key={partido.id}>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {partido.sigla || partido.nombre}
                            </label>
                            <input
                                type="text"
                                value={form.votos[index]?.votos || ''}
                                onChange={(e) => handleVotoChange(index, e.target.value)}
                                className="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                placeholder="0"
                            />
                            {errors[`voto_${index}`] && (
                                <p className="text-red-500 text-xs mt-1">
                                    {errors[`voto_${index}`]}
                                </p>
                            )}
                        </div>
                    ))}
                </div>
            </div>

            {/* Observaciones */}
            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Observaciones
                </label>
                <textarea
                    value={form.observaciones}
                    onChange={(e) => setForm(prev => ({ ...prev, observaciones: e.target.value }))}
                    className="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    rows="3"
                    placeholder="Observaciones opcionales..."
                />
            </div>

            {/* Submit button */}
            <div className="flex items-center justify-between">
                <button
                    onClick={enviarActa}
                    disabled={loading || compressing}
                    className="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition-colors font-medium"
                >
                    {loading ? 'Enviando...' : compressing ? 'Comprimiendo...' : 'Enviar Acta'}
                </button>
                
                {!isOnline && (
                    <span className="text-yellow-600 text-sm">
                        Se guardará para sincronizar luego
                    </span>
                )}
            </div>
        </div>
    );
}
```

---

## ✅ Checklist de Migración de Componentes de Campo

- [ ] ThemeToggle.jsx funciona
- [ ] useTheme.js funciona con persistencia
- [ ] AppLayout.jsx renderiza correctamente
- [ ] useNotification.js muestra notificaciones
- [ ] useImageCompression.js comprime imágenes
- [ ] api.js conecta con backend
- [ ] storage.js funciona con IndexedDB
- [ ] useOfflineSync.js sincroniza correctamente
- [ ] SyncStatus.jsx muestra estado correcto
- [ ] Home.jsx busca mesas
- [ ] Acta.jsx lista cargos
- [ ] ActaForm.jsx envía actas (online y offline)
- [ ] PWA funciona con React
- [ ] Navegación entre páginas funciona
- [ ] Tema oscuro/claro funciona

---

## 🚀 Próximo Paso

Una vez que todos los componentes de campo funcionen, procede a migrar el Dashboard de Resultados Electorales:

[04-MIGRACION-DASHBOARD.md](./04-MIGRACION-DASHBOARD.md) - Migrar dashboard 05-dashboard-resultados-eleccion

---

**Tiempo estimado:** 4-6 horas  
**Complejidad:** Media-Alta  
**Dependencias:** Setup de React completado
