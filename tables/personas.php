<?php
require '../config/db.php';
$pdo = conectar();
$ok = $error = '';

// ELIMINAR
if (isset($_POST['eliminar'])) {
    try {
        $pdo->prepare('DELETE FROM persona WHERE id = ?')->execute([(int)$_POST['id']]);
        $ok = 'Persona eliminada.';
    } catch (PDOException $x) {
        $error = $x->getCode() === '23000' ? 'No se puede eliminar: la persona está asignada a un vehículo.' : 'Error al eliminar.';
    }
}

$personas = $pdo->query('SELECT * FROM persona ORDER BY apellidos, primer_nombre')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Personas | ACME</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="../index.php">ACME S.A.S.</a>
    <div class="navbar-nav">
      <a class="nav-link active" href="personas.php">Personas</a>
      <a class="nav-link" href="vehiculos.php">Vehículos</a>
      <a class="nav-link" href="informe.php">Informe</a>
    </div>
  </div>
</nav>

<main class="container">
  <?php if ($ok): ?><div class="alert alert-success"><?= e($ok) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Personas registradas</h1>
    <a class="btn btn-success" href="../index.php">+ Nueva persona</a>
  </div>

  <div class="card table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light"><tr><th>Cédula</th><th>Nombre completo</th><th>Dirección</th><th>Teléfono</th><th>Ciudad</th><th></th></tr></thead>
      <?php foreach ($personas as $p): ?>
        <tr>
          <td><?= e($p['cedula']) ?></td>
          <td><?= e("$p[primer_nombre] $p[segundo_nombre] $p[apellidos]") ?></td>
          <td><?= e($p['direccion']) ?></td>
          <td><?= e($p['telefono']) ?></td>
          <td><?= e($p['ciudad']) ?></td>
          <td class="text-end text-nowrap">
            <a class="btn btn-sm btn-outline-primary" href="../index.php?persona=<?= $p['id'] ?>">Editar</a>
            <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar a esta persona?')">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" name="eliminar" value="1">Eliminar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$personas): ?><tr><td colspan="6" class="text-center text-muted">No hay personas registradas.</td></tr><?php endif; ?>
    </table>
  </div>
</main>

<footer class="text-center text-muted small my-4">ACME S.A.S.</footer>
</body>
</html>
