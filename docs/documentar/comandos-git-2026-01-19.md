# Comandos de Git Usados - 2026-01-19

Este documento documenta todos los comandos de git utilizados durante la sesión de trabajo del 19 de enero de 2026, con explicación detallada de cada uno.

---

## 📋 Índice

1. [Comandos de Información Básica](#1-comandos-de-información-básica)
2. [Comandos de Ramas (Branching)](#2-comandos-de-ramas-branching)
3. [Comandos de Cambios y Staging](#3-comandos-de-cambios-y-staging)
4. [Comandos de Commits](#4-comandos-de-commits)
5. [Comandos de Stash (Guardar Cambios Temporalmente)](#5-comandos-de-stash-guardar-cambios-temporalmente)
6. [Comandos de Cherry-pick (Traer Commits Específicos)](#6-comandos-de-cherry-pick-traer-commits-específicos)
7. [Comandos de Diferencias (Diff)](#7-comandos-de-diferencias-diff)
8. [Comandos de Visualización (Show/Log)](#8-comandos-de-visualización-showlog)
9. [Comandos de Remoto (Remote)](#9-comandos-de-remoto-remote)

---

## 1. Comandos de Información Básica

### `git status`
**Uso:** Ver el estado actual del repositorio (archivos modificados, nuevos, sin seguimiento).

**Ejemplo:**
```bash
git status
```

**Salida típica:**
```
On branch electoral
Changes to be committed:
  (use "git restore --staged <file>..." to unstage)
  modified:   resources/views/people/list.blade.php

Changes not staged for commit:
  (use "git add <file>..." to update what will be be committed)
  modified:   resources/views/users/list.blade.php

Untracked files:
  (use "git add <file>..." to include in what will be be committed)
  nuevo-archivo.txt
```

---

### `git branch --show-current`
**Uso:** Mostrar el nombre de la rama actual.

**Ejemplo:**
```bash
git branch --show-current
```

**Salida:**
```
electoral
```

---

### `git branch --all`
**Uso:** Listar todas las ramas (locales y remotas).

**Ejemplo:**
```bash
git branch -a
```

**Salida típica:**
```
* electoral
  main
  panel
  remotes/origin/electoral
  remotes/origin/main
  remotes/origin/panel
```

---

### `git remote -v`
**Uso:** Ver los repositorios remotos configurados.

**Ejemplo:**
```bash
git remote -v
```

**Salida:**
```
origin  https://github.com/MiltonDanielhch/electoral.git (fetch)
origin  https://github.com/MiltonDanielhch/electoral.git (push)
```

---

## 2. Comandos de Ramas (Branching)

### `git checkout -b <nombre-rama>`
**Uso:** Crear y cambiar a una nueva rama.

**Ejemplo:**
```bash
git checkout -b probar-fase1-panel
```

**Explicación:** Crea una nueva rama llamada `probar-fase1-panel` y se mueve a ella automáticamente. Equivalente a:
```bash
git branch probar-fase1-panel
git checkout probar-fase1-panel
```

---

### `git checkout <nombre-rama>`
**Uso:** Cambiar a una rama existente.

**Ejemplo:**
```bash
git checkout panel
```

**Explicación:** Mueve el HEAD a la rama `panel` y actualiza el working directory.

---

### `git checkout <file>`
**Uso:** Revertir cambios en un archivo específico a su estado en el último commit.

**Ejemplo:**
```bash
git restore composer.json
```

**Explicación:** Revierte todos los cambios no commiteados en `composer.json`.

---

## 3. Comandos de Cambios y Staging

### `git add <archivo>`
**Uso:** Agregar un archivo al área de staging (preparar para commit).

**Ejemplo:**
```bash
git add resources/views/people/list.blade.php
```

**Explicación:** Mueve `list.blade.php` de "Changes not staged" a "Changes to be committed".

---

### `git add -A`
**Uso:** Agregar todos los cambios (modificados, nuevos, eliminados) al staging.

**Ejemplo:**
```bash
git add -A
```

**Explicación:** Equivalente a `git add .` más `git add -u`. Agrega todo.

---

### `git add <archivo1> <archivo2> ...`
**Uso:** Agregar múltiples archivos específicos al staging.

**Ejemplo:**
```bash
git add resources/views/administrations/people/list.blade.php resources/views/vendor/voyager/users/list.blade.php
```

---

## 4. Comandos de Commits

### `git commit -m "mensaje"`
**Uso:** Crear un commit con un mensaje corto.

**Ejemplo:**
```bash
git commit -m "feat: Cambiar color de tablas"
```

---

### `git commit -m "$(cat <<'EOF'`
**Uso:** Crear un commit con un mensaje multilínea (usando HEREDOC).

**Ejemplo:**
```bash
git commit -m "$(cat <<'EOF'
feat: Optimizar rendimiento AJAX en panel admin

Mejoras implementadas:
- Reducir delay búsqueda: 2000ms → 500ms (75% más rápido)
- Prevenir peticiones múltiples con flag isLoading
- Agregar timeout de 10s para evitar peticiones colgadas

Impacto:
- Búsqueda 4x más rápida
- Prevención de múltiples peticiones simultáneas
EOF
)"
```

**Explicación:** Permite escribir mensajes de commit largos y con formato directamente en la terminal.

---

## 5. Comandos de Stash (Guardar Cambios Temporalmente)

### `git stash push -u -m "mensaje"`
**Uso:** Guardar cambios temporalmente (incluso archivos no rastreados).

**Ejemplo:**
```bash
git stash push -u -m "WIP: Guardar cambios de color en tablas"
```

**Explicación:**
- `-u` o `--include-untracked`: Incluye archivos nuevos
- `-m "mensaje"`: Agrega un mensaje descriptivo al stash

**Salida típica:**
```
Saved working directory and index state On electoral: WIP: Guardar cambios de color en tablas
```

---

### `git stash list`
**Uso:** Listar todos los stashes guardados.

**Ejemplo:**
```bash
git stash list
```

**Salida:**
```
stash@{0}: On panel: WIP: Guardar plan.md antes de probar
stash@{1}: On electoral: WIP: Cambios de color en tablas
```

---

### `git stash pop`
**Uso:** Recuperar el último stash y eliminarlo de la lista.

**Ejemplo:**
```bash
git stash pop
```

**Explicación:** Aplica los cambios del último stash y lo elimina. Equivalente a `git stash apply` + `git stash drop`.

**Salida:**
```
Dropped refs/stash@{0} (0e46652c11b1be93bc710646dc7303fc3c879638)
```

---

## 6. Comandos de Cherry-pick (Traer Commits Específicos)

### `git cherry-pick <hash-commit>`
**Uso:** Aplicar un commit específico a la rama actual.

**Ejemplo:**
```bash
git cherry-pick e0748ec
```

**Explicación:** Toma los cambios del commit `e0748ec` y los aplica a la rama actual, creando un nuevo commit con el mismo mensaje.

---

### `git cherry-pick <hash1> <hash2>`
**Uso:** Aplicar múltiples commits específicos en orden.

**Ejemplo:**
```bash
git cherry-pick 8bf0ec3 d74c7ee
```

**Explicación:** Aplica primero `8bf0ec3`, luego `d74c7ee`. Si uno falla, se detiene.

---

### `git cherry-pick --continue`
**Uso:** Continuar un cherry-pick que fue interrumpido.

**Ejemplo:**
```bash
git cherry-pick --continue
```

**Explicación:** Se usa después de resolver conflictos de cherry-pick o eliminar locks.

---

### `git cherry-pick --abort`
**Uso:** Abortar un cherry-pick en progreso y volver al estado anterior.

**Ejemplo:**
```bash
git cherry-pick --abort
```

---

## 7. Comandos de Diferencias (Diff)

### `git diff`
**Uso:** Ver diferencias entre archivos modificados y el staging.

**Ejemplo:**
```bash
git diff
```

**Explicación:** Muestra cambios no commiteados y no staged.

---

### `git diff <rama1> <rama2>`
**Uso:** Ver diferencias entre dos ramas.

**Ejemplo:**
```bash
git diff panel electoral
```

**Explicación:** Muestra todas las diferencias entre la rama `panel` y la rama `electoral`.

---

### `git diff <rama1> <rama2> -- <archivo>`
**Uso:** Ver diferencias en archivos específicos entre dos ramas.

**Ejemplo:**
```bash
git diff panel electoral -- resources/views/administrations/people/list.blade.php resources/views/vendor/voyager/users/list.blade.php
```

**Explicación:** Solo muestra diferencias en los archivos especificados, no en todo el proyecto.

---

### `git diff HEAD~1 <archivo>`
**Uso:** Ver diferencias con el commit anterior en un archivo específico.

**Ejemplo:**
```bash
git diff HEAD~1 resources/views/vendor/voyager/users/list.blade.php | head -40
```

**Explicación:**
- `HEAD~1`: Commit anterior al actual
- `| head -40`: Muestra solo las primeras 40 líneas

---

### `git diff <archivo>`
**Uso:** Ver cambios no commiteados en un archivo específico.

**Ejemplo:**
```bash
git diff resources/views/administrations/people/list.blade.php
```

---

### `git diff --stat`
**Uso:** Ver resumen estadístico de cambios.

**Ejemplo:**
```bash
git diff --stat
```

**Salida típica:**
```
resources/views/people/list.blade.php | 12 ++++++++++++
resources/views/users/list.blade.php    | 10 ++++++----
2 files changed, 22 insertions(+), 4 deletions(-)
```

---

## 8. Comandos de Visualización (Show/Log)

### `git log`
**Uso:** Ver historial de commits.

**Ejemplo:**
```bash
git log
```

---

### `git log --oneline`
**Uso:** Ver historial en una línea por commit (solo hash y mensaje).

**Ejemplo:**
```bash
git log --oneline
```

**Salida:**
```
404791d docs: Agregar registro de actividades 2026-01-19
55a4163 feat: Cambiar color de encabezado tablas a verde (#28a745)
5ca501e feat: Agregar estilo verde a tabla de usuarios
0fdf7e5 feat: Optimizar rendimiento AJAX en panel admin (Fase 1)
698987a plan electoral
```

---

### `git log -<número>`
**Uso:** Ver los últimos N commits.

**Ejemplo:**
```bash
git log -3
```

**Explicación:** Muestra solo los 3 commits más recientes.

---

### `git log --oneline -<número>`
**Uso:** Ver los últimos N commits en formato compacto.

**Ejemplo:**
```bash
git log --oneline -5
```

---

### `git show <hash-commit>`
**Uso:** Ver detalles completos de un commit específico.

**Ejemplo:**
```bash
git show e0748ec
```

**Salida:** Muestra hash, autor, fecha, mensaje completo, y diff de archivos.

---

### `git show --name-status HEAD`
**Uso:** Ver el último commit con solo nombres de archivos y estado.

**Ejemplo:**
```bash
git show --name-status HEAD
```

**Salida:**
```
commit e0748ec72f50ff30afc55046b70828b82d17e528
Author: milton <miltondanielhch617@gmail.com>
Date:   Mon Jan 19 13:42:48 2026 -0400

M	app/Http/Controllers/PersonController.php
M	app/Http/Controllers/UserController.php
A	docs/documentar/optimizacion-fase1.md
```

---

### `git show <hash>:<archivo>`
**Uso:** Ver contenido de un archivo en un commit específico.

**Ejemplo:**
```bash
git show e0748ec:resources/views/administrations/people/list.blade.php | head -20
```

**Explicación:**
- `e0748ec`: Hash del commit
- `:`: Separador entre hash y archivo
- `| head -20`: Muestra solo las primeras 20 líneas

---

## 9. Comandos de Remoto (Remote)

### `git push origin <rama>`
**Uso:** Subir commits al repositorio remoto.

**Ejemplo:**
```bash
git push origin electoral
```

**Explicación:** Sube todos los commits pendientes de la rama local `electoral` a la rama remota `origin/electoral`.

**Salida típica:**
```
Enumerating objects: 8, done.
Counting objects: 100% (8/8), done.
Delta compression using up to 8 threads
Compressing objects: 100% (6/6), done.
Writing objects: 100% (8/8), 2.34 KiB | 2.34 MiB/s, done.
Total 8 (delta 2), reused 0 (delta 0), pack-reused 0
To https://github.com/MiltonDanielhch/electoral.git
   698987a..404791d  electoral -> electoral
```

---

### `git ls-remote --heads origin`
**Uso:** Listar todas las ramas en el remoto.

**Ejemplo:**
```bash
git ls-remote --heads origin
```

**Salida:**
```
404791d7d43e4338f12632958baff9390b99997f	refs/heads/electoral
b856c7cb3a0030dae78dcc83ca5e257adaa199a3	refs/heads/main
18b28e3e620df86032962b9e6fceaf11b0d8ae94	refs/heads/panel
```

---

### `git pull origin <rama>`
**Uso:** Traer cambios del remoto y fusionarlos.

**Ejemplo:**
```bash
git pull origin main
```

**Explicación:** Equivalente a `git fetch origin main` + `git merge origin/main`.

---

### `git fetch origin`
**Uso:** Traer cambios del remoto sin fusionarlos.

**Ejemplo:**
```bash
git fetch origin
```

**Explicación:** Actualiza las referencias remotas (`origin/main`, `origin/panel`, etc.) sin cambiar el working directory.

---

## 🎯 Flujo Completo Ejecutado Hoy

### 1. Verificación Inicial
```bash
git status                              # Ver estado del repositorio
git branch --show-current                # Ver rama actual (panel)
git log --oneline -3                   # Ver últimos commits
```

### 2. Crear Rama de Prueba
```bash
git checkout -b probar-fase1-panel       # Crear rama para pruebas
```

### 3. Guardar Cambios Temporalmente
```bash
git stash push -u -m "WIP: Guardar cambios"  # Guardar en stash
```

### 4. Cambiar entre Ramas
```bash
git checkout panel                        # Ir a rama panel
git checkout electoral                     # Ir a rama electoral
```

### 5. Ver Diferencias
```bash
git diff panel electoral                  # Comparar ramas
git diff HEAD~1 resources/views/people/list.blade.php | head -40
```

### 6. Preparar y Commitear Cambios
```bash
git add resources/views/people/list.blade.php resources/views/users/list.blade.php
git commit -m "feat: Cambios en tablas"
```

### 7. Ver Resultados de Commits
```bash
git show --name-status HEAD              # Ver último commit
git log --oneline -5                    # Ver historial
```

### 8. Llevar Commits entre Ramas
```bash
git checkout electoral                     # Ir a rama destino
git cherry-pick e0748ec                 # Traer commit específico
git cherry-pick 8bf0ec3 d74c7ee      # Traer múltiples commits
```

### 9. Resolver Errores de Cherry-pick
```bash
rm -f .git/index.lock                   # Eliminar lock manual
git cherry-pick --continue              # Continuar cherry-pick
```

### 10. Verificar Estado Final
```bash
git status                              # Ver que todo esté limpio
git log --oneline -5                    # Ver commits locales
```

### 11. Subir al Remoto
```bash
git remote -v                            # Ver remotos configurados
git push origin electoral                 # Subir cambios
git ls-remote --heads origin            # Ver ramas en remoto
```

---

## 📚 Conceptos Clave Aprendidos

### Staging Area
Es el área intermedia entre el working directory y el repositorio. Los archivos en staging están listos para ser commiteados.

### HEAD
Puntero que indica el commit actual. `HEAD~1` es el commit anterior, `HEAD~2` es el segundo anterior, etc.

### Cherry-pick
Permite seleccionar commits específicos de una rama y aplicarlos en otra, sin tener que fusionar toda la rama.

### Stash
Permite guardar temporalmente cambios no commiteados y recuperarlos después. Útil para cambiar de rama sin perder trabajo.

### Remote
Repositorio externo (GitHub, GitLab, etc.) donde se comparte el código. `origin` es el nombre por defecto del primer remoto.

---

## 💡 Tips y Buenas Prácticas

1. **Siempre verifica el estado antes de hacer commit:**
   ```bash
   git status
   ```

2. **Usa mensajes de commit claros y descriptivos:**
   - `feat:` para nuevas funcionalidades
   - `fix:` para correcciones de bugs
   - `docs:` para documentación

3. **Verifica qué estás por commitear:**
   ```bash
   git diff --staged
   ```

4. **Usa branches para experimentar:**
   - No commiteas directamente en main
   - Puedes descartar cambios fácilmente

5. **Haz push frecuente:**
   - Evita perder código
   - Facilita colaboración

6. **Usa cherry-pick selectivamente:**
   - No llevas commits genéricos de una rama a otra
   - Solo llevas lo específico que necesitas

---

## 🔗 Recursos Adicionales

- [Documentación oficial de Git](https://git-scm.com/doc)
- [Git Cheat Sheet](https://education.github.com/git-cheat-sheet-education.pdf)
- [Git Flow](https://nvie.com/posts/a-successful-git-branching-model/)

---

**Fecha de creación:** 2026-01-19  
**Autor:** Registro de sesión de trabajo
