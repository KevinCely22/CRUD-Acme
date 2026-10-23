<?php
require '../config/db.php';
$pdo = conectar();
$ok = $error = '';

if (isset($_POST['eliminar'])) {                              // ELIMINAR
    try {
        $pdo->prepare('DELETE FROM vehiculo WHERE id = ?')->execute([(int)$_POST['id']]);
        $ok = 'Vehículo eliminado.';
    } catch (PDOException $x) {
        $error = 'Error al eliminar el vehículo.';
    }
}

$vehiculos = $pdo->query("
    SELECT v.*,
           CONCAT_WS(' ', c.primer_nombre, c.segundo_nombre, c.apellidos) AS conductor,
           CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.apellidos) AS propietario
    FROM vehiculo v
    JOIN persona c ON c.id = v.conductor_id
    JOIN persona p ON p.id = v.propietario_id
    ORDER BY v.placa")->fetchAll();
$tipos = ['particular' => 'Particular', 'publico' => 'Público'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Vehículos | ACME</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="../index.php">ACME S.A.S.</a>
    <div class="navbar-nav">
      <a class="nav-link" href="personas.php">Personas</a>
      <a class="nav-link active" href="vehiculos.php">Vehículos</a>
      <a class="nav-link" href="informe.php">Informe</a>
    </div>
  </div>
</nav>

<main class="container">
  <?php if ($ok): ?><div class="alert alert-success"><?= e($ok) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Vehículos registrados</h1>
    <a class="btn btn-success" href="../index.php">+ Nuevo vehículo</a>
  </div>

  <div class="card table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light"><tr><th>Placa</th><th>Color</th><th>Marca</th><th>Tipo</th><th>Conductor</th><th>Propietario</th><th></th></tr></thead>
      <?php foreach ($vehiculos as $v): ?>
        <tr>
          <td><?= e($v['placa']) ?></td>
          <td><?= e($v['color']) ?></td>
          <td><?= e($v['marca']) ?></td>
          <td><?= $tipos[$v['tipo']] ?></td>
          <td><?= e($v['conductor']) ?></td>
          <td><?= e($v['propietario']) ?></td>
          <td class="text-end text-nowrap">
            <a class="btn btn-sm btn-outline-primary" href="../index.php?vehiculo=<?= $v['id'] ?>">Editar</a>
            <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar este vehículo?')">
              <input type="hidden" name="id" value="<?= $v['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" name="eliminar" value="1">Eliminar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$vehiculos): ?><tr><td colspan="7" class="text-center text-muted">No hay vehículos registrados.</td></tr><?php endif; ?>
    </table>
  </div>
</main>

<footer class="text-center text-muted small my-4">ACME S.A.S. </footer>
</body>
</html>
