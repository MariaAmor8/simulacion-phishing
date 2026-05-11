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
        "%s | session_id=%s | event=%s | source=portal_simulado | redirect_status=completed | email_hash=not_applicable | credentials_stored=false%s",
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
    <title>Recomendaciones de Seguridad | Hospital San Vital</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="education-body">
    <main class="education-shell">
        <section class="education-card">
            <div class="education-header">
                <p class="eyebrow">Aviso de seguridad</p>
                <h1>Esta interaccion hacia parte de una simulacion de seguridad</h1>
                <p class="lead">
                    El objetivo era mostrar como una solicitud aparentemente formal puede inducir a
                    ingresar credenciales en una pagina no confirmada. Antes de confiar en un acceso,
                    conviene detenerse unos segundos y revisar las senales clave.
                </p>
            </div>

            <div class="education-banner" role="note" aria-label="Resumen del ejercicio">
                <strong>Resultado clave:</strong>
                No se almacenaron usuarios ni contrasenas. Solo se registraron metadatos minimos de interaccion.
            </div>

            <div class="education-highlights" aria-label="Puntos clave de verificacion">
                <article class="education-pill">
                    <span class="education-pill__label">1</span>
                    <p>Revise el remitente antes de abrir enlaces de acceso.</p>
                </article>
                <article class="education-pill">
                    <span class="education-pill__label">2</span>
                    <p>Confirme que el dominio esperado sea <strong>portal.sanvital.org</strong>.</p>
                </article>
                <article class="education-pill">
                    <span class="education-pill__label">3</span>
                    <p>Si hay urgencia injustificada, haga una pausa antes de autenticarse.</p>
                </article>
            </div>

            <div class="education-grid">
                <article class="info-panel">
                    <h2>Senales de alerta del escenario</h2>
                    <ul>
                        <li>Solicitud urgente asociada a una supuesta actualizacion obligatoria.</li>
                        <li>Enlace recibido por correo para ingresar al portal desde una ruta externa.</li>
                        <li>Peticion de validacion inmediata sin confirmar primero el dominio esperado.</li>
                        <li>Riesgo de digitar credenciales en un sitio no verificado por la institucion.</li>
                    </ul>
                </article>

                <article class="info-panel">
                    <h2>Buenas practicas recomendadas</h2>
                    <ul>
                        <li>Verificar cuidadosamente el remitente y el contexto operativo del mensaje.</li>
                        <li>Confirmar que la direccion pertenezca al dominio esperado: <strong>portal.sanvital.org</strong>.</li>
                        <li>No ingresar credenciales desde enlaces recibidos por correo.</li>
                        <li>Acceder manualmente al portal oficial escribiendo <strong>portal.sanvital.org</strong>.</li>
                        <li>Reportar mensajes sospechosos al area de seguridad institucional.</li>
                    </ul>
                </article>
            </div>

            <div class="education-footer">
                <p class="education-footer__title">Regla simple para futuros accesos</p>
                <p>
                    Revise siempre el dominio, la ruta de acceso y el contexto de la solicitud antes
                    de autenticarse. En un entorno real, esa pausa suele marcar la diferencia entre
                    un acceso seguro y un intento de phishing exitoso.
                </p>
            </div>
        </section>
    </main>
</body>
</html>
