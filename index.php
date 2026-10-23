<?php
require 'config/db.php';
$pdo = conectar();

$L = "[A-Za-zÁÉÍÓÚÜÑáéíóúüñ' ]+";

$camposP = [
    'cedula'         => ['Cédula', '\d{6,10}', 10, 1],
    'primer_nombre'  => ['Primer nombre', $L, 40, 1],
    'segundo_nombre' => ['Segundo nombre', $L, 40, 0],
    'apellidos'      => ['Apellidos', $L, 80, 1],
    'direccion'      => ['Dirección', '.{5,120}', 120, 1],
    'telefono'       => ['Teléfono', '\d{7,10}', 10, 1],
    'ciudad'         => ['Ciudad', $L, 60, 1],
];
$camposV = [
    'placa' => ['Placa', '[A-Za-z]{3}\d{2}[A-Za-z\d]', 7, 1],
    'color' => ['Color', $L, 30, 1],
    'marca' => ['Marca', $L, 40, 1],
];

// Validar
function validar($campos, &$d, &$errores)
{
    foreach ($campos as $k => [$et, $pat, $max, $req]) {
        $d[$k] = preg_replace('/\s+/u', ' ', trim((string)($_POST[$k] ?? '')));
        if (($req || $d[$k] !== '') && (!preg_match("/^$pat$/u", $d[$k]) || mb_strlen($d[$k]) > $max)) {
            $errores[] = "$et no es válido.";
        }
    }
}

$form = $_POST['form'] ?? '';
$idP  = (int)($form === 'persona'  ? ($_POST['id'] ?? 0) : ($_GET['persona']  ?? 0));
$idV  = (int)($form === 'vehiculo' ? ($_POST['id'] ?? 0) : ($_GET['vehiculo'] ?? 0));
$dp = []; $dv = []; $errores = []; $ok = '';

if ($form === 'persona') {                                    // GUARDAR PERSONA
    validar($camposP, $dp, $errores);
    if (!$errores) {
        $v = array_values($dp);
        $v[2] = $v[2] ?: null;
        try {
            if ($idP) $pdo->prepare('UPDATE persona SET cedula=?, primer_nombre=?, segundo_nombre=?, apellidos=?, direccion=?, telefono=?, ciudad=? WHERE id=?')->execute([...$v, $idP]);
            else      $pdo->prepare('INSERT INTO persona (cedula, primer_nombre, segundo_nombre, apellidos, direccion, telefono, ciudad) VALUES (?,?,?,?,?,?,?)')->execute($v);
            $ok = 'Persona guardada.'; $dp = []; $idP = 0;
        } catch (PDOException $x) {
            $errores[] = $x->getCode() === '23000' ? 'Ya existe una persona con esa cédula.' : 'Error al guardar la persona.';
        }
    }
} elseif ($form === 'vehiculo') {                             // GUARDAR VEHÍCULO
    validar($camposV, $dv, $errores);
    $dv['placa']          = strtoupper($dv['placa']);
    $dv['tipo']           = $_POST['tipo'] ?? '';
    $dv['conductor_id']   = (int)($_POST['conductor_id'] ?? 0);
    $dv['propietario_id'] = (int)($_POST['propietario_id'] ?? 0);
    if (!in_array($dv['tipo'], ['particular', 'publico'])) $errores[] = 'Seleccione el tipo de vehículo.';
    if (!$dv['conductor_id'])   $errores[] = 'Seleccione un conductor.';
    if (!$dv['propietario_id']) $errores[] = 'Seleccione un propietario.';
    if (!$errores) {
        $v = [$dv['placa'], $dv['color'], $dv['marca'], $dv['tipo'], $dv['conductor_id'], $dv['propietario_id']];
        try {
            if ($idV) $pdo->prepare('UPDATE vehiculo SET placa=?, color=?, marca=?, tipo=?, conductor_id=?, propietario_id=? WHERE id=?')->execute([...$v, $idV]);
            else      $pdo->prepare('INSERT INTO vehiculo (placa, color, marca, tipo, conductor_id, propietario_id) VALUES (?,?,?,?,?,?)')->execute($v);
            $ok = 'Vehículo guardado.'; $dv = []; $idV = 0;
        } catch (PDOException $x) {
            $errores[] = ($x->errorInfo[1] ?? 0) == 1062 ? 'Ya existe un vehículo con esa placa.' : 'Error al guardar el vehículo.';
        }
    }
} else {                                                      // CARGAR PARA EDITAR
    if ($idP) {
        $st = $pdo->prepare('SELECT * FROM persona WHERE id = ?');
        $st->execute([$idP]);
        $dp = $st->fetch() ?: [];
        if (!$dp) $idP = 0;
    }
    if ($idV) {
        $st = $pdo->prepare('SELECT * FROM vehiculo WHERE id = ?');
        $st->execute([$idV]);
        $dv = $st->fetch() ?: [];
        if (!$dv) $idV = 0;
    }
}

