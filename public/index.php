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
        <section class="portal-page" aria-label="Acceso institucional">
            <div class="portal-page__header">
                <p class="eyebrow">Hospital San Vital</p>
                <h1>Portal Clinico Institucional</h1>
            </div>

            <div class="portal-page__body">
                <section class="login-pane" aria-labelledby="login-title">
                    <div class="login-pane__intro">
                        <h2 id="login-title">Validacion de acceso requerida</h2>
                        <p class="lead">
                            Por actualizacion de politicas de proteccion de datos, el acceso al
                            portal debe validarse antes de continuar desde
                            <strong>mail.hospital.com</strong>. De lo contrario, no sera posible ingresar.
                        </p>
                    </div>

                    <form method="post" class="login-form" novalidate>
                        <label class="sr-only" for="institutional_user">Usuario o correo institucional</label>
                        <input
                            type="text"
                            id="institutional_user"
                            name="institutional_user"
                            placeholder="nombre.apellido@mail.hospital.com"
                            autocomplete="username"
                            required
                        >

                        <label class="sr-only" for="password">Contrasena</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Contrasena"
                            autocomplete="current-password"
                            required
                        >

                        <button type="submit">Validar acceso</button>
                    </form>

                    <p class="helper-text">
                        Acceso exclusivo para personal autorizado. Si la validacion no se completa,
                        no sera posible ingresar al portal.
                    </p>
                    <p class="support-link">Problemas con el acceso?</p>
                </section>

                <aside class="credential-visual" aria-label="Credencial institucional ilustrativa">
                    <div class="credential-visual__orbit" aria-hidden="true"></div>
                    <div class="credential-stack" aria-hidden="true">
                        <span class="credential-stack__back credential-stack__back--one"></span>
                        <span class="credential-stack__back credential-stack__back--two"></span>
                        <article class="credential-card">
                            <div class="credential-card__header">
                                <div class="credential-card__brand">
                                    <span class="credential-card__mark">SV</span>
                                    <div>
                                        <p>Hospital San Vital</p>
                                        <small>Acceso institucional</small>
                                    </div>
                                </div>
                                <span class="credential-card__badge">ID</span>
                            </div>
                            <div class="credential-card__body">
                                <p class="credential-card__role">Personal clinico autorizado</p>
                                <p class="credential-card__domain">mail.hospital.com</p>
                            </div>
                        </article>
                    </div>
                </aside>
            </div>
        </section>
    </main>
</body>
</html>
