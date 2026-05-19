# Corrector de Acentos para Nombres Hispanos

Corrige acentos ortográficos en nombres propios del español (ej. `JOSE GARCIA LOPEZ` → `JOSÉ GARCÍA LÓPEZ`).

## ¿Cómo funciona?

Dos fuentes de corrección, sin depender de APIs externas ni reglas RAE:

1. **Diccionario** — 50,000 nombres completos con sus acentos correctos almacenados en MySQL. Cubre la mayoría de nombres y apellidos hispanos comunes.
2. **Hunspell** (es_MX) — Corrector ortográfico local. Si una palabra no está en el diccionario, Hunspell sugiere correcciones y solo se aplican si el cambio es exclusivamente de acentuación (no altera otras letras).

## Archivos

```
nombres/
│
├── correct.php              ← CORRECTOR UNIVERSAL
│                               Modo CLI: php correct.php "JOSE GARCIA"
│                               Modo librería: require 'correct.php'; new Corrector()
│                               No necesita servidor web.
│
├── backend/
│   ├── router.php           ← Router del servidor PHP integrado
│   │                          (php -S localhost:8080 backend/router.php)
│   ├── correct.php          ← Endpoint HTTP: GET /api/correct?name=JOSE
│   ├── dictionary.json      ← Diccionario compilado con 50,492 entradas
│   └── config.ini           ← Credenciales MySQL (para regenerar datos)
│
├── frontend/
│   ├── index.html           ← Interfaz web para probar el corrector
│   ├── style.css            ← Estilos
│   └── app.js               ← Cliente JS que llama al endpoint /api/correct
│
├── api/
│   ├── generate.php         ← Genera 50,000 nombres aleatorios en MySQL
│   └── dictionary.php       ← Extrae el diccionario desde MySQL a dictionary.json
│
├── .gitignore
└── README.md
```

## Requisitos

- PHP 8.0+
- MySQL / MariaDB
- Hunspell con diccionario de español (`es_MX`)
- Extensión PHP `mysqli` y `mbstring`

### Instalar Hunspell (es_MX)

```bash
sudo apt install hunspell-es
```

Verificar que está disponible:

```bash
hunspell -d es_MX -a <<< "JOSE"
# Debería sugerir JOSÉ entre las opciones
```

## Primeros pasos

### 1. Crear la base de datos

```bash
mysql -u root -p < setup.sql
# O crear manualmente:
# CREATE DATABASE nombres_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Configurar credenciales

Editar `backend/config.ini` (y también `config.ini` en la raíz si se usan los scripts de regeneración):

```ini
host=localhost
user=root
pass=tu_contraseña
db=nombres_db
```

### 3. Generar los 50,000 nombres

```bash
php api/generate.php
```

### 4. Compilar el diccionario

```bash
php api/dictionary.php
# Genera backend/dictionary.json
```

### 5. Iniciar el servidor web

```bash
php -S localhost:8080 backend/router.php
```

Abrir http://localhost:8080 en el navegador.

## Cómo consumirlo como servicio externo

### A) API HTTP (recomendado para otros lenguajes)

Con el servidor corriendo:

```bash
curl "http://localhost:8080/api/correct?name=JOSE%20GARCIA%20LOPEZ"
```

Respuesta:

```json
{
  "original": "JOSE GARCIA LOPEZ",
  "corrected": "JOSÉ GARCÍA LÓPEZ",
  "method": "dictionary",
  "changes": [
    {"from": "JOSE", "to": "JOSÉ"},
    {"from": "GARCIA", "to": "GARCÍA"},
    {"from": "LOPEZ", "to": "LÓPEZ"}
  ]
}
```

### B) CLI (bash, scripts, contenedores)

```bash
php correct.php "JOSE GARCIA"
# → JOSÉ GARCÍA

echo "MARIA RODRIGUEZ" | php correct.php
# → MARÍA RODRÍGUEZ
```

### C) Librería PHP (desde otro proyecto PHP)

```php
require_once '/ruta/a/correct.php';

$c = new Corrector();

$nombre = $c->correct('JOSE GARCIA');
// → 'JOSÉ GARCÍA'

$detalles = $c->correctWithDetails('RAUL SANCHEZ');
// → [
//     'original' => 'RAUL SANCHEZ',
//     'corrected' => 'RAÚL SÁNCHEZ',
//     'changes' => [
//       ['from' => 'RAUL', 'to' => 'RAÚL'],
//       ['from' => 'SANCHEZ', 'to' => 'SÁNCHEZ']
//     ]
//   ]
```

### D) API REST desde cualquier lenguaje

| Método | Endpoint | Parámetro |
|--------|----------|-----------|
| GET | `/api/correct` | `name` (string) |

Ejemplos:

- **Python:** `requests.get('http://localhost:8080/api/correct', params={'name': 'JOSE GARCIA'})`
- **Node.js:** `fetch('http://localhost:8080/api/correct?name=JOSE+GARCIA')`
- **Java:** `HttpURLConnection` a la URL
- **C#:** `HttpClient.GetAsync("http://localhost:8080/api/correct?name=JOSE GARCIA")`

## Arquitectura

```
                   ┌─────────────┐
                   │   Frontend   │
                   │ (HTML/CSS/JS)│
                   └──────┬──────┘
                          │ GET /api/correct?name=...
                          ▼
                   ┌─────────────┐
                   │   Backend    │  ← PHP router.php
                   │  correct.php │
                   └──┬──────┬───┘
                      │      │
               ┌──────┘      └──────┐
               ▼                     ▼
        ┌──────────────┐    ┌──────────────┐
        │ Diccionario   │    │   Hunspell    │
        │ (50k nombres) │    │  (es_MX)      │
        └──────────────┘    └──────────────┘
               ▲
               │
        ┌──────────────┐
        │    MySQL      │
        │  nombres_db   │
        │  personas     │
        └──────────────┘
```