$personas = $pdo->query("SELECT id, cedula, CONCAT_WS(' ', primer_nombre, segundo_nombre, apellidos) AS nombre FROM persona ORDER BY apellidos, primer_nombre")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Formularios | ACME</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="index.php">ACME S.A.S.</a>
    <div class="navbar-nav">
      <a class="nav-link" href="tables/personas.php">Personas</a>
      <a class="nav-link" href="tables/vehiculos.php">Vehículos</a>
      <a class="nav-link" href="tables/informe.php">Informe</a>
    </div>
  </div>
</nav>

<main class="container">
  <?php if ($ok): ?><div class="alert alert-success"><?= e($ok) ?></div><?php endif; ?>
  <?php if ($errores): ?><div class="alert alert-danger"><?= implode('<br>', array_map('e', $errores)) ?></div><?php endif; ?>

  <!-- FORMULARIO PERSONA -->
  <form method="post" action="index.php" class="card card-body mb-4">
    <h5><?= $idP ? 'Editar persona' : 'Nueva persona (conductor o propietario)' ?></h5>
    <input type="hidden" name="form" value="persona">
    <input type="hidden" name="id" value="<?= $idP ?>">
    <div class="row g-3">
      <?php foreach ($camposP as $k => [$et, $pat, $max, $req]): ?>
        <div class="col-md-4">
          <label class="form-label" for="<?= $k ?>"><?= $et . ($req ? ' *' : '') ?></label>
          <input class="form-control" id="<?= $k ?>" name="<?= $k ?>" value="<?= e($dp[$k] ?? '') ?>"
                 pattern="<?= e($pat) ?>" maxlength="<?= $max ?>" <?= $req ? 'required' : '' ?>>
        </div>
      <?php endforeach; ?>
      <div class="col-12">
        <button class="btn btn-primary">Guardar</button>
        <?php if ($idP): ?><a class="btn btn-outline-secondary" href="index.php">Cancelar</a><?php endif; ?>
      </div>
    </div>
  </form>

  <!-- FORMULARIO VEHÍCULO -->
  <form method="post" action="index.php" class="card card-body mb-4">
    <h5><?= $idV ? 'Editar vehículo' : 'Nuevo vehículo' ?></h5>
    <input type="hidden" name="form" value="vehiculo">
    <input type="hidden" name="id" value="<?= $idV ?>">
    <div class="row g-3">
      <?php foreach ($camposV as $k => [$et, $pat, $max, $req]): ?>
        <div class="col-md-4">
          <label class="form-label" for="<?= $k ?>"><?= $et ?> *</label>
          <input class="form-control" id="<?= $k ?>" name="<?= $k ?>" value="<?= e($dv[$k] ?? '') ?>"
                 pattern="<?= e($pat) ?>" maxlength="<?= $max ?>" required>
        </div>
      <?php endforeach; ?>

      <div class="col-md-4">
        <label class="form-label" for="tipo">Tipo de vehículo *</label>
        <select class="form-select" id="tipo" name="tipo" required>
          <option value="">Seleccione...</option>
          <?php foreach (['particular' => 'Particular', 'publico' => 'Público'] as $val => $txt): ?>
            <option value="<?= $val ?>" <?= ($dv['tipo'] ?? '') === $val ? 'selected' : '' ?>><?= $txt ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <?php foreach (['conductor_id' => 'Conductor', 'propietario_id' => 'Propietario'] as $k => $et): ?>
        <div class="col-md-4">
          <label class="form-label" for="<?= $k ?>"><?= $et ?> *</label>
          <select class="form-select" id="<?= $k ?>" name="<?= $k ?>" required>
            <option value="">Seleccione...</option>
            <?php foreach ($personas as $p): ?>
              <option value="<?= $p['id'] ?>" <?= ($dv[$k] ?? 0) == $p['id'] ? 'selected' : '' ?>><?= e("$p[nombre] ($p[cedula])") ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endforeach; ?>
      <?php if (!$personas): ?><div class="col-12 text-muted small">Registra primero al menos una persona para poder asignarla.</div><?php endif; ?>

      <div class="col-12">
        <button class="btn btn-primary">Guardar</button>
        <?php if ($idV): ?><a class="btn btn-outline-secondary" href="index.php">Cancelar</a><?php endif; ?>
      </div>
    </div>
  </form>
</main>

<footer class="text-center text-muted small my-4">ACME S.A.S.</footer>
</body>
</html>
