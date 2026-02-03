/**
 * Map - Clase global para manejo modular de mapas Leaflet
 * Evita código repetido en browse, edit-add, read y mapa-geografia
 */
class SintoniaMap {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.options = {
            center: options.center || [-16.290154, -63.588653],
            zoom: options.zoom || 13,
            zoomControl: options.zoomControl !== false,
            attributionControl: options.attributionControl !== false,
            scrollWheelZoom: options.scrollWheelZoom !== false,
            dragging: options.dragging !== false,
            ...options
        };
        this.map = null;
        this.markers = [];
        this.clusterGroup = null;
    }

    /**
     * Inicializa el mapa
     */
    init() {
        const container = document.getElementById(this.containerId);
        if (!container) {
            console.error(`Contenedor #${this.containerId} no encontrado`);
            return null;
        }

        this.map = L.map(this.containerId, {
            center: this.options.center,
            zoom: this.options.zoom,
            zoomControl: this.options.zoomControl,
            attributionControl: this.options.attributionControl,
            scrollWheelZoom: this.options.scrollWheelZoom,
            dragging: this.options.dragging,
            doubleClickZoom: this.options.doubleClickZoom ?? true,
            tap: this.options.tap ?? true
        });

        // Capa base de OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);

        // Fix para el bug del "Mapa Gris"
        this.fixMapaGris();

        return this.map;
    }

    /**
     * Fix para el bug del mapa gris cuando se carga en elementos ocultos
     */
    fixMapaGris() {
        const checkVisibility = () => {
            const container = document.getElementById(this.containerId);
            if (container && container.offsetParent !== null) {
                setTimeout(() => {
                    if (this.map) {
                        this.map.invalidateSize();
                    }
                }, 300);
            }
        };

        // Verificar cuando el DOM cambie
        const observer = new MutationObserver(checkVisibility);
        observer.observe(document.body, { childList: true, subtree: true });

        // Verificar en eventos comunes
        document.addEventListener('list-loaded', checkVisibility);
        document.addEventListener('shown.bs.modal', checkVisibility);
        document.addEventListener('resize', checkVisibility);
    }

    /**
     * Crea un icono personalizado
     */
    static crearIcono(tipo, color = '#26e07f') {
        const iconos = {
            'recinto': {
                iconUrl: `https://img.icons8.com/ios-filled/50/${color.replace('#', '')}/marker.png`,
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            },
            'punto': {
                className: 'custom-div-icon',
                html: `<div style="background-color: ${color}; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 4px rgba(0,0,0,0.3);"></div>`,
                iconSize: [12, 12],
                iconAnchor: [6, 6]
            },
            'default': {
                iconUrl: 'https://img.icons8.com/ios-filled/50/ff0000/marker.png',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            }
        };

        const icono = iconos[tipo] || iconos['default'];

        if (icono.className) {
            return L.divIcon(icono);
        }
        return L.icon(icono);
    }

    /**
     * Crea contenido de popup formateado
     */
    static crearPopup(datos, titulo = null) {
        let html = '<div style="min-width: 200px; font-family: Arial, sans-serif;">';

        if (titulo) {
            html += `<h5 style="margin: 0 0 8px 0; color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px;">${titulo}</h5>`;
        }

        for (const [key, value] of Object.entries(datos)) {
            if (value !== null && value !== '') {
                const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                html += `<div style="margin-bottom: 4px;"><strong style="color: #555;">${label}:</strong> <span style="color: #333;">${value}</span></div>`;
            }
        }

        html += '</div>';
        return html;
    }

    /**
     * Agrega un marcador al mapa
     */
    agregarMarcador(lat, lng, options = {}) {
        if (!this.map) return null;

        const icono = options.icono || SintoniaMap.crearIcono('recinto');
        const marker = L.marker([lat, lng], { icon: icono }).addTo(this.map);

        if (options.popup) {
            marker.bindPopup(options.popup);
        }

        if (options.onclick) {
            marker.on('click', options.onclick);
        }

        this.markers.push(marker);
        return marker;
    }

    /**
     * Inicializa clusterización de marcadores
     */
    initClusters() {
        if (typeof L.markerClusterGroup !== 'function') {
            console.warn('Leaflet.markercluster no está cargado');
            return;
        }

        this.clusterGroup = L.markerClusterGroup({
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true,
            maxClusterRadius: 80,
            iconCreateFunction: function(cluster) {
                const childCount = cluster.getChildCount();
                let c = ' marker-cluster-';
                if (childCount < 10) {
                    c += 'small';
                } else if (childCount < 100) {
                    c += 'medium';
                } else {
                    c += 'large';
                }

                return new L.DivIcon({
                    html: '<div><span>' + childCount + '</span></div>',
                    className: 'marker-cluster' + c,
                    iconSize: new L.Point(40, 40)
                });
            }
        });

        this.map.addLayer(this.clusterGroup);
    }

    /**
     * Agrega marcador al grupo de clusters
     */
    agregarMarcadorCluster(lat, lng, options = {}) {
        if (!this.clusterGroup) {
            console.warn('Cluster no inicializado. Llama a initClusters() primero');
            return this.agregarMarcador(lat, lng, options);
        }

        const icono = options.icono || SintoniaMap.crearIcono('recinto');
        const marker = L.marker([lat, lng], { icon: icono });

        if (options.popup) {
            marker.bindPopup(options.popup);
        }

        this.clusterGroup.addLayer(marker);
        return marker;
    }

    /**
     * Centra el mapa en coordenadas
     */
    centrarEn(lat, lng, zoom = null) {
        if (this.map) {
            this.map.setView([lat, lng], zoom || this.map.getZoom());
        }
    }

    /**
     * Ajusta el mapa para mostrar todos los marcadores
     */
    ajustarATodosLosMarcadores() {
        if (this.markers.length > 0 && this.map) {
            const group = new L.featureGroup(this.markers);
            this.map.fitBounds(group.getBounds().pad(0.1));
        }
    }

    /**
     * Limpia todos los marcadores
     */
    limpiarMarcadores() {
        if (this.clusterGroup) {
            this.clusterGroup.clearLayers();
        }

        this.markers.forEach(marker => {
            if (this.map && marker) {
                this.map.removeLayer(marker);
            }
        });
        this.markers = [];
    }

    /**
     * Destruye el mapa
     */
    destruir() {
        this.limpiarMarcadores();
        if (this.map) {
            this.map.remove();
            this.map = null;
        }
    }
}

// Configuración global de colores y estilos (mantener compatibilidad)
window.MapaConfig = {
    bolivia: {
        lat: -16.290154,
        lon: -63.588653,
        zoom: 6
    },

    // Colores dinámicos desde OrganizacionPolitica
    coloresPartidos: {
        'MAS': '#009739',
        'CC': '#0066cc',
        'FPV': '#ffcc00',
        'OTRO': '#666666'
    },

    estiloPoligono: function(feature) {
        return {
            fillColor: feature.properties.color || '#cccccc',
            weight: 2,
            opacity: 1,
            color: 'white',
            dashArray: '3',
            fillOpacity: 0.7
        };
    },

    estiloPoligonoHover: {
        weight: 3,
        color: '#666',
        dashArray: '',
        fillOpacity: 0.9
    },

    // Métodos legacy - delegados a SintoniaMap para mantener compatibilidad
    crearIcono: function(tipo, color) {
        return SintoniaMap.crearIcono(tipo, color);
    },

    crearPopup: function(datos, titulo) {
        return SintoniaMap.crearPopup(datos, titulo);
    }
};
