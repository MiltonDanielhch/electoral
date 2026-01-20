# Diagrama Entidad-Relación - Sistema de Escrutinio Beni 2026

```
╔═══════════════════════════════════════════════════════════════════════════════╗
║                      SISTEMA DE ESCRUTINIO - DER                             ║
║                    Arquitectura: Estrella Normalizada                         ║
╚═══════════════════════════════════════════════════════════════════════════════╝

┌───────────────────────────────────────────────────────────────────────────────┐
│                              MÓDULO GEOGRÁFICO                                │
└───────────────────────────────────────────────────────────────────────────────┘

     ┌──────────────────────────────────────────────────────────────┐
     │                     GEOGRAFÍAS                                │
     ├──────────────────────────────────────────────────────────────┤
     │ PK  id_geografia          BIGINT                               │
     │ UK  codigo_tse           CHAR(9)                              │
     │     nombre               VARCHAR(100)                         │
     │     tipo                 ENUM(Departamento, Provincia,       │
     │                           Municipio, Cantón, Localidad)       │
     │ FK  parent_id            BIGINT  ──────────────┐              │
     │     nivel_jerarquico     TINYINT               │              │
     └──────────────────────────────────────────────────────────────┘
                                                           │
                                                           │ (auto-relación)
                                                           │ jerárquica
                                                           │
                                                           ▼
                                              ┌────────────────────┐
                                              │    GEOGRAFÍA PADRE │
                                              │    (referencia)    │
                                              └────────────────────┘
                                                           │
                     ┌─────────────────────────────────┼───────────────────────────┐
                     │                                 │                           │
                     │ (1:N)                           │ (1:N)                     │ (1:N)
                     ▼                                 ▼                           ▼
      ┌─────────────────────────┐          ┌───────────────────┐      ┌─────────────────────┐
      │        RECINTOS         │          │      CANDIDATOS   │      │      RESUMEN_VOTOS  │
      ├─────────────────────────┤          ├───────────────────┤      ├─────────────────────┤
      │ PK  id_recinto         │          │ PK  id_candidato   │      │ PK  id_cargo        │
      │ UK  codigo_tse          │          │ FK  id_partido ────┼───┬──│ FK  id_geografia ────┼──┐
      │ FK  id_geografia ──────┼───┬──────│ FK  id_cargo ──────┼───┼──│ FK  id_partido ──────┼──┼──┐
      │     nombre              │   │      │     nombre_completo│   │  │     total_votos      │  │  │
      │     direccion           │   │      │     ci (UK)        │   │  │     total_mesas     │  │  │
      └─────────────────────────┘   │      │     estado         │   │  │     porcentaje      │  │  │
                                  │      └───────────────────┘   │  │     ultima_actualiz   │  │  │
                                  │                               │  └─────────────────────┘  │  │
                                  │ (1:N)                         │                           │  │
                                  ▼                               │                           │  │
                     ┌─────────────────────────┐                │                           │  │
                     │        MESAS             │                │                           │  │
                     ├─────────────────────────┤                │                           │  │
                     │ PK  id_mesa             │                │                           │  │
                     │ UK  codigo_tse          │                │                           │  │
                     │ FK  id_recinto ─────────┼──────┐         │                           │  │
                     │     estado              │      │         │                           │  │
                     │     deleted_at (soft)   │      │         │                           │  │
                     └─────────────────────────┘      │         │                           │  │
                                                  │         │                           │  │
                                                  │ (1:N)   │                           │  │
                                                  ▼         │                           │  │
                              ┌───────────────────────────────┐ │                           │  │
                              │      ACTAS_ESCRUTINIO          │ │                           │  │
                              ├───────────────────────────────┤ │                           │  │
                              │ PK  id_acta                   │ │                           │  │
                              │ FK  id_mesa ──────────────────┼─┼───┐                       │  │
                              │ FK  id_cargo ─────────────────┼─┼───┼───┐                   │  │
                              │ UK  codigo_acta               │ │   │   │                   │  │
                              │     foto_frontal               │ │   │   │                   │  │
                              │     foto_reverso              │ │   │   │                   │  │
                              │     total_sobres               │ │   │   │                   │  │
                              │     total_votantes             │ │   │   │                   │  │
                              │     votos_validos              │ │   │   │                   │  │
                              │     votos_blancos              │ │   │   │                   │  │
                              │     votos_nulos                │ │   │   │                   │  │
                              │     votos_impugnados           │ │   │   │                   │  │
                              │     digitador                  │ │   │   │                   │  │
                              │     estado                     │ │   │   │                   │  │
                              │     created_at                 │ │   │   │                   │  │
                              └───────────────────────────────┘ │   │   │                   │  │
                                                                │   │   │                   │  │
                                                                │ (1:N)│                   │  │
                                                                ▼     │                   │  │
                                      ┌───────────────────────────┘   │                   │  │
                                      │ VOTOS_X_PARTIDO                │                   │  │
                                      ├───────────────────────────────┤                   │  │
                                      │ PK,FK id_acta ─────────────────┼───────────────────┼──┼──┐
                                      │ PK,FK id_partido ─────────────┼───────────────────┼──┼──┼──┐
                                      │       votos                   │                   │  │  │  │
                                      └───────────────────────────────┘                   │  │  │  │
                                                                             │             │  │  │  │
                                                                             │ (1:N)       │  │  │  │
                                                                             ▼             │  │  │  │
                                                              ┌───────────────────────────────┘  │  │  │
                                                              │     AUDITORIA_ACTAS             │  │  │  │
                                                              ├─────────────────────────────────┤  │  │  │
                                                              │ PK  id_auditoria               │  │  │  │
                                                              │ FK  id_acta ───────────────────┼──┼──┼──┼──┐
                                                              │     campo_modificado           │  │  │  │  │
                                                              │     valor_anterior              │  │  │  │  │
                                                              │     valor_nuevo                 │  │  │  │  │
                                                              │     usuario                     │  │  │  │  │
                                                              │     fecha_cambio                │  │  │  │  │
                                                              └─────────────────────────────────┘  │  │  │  │
                                                                                             │  │  │  │

┌───────────────────────────────────────────────────────────────────────────────┐
│                         MÓDULO ACTORES POLÍTICOS                              │
└───────────────────────────────────────────────────────────────────────────────┘

     ┌──────────────────────────────────────────────────────────────┐
     │              ORGANIZACIONES_POLÍTICAS                        │
     ├──────────────────────────────────────────────────────────────┤
     │ PK  id_partido            BIGINT                              │
     │ UK  codigo_tse            CHAR(3)                             │
     │     nombre                VARCHAR(100)                        │
     │     sigla                 VARCHAR(10)                         │
     │     color_hex             CHAR(7)                             │
     │     logo_url              VARCHAR                             │
     │     estado                ENUM(Activo, Inactivo)             │
     └──────────────────────────────────────────────────────────────┘
                            │
               ┌────────────┴────────────┐
               │                         │
               │ (1:N)                   │ (1:N)
               ▼                         ▼
     ┌──────────────────┐      ┌──────────────────┐
     │    CANDIDATOS     │      │  VOTOS_X_PARTIDO │
     │                  │      │                  │
     └──────────────────┘      └──────────────────┘

     ┌──────────────────────────────────────────────────────────────┐
     │                     CARGOS                                    │
     ├──────────────────────────────────────────────────────────────┤
     │ PK  id_cargo              TINYINT                             │
     │     descripcion            VARCHAR(60)                        │
     │     nivel                  ENUM(D, P, M)                      │
     │     tipo_acta              ENUM(Normal, Especial)             │
     │     acta_unica             BOOLEAN                             │
     └──────────────────────────────────────────────────────────────┘
                            │
               ┌────────────┴────────────┐
               │                         │
               │ (1:N)                   │ (1:N)
               ▼                         ▼
     ┌──────────────────┐      ┌──────────────────┐
     │    CANDIDATOS     │      │  ACTAS_ESCRUTINIO│
     │                  │      │                  │
     └──────────────────┘      └──────────────────┘

┌───────────────────────────────────────────────────────────────────────────────┐
│                       MÓDULO OPTIMIZACIÓN                                     │
└───────────────────────────────────────────────────────────────────────────────┘

     ┌──────────────────────────────────────────────────────────────┐
     │              CONTROL_PROCESAMIENTO                            │
     ├──────────────────────────────────────────────────────────────┤
     │ PK  id_control            BIGINT                              │
     │     tabla_destino         VARCHAR(50)  (UK)                  │
     │     ultimo_id_procesado   BIGINT                              │
     │     fecha_ultima_actualizacion DATETIME                       │
     │     estado                ENUM(Activo, Pausado, Error)        │
     │     total_registros_procesados INT                            │
     │     ultima_ejecucion_exitosa TIMESTAMP                        │
     │     ultimo_error           TEXT                                │
     └──────────────────────────────────────────────────────────────┘

     ┌──────────────────────────────────────────────────────────────┐
     │            CACHE_RESULTADOS (ENGINE=MEMORY)                   │
     ├──────────────────────────────────────────────────────────────┤
     │ PK  cache_key             VARCHAR(100)                        │
     │     cache_data            JSON                                │
     │     expires_at            DATETIME                            │
     │     created_at            DATETIME                            │
     └──────────────────────────────────────────────────────────────┘

     ┌──────────────────────────────────────────────────────────────┐
     │        CACHE_RESULTADOS_BACKUP (ENGINE=InnoDB)               │
     ├──────────────────────────────────────────────────────────────┤
     │ PK  cache_key             VARCHAR(100)                        │
     │     cache_data            JSON                                │
     │     last_sync             DATETIME                            │
     └──────────────────────────────────────────────────────────────┘

┌───────────────────────────────────────────────────────────────────────────────┐
│                         LEYENDA                                            │
└───────────────────────────────────────────────────────────────────────────────┘

     PK  = Primary Key (Clave Primaria)
     FK  = Foreign Key (Clave Foránea)
     UK  = Unique Key (Clave Única)
     1:N = Uno a Muchos
     (1) = Cardilalidad Mínima (Opcional)
     (N) = Cardilalidad Máxima (Muchos)
     ENUM = Enumeración de valores permitidos

┌───────────────────────────────────────────────────────────────────────────────┐
│                   REGLAS DE NEGOCIO Y RESTRICCIONES                           │
└───────────────────────────────────────────────────────────────────────────────┘

     1. GEORFÍA JERÁRQUICA
        • parent_id puede ser NULL (nivel 1 = Departamento)
        • Los municipios son nivel 3 en la jerarquía
        • Trigger trg_geo_nivel_jerarquico calcula el nivel automáticamente

     2. RECINTOS
        • Solo pueden asociarse a municipios (tipo='Municipio')
        • Código TSE debe ser exactamente 3 dígitos numéricos
        • Trigger trg_recintos_validacion asegura estas reglas

     3. MESAS
        • Código TSE debe ser exactamente 11 dígitos numéricos
        • Estado: Habilitada → Escrutada | Anulada | Observada
        • Soft delete habilitado (deleted_at)
        • Trigger trg_mesas_validacion valida código

     4. ACTAS_ESCRUTINIO
        • UNIQUE(id_mesa, id_cargo) - Una mesa no puede tener dos actas para el mismo cargo
        • CHECK: votos_validos + votos_blancos + votos_nulos + votos_impugnados = total_sobres
        • Estado: Pendiente → Digitada → Validada | Observada | Cerrada
        • Fotos del acta (frontal y reverso)

     5. VOTOS_X_PARTIDO
        • PRIMARY KEY COMPUESTA(id_acta, id_partido)
        • Trigger trg_votos_validacion: suma_votos ≤ votos_validos del acta
        • CASCADE DELETE si se borra el acta

     6. CANDIDATOS
        • UNIQUE(id_cargo, id_geografia_postulacion, id_partido)
        • Un mismo partido no puede tener dos candidatos para el mismo cargo y territorio
        • Estado: Postulado → Retirado | Electo

     7. RESUMEN_VOTOS (Tabla de Lectura Rápida)
        • PRIMARY KEY COMPUESTA(id_cargo, id_geografia, id_partido)
        • Actualizado por proceso asíncrono (no manual)
        • Optimizado para consultas agregadas por territorio

     8. AUDITORIA_ACTAS
        • Trigger trg_auditoria_actas registra cambios en estado y totales
        • Solo se audita si el valor cambió realmente
        • Registra usuario que hizo el cambio

     9. CACHE Y OPTIMIZACIÓN
        • CACHE_RESULTADOS: tabla en memoria para respuestas rápidas
        • CACHE_RESULTADOS_BACKUP: respaldo en disco cada 30 segundos
        • EVENTO sync_cache_backup: sincronización automática
        • CONTROL_PROCESAMIENTO: controla procesamiento de resúmenes

┌───────────────────────────────────────────────────────────────────────────────┐
│                    FLUJO DE ACTUALIZACIÓN DE DATOS                            │
└───────────────────────────────────────────────────────────────────────────────┘

     1. MESA: Escrutada → Estado cambia a 'Escrutada'
        │
     2. ACTA: Se digita → Estado 'Digitada'
        │
     3. ACTA: Se valida → Estado 'Validada'
        │
        ├─→ TRIGGER trg_auditoria_actas (audita cambio de estado)
        │
        ├─→ TRIGGER trg_actualizar_resumen_validacion
        │      • Actualiza control_procesamiento
        │      • Inserta señal en cache_resultados
        │      • Worker asíncrono lee la señal
        │
        └─→ WORKER → Actualiza RESUMEN_VOTOS
               • Suma votos por partido
               • Calcula porcentajes
               • Cuenta mesas escrutadas
               • Actualiza ultima_actualizacion

┌───────────────────────────────────────────────────────────────────────────────┐
│                      ÍNDICES PRINCIPALES                                       │
└───────────────────────────────────────────────────────────────────────────────┘

     GEOGRAFÍAS:
        • idx_geo_tipo_parent (tipo, parent_id)
        • nivel_jerarquico
        • codigo_tse (UNIQUE)

     RECINTOS:
        • id_geografia
        • codigo_tse (UNIQUE)

     MESAS:
        • idx_mesa_recinto_estado (id_recinto, estado)
        • codigo_tse (UNIQUE)
        • estado

     ACTAS_ESCRUTINIO:
        • uk_mesa_cargo (id_mesa, id_cargo) UNIQUE
        • idx_acta_cargo_estado (id_cargo, estado)
        • estado
        • created_at

     VOTOS_X_PARTIDO:
        • id_partido
        • votos

     RESUMEN_VOTOS:
        • pk_resumen (id_cargo, id_geografia, id_partido)
        • idx_resumen_territorio (id_geografia, id_cargo)
        • idx_actualizacion_rapida (id_cargo, ultima_actualizacion)
        • porcentaje_votos

     AUDITORIA_ACTAS:
        • id_acta
        • fecha_cambio

     ORGANIZACIONES_POLÍTICAS:
        • sigla
        • estado

     CANDIDATOS:
        • uk_candidato_unico (id_cargo, id_geografia_postulacion, id_partido)
        • id_partido
        • id_geografia_postulacion

     CONTROL_PROCESAMIENTO:
        • estado

     CACHE_RESULTADOS:
        • idx_cache_expires (expires_at)

     CACHE_RESULTADOS_BACKUP:
        • idx_backup_sync (last_sync)

┌───────────────────────────────────────────────────────────────────────────────┐
│                   ESTADÍSTICAS DEL ESQUEMA                                     │
└───────────────────────────────────────────────────────────────────────────────┘

     • Total de Tablas Principales: 10
     • Tablas de Catálogo: 3 (geografias, organizaciones_politicas, cargos)
     • Tablas Transaccionales: 3 (mesas, actas_escrutinio, votos_x_partido)
     • Tablas de Audioría: 2 (auditoria_actas, control_procesamiento)
     • Tablas de Caché: 2 (cache_resultados, cache_resultados_backup)
     • Tablas de Resumen: 1 (resumen_votos)
     • Total de Triggers: 5
     • Total de Eventos: 1
     • Triggers de Validación: 3 (geo, recintos, mesas, votos)
     • Triggers de Auditoría: 1 (actas)
     • Triggers de Optimización: 1 (resumen)
     • Relaciones Uno a Muchos (1:N): 12
     • Auto-relaciones: 1 (geografias)
     • Índices Únicos: 7
     • Check Constraints: 1 (suma de votos = total_sobres)

┌───────────────────────────────────────────────────────────────────────────────┐
│                    ARQUITECTURA DE DATOS                                      │
└───────────────────────────────────────────────────────────────────────────────┘

     ESTRELLA NORMALIZADA:
        • TABLA CENTRAL: actas_escrutinio (hechos del escrutinio)
        • DIMENSIONES:
          - geografias (territorio)
          - mesas (ubicación física)
          - cargos (tipo de elección)
          - organizaciones_politicas (partidos)
          - candidatos (opciones de voto)

     OPTIMIZACIONES:
        • Tabla resumen_votos para agregaciones rápidas
        • Cache en memoria para resultados en tiempo real
        • Backup de cache cada 30 segundos
        • Índices compuestos para consultas geográficas
        • Triggers para actualizaciones automáticas

     INTEGRIDAD:
        • Foreign Keys en cascada para limpieza
        • Check constraints para consistencia matemática
        • Triggers de validación a nivel de base de datos
        • Soft deletes para preservar historial
        • Auditoría automática de cambios críticos

┌───────────────────────────────────────────────────────────────────────────────┐
│                         FIN DEL DIAGRAMA                                     │
└───────────────────────────────────────────────────────────────────────────────┘
