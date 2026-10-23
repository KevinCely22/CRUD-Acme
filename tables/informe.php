<?php
require '../config/db.php';
$pdo = conectar();

$informe = $pdo->query("
    SELECT v.placa, v.marca,
           CONCAT_WS(' ', c.primer_nombre, c.segundo_nombre, c.apellidos) AS conductor,
           CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.apellidos) AS propietario
    FROM vehiculo v
    JOIN persona c ON c.id = v.conductor_id
    JOIN persona p ON p.id = v.propietario_id
    ORDER BY v.placa")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Informe | ACME</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="../index.php">ACME S.A.S.</a>
    <div class="navbar-nav">
      <a class="nav-link" href="personas.php">Personas</a>
      <a class="nav-link" href="vehiculos.php">Vehículos</a>
      <a class="nav-link active" href="informe.php">Informe</a>
    </div>
  </div>
</nav>

<main class="container">
  <h1 class="h4 mb-3">Informe de vehículos</h1>

  <div class="card table-responsive">
    <table class="table table-striped table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>Placa</th><th>Marca</th><th>Conductor</th><th>Propietario</th></tr>
      </thead>
      <?php foreach ($informe as $f): ?>
        <tr>
          <td><?= e($f['placa']) ?></td>
          <td><?= e($f['marca']) ?></td>
          <td><?= e($f['conductor']) ?></td>
          <td><?= e($f['propietario']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$informe): ?>
        <tr><td colspan="4" class="text-center text-muted">No hay vehículos registrados.</td></tr>
      <?php endif; ?>
    </table>
  </div>
</main>

<footer class="text-center text-muted small my-4"> ACME S.A.S. </footer>
</body>
</html>