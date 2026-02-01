window.MapaConfig = {
    bolivia: {
        lat: -16.290154,
        lon: -63.588653,
        zoom: 6
    },

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

    crearIcono: function(tipo) {
        const iconos = {
            'recinto': {
                iconUrl: 'https://img.icons8.com/ios-filled/50/26e07f/marker.png',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            },
            'default': {
                iconUrl: 'https://img.icons8.com/ios-filled/50/ff0000/marker.png',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            }
        };

        const icono = iconos[tipo] || iconos['default'];
        return L.icon(icono);
    },

    crearPopup: function(datos) {
        let html = '<div style="min-width: 200px;">';

        for (const [key, value] of Object.entries(datos)) {
            if (value !== null && value !== '') {
                const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                html += `<strong>${label}:</strong> ${value}<br>`;
            }
        }

        html += '</div>';
        return html;
    }
};
