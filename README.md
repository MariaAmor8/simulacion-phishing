# Simulacion de phishing para concientizacion

Este proyecto implementa una simulacion web ficticia del **Portal Clinico San Vital** para ejercicios de concientizacion en ciberseguridad dentro de un entorno controlado. La aplicacion no autentica contra sistemas reales, no reenvia datos y no almacena contrasenas.

## Proposito

La experiencia representa una supuesta validacion de acceso motivada por una actualizacion de politicas de proteccion de datos. Despues de interactuar con el formulario, el usuario es redirigido a una pagina educativa con recomendaciones para reconocer senales de phishing.

## Alcance etico

- Proyecto ficticio para formacion y concientizacion en entorno controlado.
- No debe usarse contra usuarios reales, organizaciones reales ni infraestructuras de produccion.
- No incluye mecanismos para autenticar, reenviar credenciales ni reutilizar datos ingresados.
- No incluye instrucciones para suplantacion de dominios reales, evasion de controles o despliegues enganiosos.

## Estructura del proyecto

```text
simulacion-phishing/
|-- public/
|   |-- index.php
|   |-- educational.php
|   `-- assets/
|       `-- styles.css
|-- logs/
|   `-- interactions.log
|-- nginx/
|   `-- simulacion.conf
`-- README.md
```

## Flujo funcional

1. El usuario abre `public/index.php`.
2. Se muestra la interfaz ficticia del Portal Clinico Institucional.
3. Al enviar el formulario, `index.php` recibe la peticion `POST`.
4. La contrasena se descarta inmediatamente en memoria y el correo solo se usa para generar un hash de seguimiento.
5. Se registra un evento minimo de interaccion en `logs/interactions.log`.
6. El usuario es redirigido a `public/educational.php`.
7. La pagina educativa muestra recomendaciones para detectar accesos sospechosos.

## Eventos registrados

El sistema registra metadatos minimos y nunca almacena credenciales ni el correo completo. Los eventos previstos son:

- `landing_page_loaded`
- `form_submitted`
- `education_page_redirected`

Ejemplo de linea de log permitida:

```text
2026-05-10 15:30:45 | session_id=SIM-8F31A | event=form_submitted | source=portal_simulado | redirect_status=redirecting_to_educational_page | email_hash=4e3b1d7a... | credentials_stored=false
```

## Privacidad y manejo de datos

- No se almacenan contrasenas.
- No se almacena el correo completo; solo puede registrarse un hash del correo como metadato de seguimiento.
- No se recolectan datos clinicos.
- No se usan usuarios reales.
- Los logs son temporales y deben eliminarse al finalizar la practica.

## Prueba local rapida

### Requisitos

- PHP 8.1 o superior

### Arranque

```bash
php -S 127.0.0.1:8000 -t public
```

Luego abra `http://127.0.0.1:8000`.

## Despliegue en Ubuntu con nginx + php-fpm

Esta guia asume un servidor Ubuntu estandar, un usuario con privilegios `sudo` y un entorno controlado.

### 1. Instalar dependencias

```bash
sudo apt update
sudo apt install -y nginx php-fpm php-cli
```

Verificacion basica:

```bash
systemctl status nginx
systemctl status php*-fpm
```

### 2. Copiar el proyecto

Ruta sugerida:

```text
/var/www/simulacion-phishing
```

Ejemplo:

```bash
sudo mkdir -p /var/www
sudo cp -r /ruta/local/simulacion-phishing /var/www/simulacion-phishing
cd /var/www/simulacion-phishing
ls
```

### 3. Ajustar permisos

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

### 4. Ajustar Nginx

Revise `nginx/simulacion.conf` y adapte los placeholders operativos segun su servidor:

- `root /ruta/al/proyecto/simulacion-phishing/public;`
- `error_log /ruta/al/proyecto/simulacion-phishing/logs/nginx_error.log warn;`
- `fastcgi_pass 127.0.0.1:9000;` o el socket real de `php-fpm`

Para identificar el socket:

```bash
ls /run/php/
```

Ejemplo de ajuste:

```nginx
fastcgi_pass unix:/run/php/php8.3-fpm.sock;
```

Mantenga `access_log off;` para no generar metadatos adicionales fuera del archivo funcional principal `logs/interactions.log`.

### 5. Activar el sitio

```bash
sudo cp /var/www/simulacion-phishing/nginx/simulacion.conf /etc/nginx/sites-available/simulacion
sudo ln -s /etc/nginx/sites-available/simulacion /etc/nginx/sites-enabled/simulacion
sudo rm -f /etc/nginx/sites-enabled/default
```

### 6. Validar y recargar servicios

```bash
sudo nginx -t
sudo systemctl restart phpX.Y-fpm
sudo systemctl reload nginx
sudo systemctl enable nginx
sudo systemctl enable phpX.Y-fpm
```

### 7. Validacion funcional

Abra la aplicacion en el navegador y valide:

1. Carga de la landing del Portal Clinico San Vital.
2. Ingreso de datos ficticios en el formulario.
3. Click en `Validar acceso`.
4. Redireccion inmediata a `educational.php`.
5. Presencia de eventos en `logs/interactions.log`.
6. Ausencia de contrasena y de correo completo en el log.
7. Presencia de `email_hash=...` cuando el formulario trae un correo valido.

Puede inspeccionar el log con:

```bash
cat /var/www/simulacion-phishing/logs/interactions.log
```

## Resolucion breve de problemas

### Error 502 Bad Gateway

- Revise que `fastcgi_pass` apunte al socket real de `php-fpm`.
- Confirme que `phpX.Y-fpm` este activo.

### Pagina en blanco o error PHP

- Revise el `error_log` configurado en Nginx.
- Confirme que `index.php` y `educational.php` esten dentro de `public/`.

### No se escribe `logs/interactions.log`

- Revise propietario y permisos de `logs/`.
- Confirme permisos sobre `logs/interactions.log`.

### Se abre otro sitio o da 404

- Revise que `root` apunte exactamente a la carpeta `public`.
- Confirme que el sitio correcto este habilitado en `/etc/nginx/sites-enabled/`.

## Limpieza de logs

En Linux:

```bash
truncate -s 0 /var/www/simulacion-phishing/logs/interactions.log
```

O eliminarlo:

```bash
rm /var/www/simulacion-phishing/logs/interactions.log
```

En PowerShell:

```powershell
Clear-Content .\logs\interactions.log
```

O eliminarlo:

```powershell
Remove-Item .\logs\interactions.log
```

## Notas de implementacion

- `public/index.php` genera o recupera un `session_id` ficticio con formato `SIM-XXXXX`.
- El formulario existe solo para la simulacion visual del escenario.
- La contrasena recibida se descarta inmediatamente con `unset(...)`.
- El correo no se almacena completo; solo se conserva un hash para registrarlo como `email_hash` cuando aplica.
- `public/educational.php` registra el evento final de redireccion educativa.
- `logs/interactions.log` nunca debe contener contrasena ni el correo completo.
