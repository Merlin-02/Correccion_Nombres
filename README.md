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

## Servidor

```bash
php -S localhost:8000 servicios/router.php
```

---

## Uso desde curl (API HTTP)

### Corrector

```bash
# Mínimo (solo nombres)
curl "http://localhost:8000/api/correct?nombres=JOSE"

# Con nombres y apellidos
curl "http://localhost:8000/api/correct?nombres=JOSE&apellidos=GARCIA"

# Formato minúsculas
curl "http://localhost:8000/api/correct?nombres=JOSE&apellidos=GARCIA&formato=minusculas"

# Formato Capitalizado
curl "http://localhost:8000/api/correct?nombres=JOSE&apellidos=GARCIA&formato=Capitalizado"

# Orden apellidos + nombres
curl "http://localhost:8000/api/correct?nombres=JOSE&apellidos=GARCIA&orden=apellidos_nombres&formato=minusculas"

# Backward compat: parámetro name=
curl "http://localhost:8000/api/correct?name=JOSE%20GARCIA"
```

Respuesta:
```json
{
  "original": "JOSE GARCIA",
  "corrected": "JOSÉ GARCÍA",
  "method": "dictionary",
  "changes": [
    { "from": "JOSE", "to": "JOSÉ" },
    { "from": "GARCIA", "to": "GARCÍA" }
  ]
}
```

### Diccionario

```bash
# Listar palabras (paginado)
curl "http://localhost:8000/api/dictionary?action=list"

# Con paginación y búsqueda
curl "http://localhost:8000/api/dictionary?action=list&page=1&per_page=20&search=MARIA"

# Agregar palabra
curl "http://localhost:8000/api/dictionary?action=add&word_no_accent=JOSE&word_accented=JOSÉ"
```

Respuesta list:
```json
{
  "words": [
    { "word_no_accent": "MARIA", "word_accented": "MARÍA" }
  ],
  "page": 1,
  "per_page": 20,
  "total": 562,
  "total_pages": 29
}
```

---

## Uso como librería PHP

```php
require 'correct.php';

$c = new Corrector();

// Corrección estructurada con nombres + apellidos
$result = $c->correctStructured(
    nombres: 'JOSE',
    apellidos: 'GARCIA LOPEZ',
    orden: 'nombres_apellidos',
    formato: 'Capitalizado'
);
echo $result['corrected']; // "José García López"

// Solo nombres (string simple)
$result = $c->correctStructured(nombres: 'JOSE GARCIA');
echo $result['corrected']; // "JOSÉ GARCÍA"

// Método simple (string a string)
$texto = $c->correct('JOSE GARCIA');
echo $texto; // "JOSÉ GARCÍA"

// Quitar acentos
echo Corrector::removeAccents('JOSÉ GARCÍA'); // "JOSE GARCIA"
```

### Respuesta de correctStructured

```php
[
  'original'  => 'JOSE GARCIA',
  'corrected' => 'José García',
  'method'    => 'dictionary',   // 'dictionary' | 'hunspell' | 'no_changes'
  'changes'   => [
    ['from' => 'JOSE',   'to' => 'JOSÉ'],
    ['from' => 'GARCIA', 'to' => 'GARCÍA'],
  ],
]
```

---

## Uso CLI

```bash
php correct.php --nombres="JOSE" --apellidos="GARCIA LOPEZ"

# Con opciones
php correct.php \
  --nombres="kevin merlin" \
  --apellidos="cabrera coyotzi" \
  --orden=apellidos_nombres \
  --formato=Capitalizado

# Por stdin
echo "JOSE GARCIA" | php correct.php

# Solo nombres directo
php correct.php "JOSE GARCIA"
```

### Parámetros

| Parámetro      | Valores                                       | Default              |
|----------------|-----------------------------------------------|----------------------|
| `--nombres`    | texto                                         | (requerido)          |
| `--apellidos`  | texto                                         | vacío                |
| `--orden`      | `nombres_apellidos` / `apellidos_nombres`     | `nombres_apellidos`  |
| `--formato`    | `MAYUSCULAS` / `minusculas` / `Capitalizado`  | `MAYUSCULAS`         |

---

## Frontend web

```
http://localhost:8000            → Corrector
http://localhost:8000/diccionario → Administrar diccionario
```

---

## Estructura

```
├── api/migrate.php           ← Pobla BD desde arrays o tabla legacy
├── servicios/
│   ├── correct.php           ← API endpoint /api/correct
│   ├── dictionary.php        ← API endpoint /api/dictionary
│   ├── db.php                ← Helper de conexión a BD
│   └── router.php            ← Router para PHP built-in server
├── correct.php               ← CLI + clase Corrector (también usable como librería)
├── database.sql              ← Esquema de BD
├── config.ini                ← Credenciales MySQL
└── frontend/
    ├── index.html            ← Corrector (dark theme)
    ├── dictionary.html        ← Admin diccionario (tema claro institucional)
    ├── dictionary.js
    ├── app.js
    └── style.css
```
