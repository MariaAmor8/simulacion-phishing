# Simulación académica de phishing para concientización

Este proyecto implementa una simulación web ficticia del **Portal Clínico San Vital** para un laboratorio académico de ciberseguridad. El entorno está diseñado exclusivamente para formación y concientización en un contexto controlado. No debe utilizarse contra usuarios reales ni fuera de escenarios autorizados de laboratorio.

## Propósito del proyecto

La simulación representa un flujo típico de ingeniería social: una supuesta validación de acceso motivada por una actualización de políticas de protección de datos. Después de la interacción con el formulario, el sistema redirige inmediatamente a una página educativa que explica el ejercicio y refuerza buenas prácticas para detectar phishing.

## Alcance ético

- Proyecto ficticio para formación académica en entorno controlado.
- No debe usarse contra usuarios reales, organizaciones reales ni infraestructuras de producción.
- No incluye mecanismos para capturar credenciales, reenviarlas, almacenarlas o utilizarlas.
- No incluye instrucciones para suplantación de dominios reales, evasión de controles o despliegues engañosos.

## Estructura del proyecto

```text
simulacion-phishing/
├── public/
│   ├── index.php
│   ├── educational.php
│   └── assets/
│       └── styles.css
├── logs/
│   └── interactions.log
├── nginx/
│   └── simulacion.conf
└── README.md
```

## Flujo funcional

1. El usuario abre `public/index.php`.
2. Se muestra la interfaz ficticia del Portal Clínico Institucional.
3. Al enviar el formulario, `index.php` recibe la petición `POST`.
4. Los campos de usuario y contraseña se descartan inmediatamente en memoria.
5. Se registra solo un evento mínimo de interacción en `logs/interactions.log`.
6. El usuario es redirigido a `public/educational.php`.
7. La página educativa explica que la interacción fue parte de una simulación académica.

## Eventos registrados

El sistema registra únicamente metadatos mínimos y nunca almacena credenciales. Los eventos previstos son:

- `landing_page_loaded`
- `form_submitted`
- `education_page_redirected`

Ejemplo de línea de log permitida:

```text
2026-05-10 15:30:45 | session_id=SIM-8F31A | event=form_submitted | source=portal_simulado | redirect_status=redirecting_to_educational_page | credentials_stored=false
```

## Privacidad y manejo de datos

- No se almacenan credenciales.
- No se recolectan datos clínicos.
- No se usan usuarios reales.
- Los logs son temporales.
- Los logs deben eliminarse al finalizar el laboratorio.
- Los datos registrados solo sirven para fines pedagógicos.

## Ejecución en entorno local o de laboratorio

### Requisitos

- Nginx
- PHP 8.1 o superior
- PHP-FPM o un runtime PHP equivalente para Nginx

### Configuración general

1. Ajuste `nginx/simulacion.conf` con el `server_name` del entorno de laboratorio.
2. Reemplace el valor de `root` por la ruta real del directorio `public/`.
3. Configure `fastcgi_pass` para que apunte a su instancia local de PHP-FPM.
4. Asegúrese de que el directorio `logs/` tenga permisos de escritura para el proceso de PHP.
5. Mantenga `access_log off;` en Nginx para no registrar metadatos adicionales fuera del alcance pedagógico.
6. Levante Nginx y acceda a la URL local.

### Ejemplo de prueba rápida con el servidor embebido de PHP

Para una validación local sencilla, puede ejecutar:

```bash
php -S 127.0.0.1:8000 -t public
```

Luego abra `http://127.0.0.1:8000`.

## Limpieza de logs al finalizar el laboratorio

Puede vaciar o eliminar el archivo temporal de logs cuando termine la práctica.

En PowerShell:

```powershell
Clear-Content .\logs\interactions.log
```

O eliminarlo por completo:

```powershell
Remove-Item .\logs\interactions.log
```

Si elimina el archivo, la aplicación volverá a crearlo cuando se registre un nuevo evento.

## Notas de implementación

- `public/index.php` genera o recupera un `session_id` ficticio con formato `SIM-XXXXX`.
- El formulario existe solo para la simulación visual del escenario.
- Las credenciales recibidas se descartan inmediatamente con `unset(...)`.
- `public/educational.php` registra únicamente el evento final de redirección educativa.
- `logs/interactions.log` nunca debe contener usuario, correo ni contraseña.
