# Migraciones de Base de Datos

## Migraciones del Sistema

El sistema incluye migraciones personalizadas para extender la funcionalidad de Laravel y Voyager.

---

## Migraciones Personalizadas

### 1. Tabla People

**Archivo:** `database/migrations/2025_04_07_092413_create_people_table.php`

**Propósito:** Crear tabla para gestión de personas (naturales y jurídicas).

**Campos:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | PK, auto-increment |
| `person_type` | enum | 'Natural', 'Jurídica', default: 'Natural' |
| `tipo_doc` | string(10) | Tipo de documento, default: 'CI' |
| `ci` | string (nullable) | Carnet de identidad |
| `ci_complemento` | string(5) (nullable) | Complemento de CI |
| `nit` | string (nullable) | NIT para personas jurídicas |
| `first_name` | string (nullable) | Primer nombre |
| `middle_name` | string (nullable) | Segundo nombre |
| `paternal_surname` | string (nullable) | Apellido paterno |
| `maternal_surname` | string (nullable) | Apellido materno |
| `legal_name` | string (nullable) | Razón social (jurídicas) |
| `birth_date` | date (nullable) | Fecha de nacimiento |
| `email` | string (unique, nullable) | Email |
| `phone` | string (nullable) | Teléfono |
| `address` | text (nullable) | Dirección |
| `gender` | enum | 'Masculino', 'Femenino' (nullable) |
| `image` | string (nullable) | URL de imagen |
| `status` | tinyInt | 1=activo, 0=inactivo, 2=pending, default: 1 |
| `estado_persona` | enum | 'Activo', 'Inactivo', 'Fallecido', default: 'Activo' |
| `created_at`, `updated_at` | timestamps | Timestamps automáticos |
| `registerUser_id` | foreignId (nullable) | Usuario que registró |
| `registerRole` | string (nullable) | Rol del usuario que registró |
| `deleted_at` | timestamp (nullable) | Soft delete |
| `deleteUser_id` | foreignId (nullable) | Usuario que eliminó |
| `deleteRole` | string (nullable) | Rol del usuario que eliminó |
| `deleteObservation` | text (nullable) | Observación de eliminación |

**Índices:**
```php
$table->unique(['tipo_doc', 'ci', 'ci_complemento']);
```
Evita duplicados de CI/NIT con complemento.

**Foreign Keys:**
```php
$table->foreignId('registerUser_id')->nullable()->constrained('users');
$table->foreignId('deleteUser_id')->nullable()->constrained('users');
```

**Soft Deletes:**
```php
$table->softDeletes();
```

---

### 2. Extensión de Tabla Users

**Archivo:** `database/migrations/2025_04_07_092414_update_user_table.php`

**Propósito:** Extender tabla de usuarios de Voyager con campos adicionales.

**Campos agregados:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `status` | smallInt | Estado del usuario, default: 1 |
| `person_id` | foreignId (nullable) | Relación con persona |
| `registerUser_id` | foreignId (nullable) | Usuario que registró |
| `registerRole` | string (nullable) | Rol del usuario que registró |
| `deleted_at` | timestamp (nullable) | Soft delete |
| `deleteUser_id` | foreignId (nullable) | Usuario que eliminó |
| `deleteRole` | string (nullable) | Rol del usuario que eliminó |
| `deleteObservation` | text (nullable) | Observación de eliminación |

**Foreign Keys:**
```php
$table->foreignId('person_id')->nullable()->constrained('people');
$table->foreignId('registerUser_id')->nullable()->constrained('users');
$table->foreignId('deleteUser_id')->nullable()->constrained('users');
```

---

## Migraciones de Voyager

Las siguientes migraciones son incluidas automáticamente por Voyager:

### Tablas del Sistema

1. **users** - Usuarios del sistema (extendida)
2. **roles** - Roles de usuario
3. **permissions** - Permisos del sistema
4. **permission_role** - Relación permiso-rol
5. **settings** - Configuraciones del sistema
6. **menus** - Menús de navegación
7. **menu_items** - Ítems de menú
8. **data_types** - Tipos de datos BREAD
9. **data_rows** - Filas de datos BREAD

### Tablas de Contenido

10. **posts** - Publicaciones (blog)
11. **pages** - Páginas
12. **categories** - Categorías
13. **post_category** - Relación post-categoría
14. **translations** - Traducciones (multilenguaje)

### Tablas de Sistema

15. **password_resets** - Restablecimiento de contraseñas
16. **failed_jobs** - Jobs fallidos
17. **personal_access_tokens** - Tokens de API (Sanctum)

---

## Migraciones Nativas de Laravel

Incluidas por defecto en Laravel:

