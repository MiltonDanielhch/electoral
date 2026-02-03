# 📊 Diccionario de Datos: Sistema de Escrutinio Beni 2026

El modelo sigue una arquitectura de **Estrella Normalizada**, donde las tablas maestras alimentan el núcleo de la votación y el motor de triggers actualiza los resúmenes de forma asíncrona.

## 1. Módulo Geográfico y Electoral (Estructura)

### Tabla: `geografias`
Define la jerarquía territorial del departamento.

- **id_geografia** (PK, BigInt): Identificador único.
- **codigo_tse** (Char 9, Unique): Código oficial del Órgano Electoral.
- **nombre** (String 100): Nombre del territorio (ej. "Vaca Díez", "Riberalta").
- **tipo** (Enum): `['Departamento', 'Provincia', 'Municipio', 'Localidad']`.
- **parent_id** (FK): Referencia a la misma tabla (el padre jerárquico).
- **nivel_jerarquico** (TinyInt): Calculado por trigger (1=Dpto, 2=Prov, 3=Mun...).

### Tabla: `recintos`
Ubicación física donde se vota.

- **id_recinto** (PK, BigInt).
- **codigo_tse** (Char 3, Unique): Código de recinto.
- **id_geografia** (FK): Debe apuntar a una geografía de tipo 'Municipio'.
- **nombre** (String 150): Nombre de la unidad educativa o centro.
- **direccion** (String 255): Ubicación física.

### Tabla: `mesas`
La unidad mínima de conteo.

- **id_mesa** (PK, BigInt).
- **codigo_tse** (Char 11, Unique): Código único nacional de mesa.
- **id_recinto** (FK): Recinto al que pertenece.
- **estado** (Enum): `['Habilitada', 'Escrutada', 'Anulada', 'Observada']`.

## 2. Módulo de Actores Políticos

### Tabla: `organizaciones_politicas`
- **id_partido** (PK, BigInt).
- **codigo_tse** (Char 3): Sigla numérica.
- **nombre** (String 100): Nombre completo.
- **sigla** (String 10): Sigla corta.
- **color_hex** (Char 7): Color para gráficos (ej. #FF0000).
- **logo_url** (String): Ruta a la imagen del logo.

### Tabla: `cargos`
- **id_cargo** (PK, TinyInt).
- **descripcion** (String 60): (ej. "Gobernador", "Alcalde").
- **nivel** (Enum): `['D', 'P', 'M']` (Departamental, Provincial, Municipal).

### Tabla: `candidatos`
- **id_candidato** (PK, BigInt).
- **nombre_completo** (String 150).
- **id_partido** (FK).
- **id_cargo** (FK).
- **id_geografia_postulacion** (FK): Indica dónde compite (ej. un Alcalde apunta a un ID de municipio).

## 3. Módulo de Escrutinio (El Núcleo)

### Tabla: `actas_escrutinio`
Almacena el resumen de cada mesa y la prueba visual.

- **id_acta** (PK, BigInt).
- **id_mesa** (FK, Unique con id_cargo): Una mesa no puede tener dos actas para el mismo cargo.
- **id_cargo** (FK).
- **foto_frontal** (String): URL/Path de la imagen en el servidor.
- **total_sobres** (Int): Cuántas personas votaron físicamente.
- **votos_validos** (Int): Suma de votos por partidos.
- **votos_blancos/nulos/impugnados** (Int).
- **estado** (Enum): `['Pendiente', 'Digitada', 'Validada']`.
- **digitador** (String): Usuario que subió el acta.

### Tabla: `votos_x_partido`
- **id_acta** (FK).
- **id_partido** (FK).
- **votos** (Int): Cantidad de votos obtenidos.
- **PK compuesta:** `(id_acta, id_partido)`.

## 4. Módulo de Inteligencia y Caché (Optimización)

### Tabla: `resumen_votos` (Tabla de Lectura Rápida)
Actualizada automáticamente por triggers. No se inserta manualmente.

- **id_cargo** (PK).
- **id_geografia** (PK).
- **id_partido** (PK).
- **total_votos** (Int).
- **total_mesas_escrutadas** (Int).
- **porcentaje_votos** (Decimal 5,2).

### Tabla: `auditoria_actas`
- **id_auditoria** (PK).
- **id_acta** (FK).
- **campo_modificado** (String).
- **valor_anterior / valor_nuevo** (Text).
- **fecha_cambio** (Timestamp).

## 🚀 Resumen de Relaciones Críticas
- **Geografía Circular:** `geografias` depende de sí misma para crear el árbol (Dpto > Prov > Mun).
- **Validación de Totales:** `actas_escrutinio` debe tener un check donde `total_sobres = validos + blancos + nulos`.
- **Integridad:** Si se borra una mesa, se borra el acta (`onDelete cascade`).
