<?php
declare(strict_types=1);

/**
 * Registra eventos pedagogicos con metadatos minimos.
 * Nunca almacena el usuario ni la contrasena enviados en el formulario.
 */
function logSimulationEvent(string $eventType, string $sessionId, string $redirectStatus = 'not_applicable'): void
{
    $logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs';
    $logFile = $logDir . DIRECTORY_SEPARATOR . 'interactions.log';

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $source = 'portal_simulado';
    $line = sprintf(
        "%s | session_id=%s | event=%s | source=%s | redirect_status=%s | credentials_stored=false%s",
        $timestamp,
        $sessionId,
        $eventType,
        $source,
        $redirectStatus,
        PHP_EOL
    );

    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

/**
 * Genera un identificador ficticio para la simulacion.
 * No representa una identidad real ni una sesion autenticada.
 */
function getSimulationSessionId(): string
{
    if (!isset($_COOKIE['sv_sim_session']) || !preg_match('/^SIM-[A-F0-9]{5}$/', $_COOKIE['sv_sim_session'])) {
        $sessionId = 'SIM-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        setcookie(
            'sv_sim_session',
            $sessionId,
            [
                'expires' => time() + 1800,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );

        return $sessionId;
    }

    return $_COOKIE['sv_sim_session'];
}

$sessionId = getSimulationSessionId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /**
     * Se reciben los campos solo para descartarlos de inmediato.
     * No se validan contra sistemas reales, no se registran y no se reutilizan.
     */
    $submittedUser = $_POST['institutional_user'] ?? '';
    $submittedPassword = $_POST['password'] ?? '';

    unset($submittedUser, $submittedPassword);

    logSimulationEvent('form_submitted', $sessionId, 'redirecting_to_educational_page');
    header('Location: educational.php');
    exit;
}

logSimulationEvent('landing_page_loaded', $sessionId);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Clinico Institucional | Hospital San Vital</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="portal-body">
    <main class="portal-shell">
        <section class="brand-panel" aria-label="Informacion institucional">
            <div class="brand-header">
                <div class="brand-mark" aria-hidden="true">SV</div>
                <div>
                    <p class="eyebrow">Hospital San Vital</p>
                    <p class="brand-subtitle">Red asistencial interna</p>
                </div>
            </div>

            <h1>Portal Clinico Institucional</h1>
            <p class="lead">
                Plataforma interna para consulta operativa, trazabilidad administrativa y
                validacion de acceso sobre recursos clinicos institucionales.
            </p>

            <dl class="meta-list" aria-label="Datos del acceso institucional">
                <div>
                    <dt>Dominio esperado</dt>
                    <dd>mail.hospital.com</dd>
                </div>
                <div>
                    <dt>Entorno</dt>
                    <dd>Acceso interno controlado</dd>
                </div>
                <div>
                    <dt>Validacion</dt>
                    <dd>Requerida antes de continuar</dd>
                </div>
            </dl>

            <div class="notice-card">
                <p class="notice-label">Aviso de seguridad</p>
                <p>
                    Debido a la actualizacion de politicas de proteccion de datos y continuidad
                    operativa, el acceso debe validarse unicamente desde rutas institucionales
                    verificadas.
                </p>
            </div>
        </section>

        <section class="login-card" aria-labelledby="login-title">
            <div class="login-card__header">
                <p class="section-kicker">Acceso seguro institucional</p>
                <h2 id="login-title">Validacion de acceso requerida</h2>
                <p>
                    Ingrese sus credenciales institucionales para validar la continuidad de sesion
                    sobre el dominio oficial <strong>mail.hospital.com</strong>.
                </p>
            </div>

            <div class="login-card__status" aria-label="Estado del portal">
                <span class="status-dot" aria-hidden="true"></span>
                Servicio disponible para personal autorizado
            </div>

            <form method="post" class="login-form" novalidate>
                <label for="institutional_user">Usuario o correo institucional</label>
                <input
                    type="text"
                    id="institutional_user"
                    name="institutional_user"
                    placeholder="nombre.apellido@mail.hospital.com"
                    autocomplete="username"
                    required
                >
                <p class="input-hint">Use exclusivamente cuentas emitidas bajo el dominio institucional.</p>

                <label for="password">Contrasena</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Ingrese su contrasena"
                    autocomplete="current-password"
                    required
                >

                <button type="submit">Validar acceso</button>
            </form>

            <p class="helper-text">
                Acceso restringido al personal autorizado del Hospital San Vital. Toda validacion
                debe completarse desde <strong>mail.hospital.com</strong>.
            </p>
        </section>
    </main>
</body>
</html>
