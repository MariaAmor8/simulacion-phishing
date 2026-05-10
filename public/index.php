<?php
declare(strict_types=1);

/**
 * Registra eventos pedagógicos con metadatos mínimos.
 * Nunca almacena el usuario ni la contraseña enviados en el formulario.
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
 * Genera un identificador ficticio para la simulación.
 * No representa una identidad real ni una sesión autenticada.
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
    <title>Portal Clínico Institucional | Hospital San Vital</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="portal-body">
    <main class="portal-shell">
        <section class="brand-panel" aria-label="Información institucional">
            <div class="brand-mark" aria-hidden="true">SV</div>
            <p class="eyebrow">Hospital San Vital</p>
            <h1>Portal Clínico Institucional</h1>
            <p class="lead">
                Acceso interno para consulta y actualización de información clínica y administrativa
                en entorno institucional controlado.
            </p>
            <div class="notice-card">
                <p class="notice-label">Validación de acceso requerida</p>
                <p>
                    Por actualización de políticas de protección de datos, el acceso al portal debe
                    validarse antes de continuar.
                </p>
            </div>
        </section>

        <section class="login-card" aria-labelledby="login-title">
            <div class="login-card__header">
                <p class="section-kicker">Acceso seguro</p>
                <h2 id="login-title">Validación de acceso requerida</h2>
                <p>
                    Ingrese sus datos institucionales para completar la validación del acceso al portal.
                </p>
            </div>

            <form method="post" class="login-form" novalidate>
                <label for="institutional_user">Usuario o correo institucional</label>
                <input
                    type="text"
                    id="institutional_user"
                    name="institutional_user"
                    placeholder="usuario@sanvital.local"
                    autocomplete="username"
                    required
                >

                <label for="password">Contraseña</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Ingrese su contraseña"
                    autocomplete="current-password"
                    required
                >

                <button type="submit">Validar acceso</button>
            </form>

            <p class="helper-text">
                Acceso restringido al personal autorizado del Hospital San Vital.
            </p>
        </section>
    </main>
</body>
</html>
