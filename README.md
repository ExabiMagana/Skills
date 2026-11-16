# ITCA SKILLS 2025 — PHP puro

Aplicación de preguntas y quiz en PHP 8.2+, sin Laravel, Composer, Node ni compilación. Conserva el CRUD, las cinco categorías (CRI, PE, CSIS, AMACSS y DAE), la selección aleatoria, las respuestas y el resumen de puntuación. Las respuestas se comparan sin distinguir mayúsculas y se muestra la solución después de responder, como en la aplicación original.

## Ejecutar con XAMPP

1. Activa Apache y MySQL.
2. Importa `INDICACIONES/quiz_game.sql` desde phpMyAdmin. Si ya tienes la base `quiz_game`, conserva la existente: no vuelvas a importar el volcado sobre ella.
3. Las credenciales predeterminadas son `127.0.0.1:3306`, base `quiz_game`, usuario `root`, contraseña vacía. Para cambiarlas, copia `.env.example` a `.env` y edita los valores. También puedes editar `config/database.php`.
4. Abre **http://localhost/Skills-ITCA-2025/**. También funciona **http://localhost/Skills-ITCA-2025/public/**.

Los enlaces usan `index.php?route=...`, por lo que no requieren reescritura de URL. Con Apache y mod_rewrite también puedes abrir `/juego`, `/preguntas` y `/preguntas/1/edit`. Para un servidor público configura el directorio web en `public/`, de modo que los archivos internos nunca sean públicos.

## Servidor integrado (alternativa)

Desde la raíz del proyecto, con MySQL activo:

```sh
php -S localhost:8000 -t public public/router.php
```

Abre http://localhost:8000. El estilo original utiliza Tailwind por CDN y requiere Internet; la lógica, los formularios y el juego se ejecutan localmente.

## Estructura

- `index.php`: rutas, CRUD y endpoints JSON del juego.
- `bootstrap/app.php`: sesiones, CSRF, PDO, consultas y funciones de vistas.
- `config/database.php`: conexión y lectura opcional de `.env`.
- `resources/views/`: plantillas PHP con el diseño original.
- `public/index.php`: entrada para servidores con raíz pública.
- `INDICACIONES/quiz_game.sql`: base de datos original, compatible sin migraciones.

Las escrituras usan consultas preparadas, transacciones y protección CSRF. El servidor guarda las preguntas de cada partida para evitar puntuar dos veces la misma respuesta. La administración sigue siendo accesible sin inicio de sesión, como en el proyecto original; úsala en un entorno de confianza.

