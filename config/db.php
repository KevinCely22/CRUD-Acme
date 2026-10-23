<?php
// Conexión DB
function conectar(): PDO
{
    return new PDO('mysql:host=localhost;dbname=acme;charset=utf8mb4', 'root', '1234*', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function e($t) { return htmlspecialchars($t ?? '', ENT_QUOTES); }
