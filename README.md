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

Esta aplicación puede probarse de dos maneras:

- Prueba rápida local con el servidor embebido de PHP.
- Despliegue completo en Ubuntu con `nginx + php-fpm`.

## Prueba rápida local con PHP

### Requisitos

- PHP 8.1 o superior

### Arranque rápido

Para una validación local sencilla, puede ejecutar:

```bash
php -S 127.0.0.1:8000 -t public
```

Luego abra `http://127.0.0.1:8000`.

## Despliegue completo en Ubuntu

Esta guía asume un servidor Ubuntu estándar, un usuario con privilegios `sudo` y un entorno de laboratorio controlado. No documenta exposición pública, HTTPS real ni integración con sistemas externos porque eso queda fuera del alcance ético y técnico de esta simulación.

### 1. Instalar dependencias del sistema

Actualice los índices de paquetes e instale `nginx`, `php-fpm` y las utilidades básicas de PHP:

```bash
sudo apt update
sudo apt install -y nginx php-fpm php-cli
```

Verifique que los servicios queden instalados:

```bash
systemctl status nginx
systemctl status php*-fpm
```

### 2. Copiar el proyecto al servidor

Use como ruta recomendada:

```text
/var/www/simulacion-phishing
```

Copie o despliegue el repositorio en esa carpeta. Por ejemplo, si ya tiene el proyecto localmente:

```bash
sudo mkdir -p /var/www
sudo cp -r /ruta/local/simulacion-phishing /var/www/simulacion-phishing
```

Luego entre al proyecto y confirme que la estructura esperada existe:

```bash
cd /var/www/simulacion-phishing
ls
ls public logs nginx
```

Debe ver al menos:

- `public/`
- `logs/`
- `nginx/`

### 3. Ajustar permisos mínimos

El proyecto debe poder leerse normalmente, pero solo el proceso web debe escribir en `logs/`. En Ubuntu, `nginx` y `php-fpm` suelen ejecutar como `www-data`.

Ejemplo de permisos recomendados:

```bash
sudo chown -R $USER:www-data /var/www/simulacion-phishing
sudo find /var/www/simulacion-phishing -type d -exec chmod 755 {} \;
sudo find /var/www/simulacion-phishing -type f -exec chmod 644 {} \;
sudo chown -R www-data:www-data /var/www/simulacion-phishing/logs
sudo chmod 775 /var/www/simulacion-phishing/logs
sudo touch /var/www/simulacion-phishing/logs/interactions.log
sudo chown www-data:www-data /var/www/simulacion-phishing/logs/interactions.log
sudo chmod 664 /var/www/simulacion-phishing/logs/interactions.log
```

Con esta configuración, el contenido de la aplicación queda legible y el directorio `logs/` queda disponible para escritura por parte del servicio web.

### 4. Ajustar la plantilla de Nginx del proyecto

Edite `nginx/simulacion.conf` y reemplace los placeholders antes de activarla:

1. `server_name completar;`
   Use la IP o nombre DNS interno del servidor de laboratorio.
2. `root /ruta/al/proyecto/simulacion-phishing/public;`
   Reemplace por:

```nginx
root /var/www/simulacion-phishing/public;
```

3. `fastcgi_pass 127.0.0.1:9000;`
   En Ubuntu se recomienda usar el socket Unix de `php-fpm`.

Primero identifique el socket real:

```bash
ls /run/php/
```

Normalmente verá algo parecido a:

```text
php8.3-fpm.sock
php8.3-fpm.pid
```

Con ese dato, ajuste la línea a algo como:

```nginx
fastcgi_pass unix:/run/php/php8.3-fpm.sock;
```

Mantenga `access_log off;` para evitar que Nginx genere metadatos adicionales fuera del alcance pedagógico de la simulación. El archivo funcional esperado para el laboratorio sigue siendo únicamente `logs/interactions.log`.

### 5. Activar el sitio en Nginx

Copie la plantilla ajustada a `sites-available`:

```bash
sudo cp /var/www/simulacion-phishing/nginx/simulacion.conf /etc/nginx/sites-available/simulacion
```

Active el sitio:

```bash
sudo ln -s /etc/nginx/sites-available/simulacion /etc/nginx/sites-enabled/simulacion
```

Si el sitio por defecto está habilitado y no lo necesita, desactívelo:

```bash
sudo rm -f /etc/nginx/sites-enabled/default
```

### 6. Validar la configuración y arrancar servicios

Pruebe la sintaxis de Nginx:

```bash
sudo nginx -t
```

Reinicie `php-fpm` con la versión instalada. Sustituya `X.Y` por su versión real, por ejemplo `8.3`:

```bash
sudo systemctl restart phpX.Y-fpm
sudo systemctl reload nginx
```

Habilite ambos servicios para que inicien automáticamente:

```bash
sudo systemctl enable nginx
sudo systemctl enable phpX.Y-fpm
```

Si quiere comprobarlos:

```bash
systemctl status nginx
systemctl status phpX.Y-fpm
```

### 7. Abrir el firewall solo si aplica

Si `ufw` está habilitado en el servidor, permita HTTP:

```bash
sudo ufw allow 'Nginx HTTP'
```

Puede comprobar el estado del firewall con:

```bash
sudo ufw status
```

Si `ufw` no está activo, este paso no es necesario.

### 8. Validar el funcionamiento de la aplicación

Abra en un navegador:

```text
http://IP_DEL_SERVIDOR/
```

Realice esta validación:

1. Confirmar que carga la landing del Portal Clínico San Vital.
2. Ingresar datos ficticios en los campos del formulario.
3. Pulsar `Validar acceso`.
4. Confirmar redirección inmediata a `educational.php`.
5. Revisar el archivo `logs/interactions.log`.
6. Verificar que no aparezcan ni el usuario ni la contraseña digitados.

Puede inspeccionar el log con:

```bash
cat /var/www/simulacion-phishing/logs/interactions.log
```

Los eventos esperados son:

- `landing_page_loaded`
- `form_submitted`
- `education_page_redirected`

### 9. Resolución breve de problemas

**Error 502 Bad Gateway**

- Revise que `fastcgi_pass` apunte al socket real de `php-fpm`.
- Confirme que `phpX.Y-fpm` esté activo con `systemctl status phpX.Y-fpm`.

**Página en blanco o error PHP**

- Revise el log de errores de Nginx definido en la configuración.
- Confirme que `index.php` y `educational.php` estén dentro de `public/`.

**No se escribe `logs/interactions.log`**

- Revise que `logs/` pertenezca a `www-data`.
- Confirme permisos de escritura sobre `logs/` y `interactions.log`.

**Se abre otro sitio o da 404**

- Revise que `root` apunte exactamente a `/var/www/simulacion-phishing/public`.
- Confirme que el sitio correcto esté habilitado en `/etc/nginx/sites-enabled/`.

## Limpieza de logs al finalizar el laboratorio

Puede vaciar o eliminar el archivo temporal de logs cuando termine la práctica.

En Linux:

```bash
truncate -s 0 /var/www/simulacion-phishing/logs/interactions.log
```

O eliminarlo por completo:

```bash
rm /var/www/simulacion-phishing/logs/interactions.log
```

Si elimina el archivo, la aplicación volverá a crearlo cuando se registre un nuevo evento.

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
