<?php
declare(strict_types=1);

/**
 * Registra eventos pedagogicos con metadatos minimos.
 * Nunca almacena el correo completo ni la contrasena enviados en el formulario.
 */
function logSimulationEvent(
    string $eventType,
    string $sessionId,
    string $redirectStatus = 'not_applicable',
    string $emailDomain = 'not_applicable'
): void
{
    $logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs';
    $logFile = $logDir . DIRECTORY_SEPARATOR . 'interactions.log';

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $source = 'portal_simulado';
    $line = sprintf(
        "%s | session_id=%s | event=%s | source=%s | redirect_status=%s | email_domain=%s | credentials_stored=false%s",
        $timestamp,
        $sessionId,
        $eventType,
        $source,
        $redirectStatus,
        $emailDomain,
        PHP_EOL
    );

    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

function extractEmailDomain(string $submittedUser): string
{
    $normalizedUser = strtolower(trim($submittedUser));

    if ($normalizedUser === '') {
        return 'not_provided';
    }

    $atPosition = strrpos($normalizedUser, '@');
    if ($atPosition === false || $atPosition === strlen($normalizedUser) - 1) {
        return 'not_provided';
    }

    return substr($normalizedUser, $atPosition + 1);
}

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
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
$errors = [];
$institutionalUser = '';
$password = '';
$firstInvalidField = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /**
     * Se reciben los campos para validaciones educativas de consistencia.
     * No se validan contra sistemas reales, no se registran completos y no se reutilizan.
     */
    $institutionalUser = (string) ($_POST['institutional_user'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    $trimmedUser = trim($institutionalUser);
    $trimmedPassword = trim($password);

    if ($trimmedUser === '') {
        $errors['institutional_user'] = 'Ingrese un usuario o correo institucional.';
    } elseif ($institutionalUser !== $trimmedUser) {
        $errors['institutional_user'] = 'Elimine espacios al inicio o al final del correo.';
    } elseif (preg_match('/\s/', $trimmedUser) === 1) {
        $errors['institutional_user'] = 'El correo no debe contener espacios.';
    } elseif (filter_var($trimmedUser, FILTER_VALIDATE_EMAIL) === false) {
        $errors['institutional_user'] = 'Ingrese un correo con formato valido.';
    } elseif (extractEmailDomain($trimmedUser) !== 'hospital.com') {
        $errors['institutional_user'] = 'Use un correo institucional con dominio @hospital.com.';
    }

    if ($trimmedPassword === '') {
        $errors['password'] = 'Ingrese la contrasena para continuar.';
    } elseif ($password !== $trimmedPassword) {
        $errors['password'] = 'Elimine espacios al inicio o al final de la contrasena.';
    }

    if ($errors !== []) {
        $firstInvalidField = array_key_first($errors);
        $password = '';
    } else {
        $emailDomain = extractEmailDomain($trimmedUser);

        unset($password, $trimmedPassword);

        logSimulationEvent('form_submitted', $sessionId, 'redirecting_to_educational_page', $emailDomain);
        header('Location: educational.php');
        exit;
    }
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
                            portal debe validarse antes de continuar. De lo contrario, no sera
                            posible ingresar.
                        </p>
                        <p class="field-note">Use su correo institucional.</p>
                    </div>

                    <form method="post" class="login-form" novalidate>
                        <?php if ($errors !== []): ?>
                            <div class="form-alert" role="alert" aria-live="assertive" tabindex="-1">
                                <p>Revise los campos marcados antes de continuar.</p>
                                <ul>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= escapeHtml($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="field-group">
                            <label class="sr-only" for="institutional_user">Usuario o correo institucional</label>
                            <input
                                type="text"
                                id="institutional_user"
                                name="institutional_user"
                                placeholder="nombre.apellido@hospital.com"
                                autocomplete="username"
                                value="<?= escapeHtml($institutionalUser) ?>"
                                aria-invalid="<?= isset($errors['institutional_user']) ? 'true' : 'false' ?>"
                                aria-describedby="institutional_user_note<?= isset($errors['institutional_user']) ? ' institutional_user_error' : '' ?>"
                                <?= $firstInvalidField === 'institutional_user' ? 'autofocus' : '' ?>
                                required
                            >
                            <p class="field-note" id="institutional_user_note">Use un correo institucional sin espacios y con formato valido.</p>
                            <?php if (isset($errors['institutional_user'])): ?>
                                <p class="field-error" id="institutional_user_error"><?= escapeHtml($errors['institutional_user']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="field-group">
                            <label class="sr-only" for="password">Contrasena</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Contrasena"
                                autocomplete="current-password"
                                aria-invalid="<?= isset($errors['password']) ? 'true' : 'false' ?>"
                                aria-describedby="password_note<?= isset($errors['password']) ? ' password_error' : '' ?>"
                                <?= $firstInvalidField === 'password' ? 'autofocus' : '' ?>
                                required
                            >
                            <p class="field-note" id="password_note">Ingrese su contrasena sin espacios al inicio o al final.</p>
                            <?php if (isset($errors['password'])): ?>
                                <p class="field-error" id="password_error"><?= escapeHtml($errors['password']) ?></p>
                            <?php endif; ?>
                        </div>

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

            <footer class="portal-footer" aria-label="Aviso de simulacion">
                <p>Simulacion de seguridad institucional.</p>
            </footer>
        </section>
    </main>
</body>
</html>
