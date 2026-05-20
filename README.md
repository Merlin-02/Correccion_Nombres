# Corrector de Acentos para Nombres Hispanos

Corrige acentos ortográficos en nombres propios del español usando un diccionario en MySQL y Hunspell como respaldo.

## Requisitos

- PHP 8.1+
- MySQL/MariaDB
- Hunspell con diccionario `es_MX` (opcional, para fallback)

## Setup

```bash
# 1. Crear BD y tabla
mysql -u root -p < database.sql

# 2. Poblar la tabla dictionary
php api/migrate.php

# 3. Verificar
mysql -u root -p -e "USE nombres_db; SELECT COUNT(*) FROM dictionary;"
```

## Uso CLI

```bash
php correct.php --nombres="JOSE" --apellidos="GARCIA LOPEZ"

# Con opciones
php correct.php \
  --nombres="kevin merlin" \
  --apellidos="cabrera coyotzi" \
  --orden=apellidos_nombres \
  --formato=Capitalizado
```

### Parámetros

| Parámetro   | Valores                                       | Default              |
|-------------|-----------------------------------------------|----------------------|
| `--nombres`  | texto                                         | (requerido)          |
| `--apellidos` | texto                                       | vacío                |
| `--orden`    | `nombres_apellidos` / `apellidos_nombres`     | `nombres_apellidos`  |
| `--formato`  | `MAYUSCULAS` / `minusculas` / `Capitalizado` | `MAYUSCULAS`         |

## Uso como librería

```php
require 'correct.php';
$c = new Corrector();

$result = $c->correctStructured(
    nombres: 'JOSE',
    apellidos: 'GARCIA LOPEZ',
    orden: 'nombres_apellidos',
    formato: 'Capitalizado'
);
// $result['corrected'] → "José García López"
```

## API HTTP

```bash
# Iniciar servidor
php -S localhost:8000 backend/router.php

# Consultar
curl "http://localhost:8000/api/correct?nombres=JOSE&apellidos=GARCIA&formato=minusculas&orden=apellidos_nombres"
```

Respuesta:
```json
{
  "original": "GARCIA JOSE",
  "corrected": "garcía josé",
  "method": "dictionary",
  "changes": [
    { "from": "GARCIA", "to": "GARCÍA" },
    { "from": "JOSE", "to": "JOSÉ" }
  ]
}
```

## Frontend web

```
php -S localhost:8000 backend/router.php
# Abrir http://localhost:8000          (corrector)
#      http://localhost:8000/diccionario (admin diccionario)
```

## Administrar diccionario

Desde `/diccionario` se puede:
- **Listar** palabras registradas con paginación y búsqueda
- **Agregar** nuevas palabras al diccionario (sin acento → con acento)

API: `GET /api/dictionary?action=list&page=1&per_page=20&search=...`
API: `GET /api/dictionary?action=add&word_no_accent=JOSE&word_accented=JOSÉ`

## Estructura

```
├── api/migrate.php          ← Pobla BD desde arrays o tabla legacy
├── backend/
│   ├── correct.php          ← API endpoint /api/correct
│   ├── dictionary.php       ← API endpoint /api/dictionary
│   ├── db.php               ← Helper de conexión a BD
│   └── router.php           ← Router para PHP built-in server
├── correct.php              ← CLI + clase Corrector
├── database.sql             ← Esquema de BD
├── config.ini               ← Credenciales MySQL
└── frontend/
    ├── index.html           ← Corrector (dark theme)
    ├── dictionary.html       ← Admin diccionario (tema claro institucional)
    ├── dictionary.js
    ├── app.js
    └── style.css
```