1. **2014_10_12_000000_create_users_table.php** - Tabla base de usuarios
2. **2014_10_12_100000_create_password_resets_table.php** - Reseteo de contraseñas
3. **2019_08_19_000000_create_failed_jobs_table.php** - Jobs fallidos
4. **2019_12_14_000001_create_personal_access_tokens_table.php** - Tokens API
5. **2019_12_14_000002_update_permission_table.php** - Actualización de permisos
6. **2019_12_14_000003_update_role_table.php** - Actualización de roles

---

## Relaciones Entre Tablas

```
people (1) ←→ (1) users
  ↓                   ↓
  registerUser_id → users
  deleteUser_id → users

users (1) ←→ (N) roles
  ↓
  registerUser_id → users (auto-relación)
  deleteUser_id → users (auto-relación)

data_types (BREAD)
  ↓
  data_rows (campos BREAD)

menus
  ↓
  menu_items

posts
  ↓
  categories (N:M)
  ↓
  post_category
```

---

## Diagrama de Base de Datos (Simplificado)

```
┌─────────────────┐
│     people      │
├─────────────────┤
│ id (PK)         │
│ person_type     │
│ ci              │
│ first_name      │
│ ...             │
│ registerUser_id │──┐
│ deleteUser_id   │──┼─→ users
│ deleted_at      │  │
└─────────────────┘  │
                     │
┌─────────────────┐  │
│     users       │◄─┘
├─────────────────┤
│ id (PK)         │
│ name            │
│ email           │
│ password        │
│ person_id       │──→ people
│ role_id         │──→ roles
│ registerUser_id │──→ users (auto)
│ deleteUser_id   │──→ users (auto)
│ deleted_at      │
└─────────────────┘

┌─────────────────┐
│     roles       │
├─────────────────┤
│ id (PK)         │
│ name            │
│ display_name    │
└─────────────────┘

┌─────────────────┐
│  permissions    │
├─────────────────┤
│ id (PK)         │
│ key             │
│ table_name      │
└─────────────────┘
       ↑
       │
       │ (N:M)
       │
┌─────────────────┐
│ permission_role │
├─────────────────┤
│ permission_id   │──→ permissions
│ role_id         │──→ roles
└─────────────────┘
```

---

## Ejecutar Migraciones

### Todas las migraciones
```bash
php artisan migrate
```

### Solo migraciones específicas
```bash
php artisan migrate --path=database/migrations/2025_04_07_092413_create_people_table.php
```

### Rollback de última migración
```bash
php artisan migrate:rollback
```

### Rollback de todas las migraciones
```bash
php artisan migrate:reset
```

### Rollback y volver a migrar
```bash
php artisan migrate:refresh
```

### Rollback con seeders
```bash
php artisan migrate:fresh --seed
```

---

## Seeders

Los seeders principales del sistema:

1. **VoyagerDatabaseSeeder** - Datos básicos de Voyager
2. **VoyagerDummyDatabaseSeeder** - Datos de ejemplo
3. **UsersTableSeeder** - Usuarios de prueba
4. **RolesTableSeeder** - Roles básicos
5. **PermissionsTableSeeder** - Permisos básicos
6. **SettingsTableSeeder** - Configuraciones básicas
7. **MenusTableSeeder** - Menús básicos
8. **MenuItemsTableSeeder** - Ítems de menú

---

## Convenciones de Nomenclatura

### Tablas
- Plural en inglés: `people`, `users`, `roles`
- Snake case para nombres compuestos: `menu_items`, `permission_role`

### Campos
- Snake case: `first_name`, `register_user_id`
- FK: `{tabla}_id` (ej: `person_id`, `role_id`)

### Timestamps
- `created_at` - Fecha de creación
- `updated_at` - Fecha de actualización
- `deleted_at` - Soft delete (nullable)

### Enums
- Uso de enums para estados: `status` (0,1,2), `gender` ('Masculino', 'Femenino')

---

## Notas Importantes

1. **Soft Deletes:** Ambas tablas principales (`people`, `users`) usan soft deletes.

2. **Auditoría:** Se registran `registerUser_id` y `deleteUser_id` para auditoría.

3. **Roles de Auditoría:** Se registra también el nombre del rol (`registerRole`, `deleteRole`).

4. **Unique Constraint:** La combinación `tipo_doc`, `ci`, `ci_complemento` es única para evitar duplicados.

5. **Personas Jurídicas:** El sistema soporta ambos tipos con campos específicos (`legal_name`, `nit`).

6. **FK Nullable:** Las FK de auditoría son nullable para permitir registros sin usuario (seeders, imports).

7. **Voyager Migraciones:** Voyager incluye sus propias migraciones que se ejecutan automáticamente.

8. **Relación Users-People:** Es una relación opcional (un usuario puede no tener persona asociada).

9. **Status Dual:** Se usan dos tipos de status: numérico (`status`) y enum (`estado_persona`).

10. **Índices:** Se crean índices únicos en campos de identidad para evitar duplicados.
