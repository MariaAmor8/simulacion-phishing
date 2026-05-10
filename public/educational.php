<?php
declare(strict_types=1);

function getEducationalSessionId(): string
{
    if (isset($_COOKIE['sv_sim_session']) && preg_match('/^SIM-[A-F0-9]{5}$/', $_COOKIE['sv_sim_session'])) {
        return $_COOKIE['sv_sim_session'];
    }

    return 'SIM-UNKNOWN';
}

function logEducationalEvent(string $eventType, string $sessionId): void
{
    $logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs';
    $logFile = $logDir . DIRECTORY_SEPARATOR . 'interactions.log';

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $line = sprintf(
        "%s | session_id=%s | event=%s | source=portal_simulado | redirect_status=completed | credentials_stored=false%s",
        $timestamp,
        $sessionId,
        $eventType,
        PHP_EOL
    );

    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

$sessionId = getEducationalSessionId();
logEducationalEvent('education_page_redirected', $sessionId);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulación Académica | Hospital San Vital</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="education-body">
    <main class="education-shell">
        <section class="education-card">
            <p class="eyebrow">Hospital San Vital</p>
            <h1>Esta interacción correspondía a una simulación académica</h1>
            <p class="lead">
                El ejercicio hace parte de un laboratorio controlado de concientización sobre phishing.
                Su propósito es ayudar a identificar señales de riesgo antes de ingresar credenciales en
                un sitio no verificado.
            </p>

            <div class="education-grid">
                <article class="info-panel">
                    <h2>Señales de alerta del escenario</h2>
                    <ul>
                        <li>Solicitud urgente asociada a una supuesta actualización obligatoria.</li>
                        <li>Enlace recibido por correo para ingresar al portal.</li>
                        <li>Petición de validación de acceso desde un vínculo externo.</li>
                        <li>Riesgo de digitar credenciales en un sitio no confirmado por la institución.</li>
                    </ul>
                </article>

                <article class="info-panel">
                    <h2>Buenas prácticas recomendadas</h2>
                    <ul>
                        <li>Verificar cuidadosamente el remitente del mensaje.</li>
                        <li>Revisar el dominio antes de confiar en una página de acceso.</li>
                        <li>No ingresar credenciales desde enlaces recibidos por correo.</li>
                        <li>Acceder manualmente al portal oficial desde rutas conocidas.</li>
                        <li>Reportar mensajes sospechosos al área de seguridad institucional.</li>
                    </ul>
                </article>
            </div>

            <div class="education-footer">
                <p>
                    Esta simulación no almacenó usuarios ni contraseñas. Solo se registraron metadatos
                    mínimos con fines pedagógicos en el laboratorio.
                </p>
                <a class="button-link" href="completar">Continuar a la página de formación</a>
            </div>
        </section>
    </main>
</body>
</html>
