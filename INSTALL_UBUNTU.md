# Implementierung auf Ubuntu 24.10 (Proxmox CT)

Diese Anleitung beschreibt die Installation des MotoCalendar-Systems auf einem frischen Ubuntu 24.10 Container (LXC) in Proxmox.

## Voraussetzungen
- Ein laufender Proxmox CT mit Ubuntu 24.10.
- Root-Zugriff (via Konsole oder SSH).

## 1. System aktualisieren
```bash
apt update && apt upgrade -y
```

## 2. Webserver, PHP und Datenbank installieren
Installieren Sie Apache, MariaDB und PHP mit den notwendigen Erweiterungen.

```bash
apt install -y apache2 mariadb-server php php-mysql php-mbstring php-xml php-curl php-zip unzip git
```

## 3. MariaDB konfigurieren

### Sicherheitsscript ausführen
```bash
mysql_secure_installation
```
Folgen Sie den Anweisungen (Root-Passwort setzen, anonyme Benutzer entfernen, Remote-Root-Login verbieten, Test-DB entfernen).

### Datenbank und Benutzer anlegen
Loggen Sie sich in MariaDB ein:
```bash
mysql -u root -p
```

Führen Sie folgende SQL-Befehle aus (ersetzen Sie `geheimes_passwort` durch ein sicheres Passwort!):

```sql
CREATE DATABASE motocalendar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'moto_user'@'localhost' IDENTIFIED BY 'geheimes_passwort';
GRANT ALL PRIVILEGES ON motocalendar.* TO 'moto_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 4. Applikation installieren

### Code kopieren
Kopieren Sie die Projektdateien in das Webroot-Verzeichnis (z.B. `/var/www/html/motocalendar`).
Wenn Sie git nutzen:
```bash
cd /var/www/html
git clone <repository-url> motocalendar
chown -R www-data:www-data motocalendar
```
Oder manuell hochladen (z.B. via SCP/SFTP).

### Konfiguration anpassen
Bearbeiten Sie die Datei `inc/config.php` und tragen Sie die Datenbank-Zugangsdaten ein:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'motocalendar');
define('DB_USER', 'moto_user');
define('DB_PASS', 'geheimes_passwort');
```

## 5. Apache Konfiguration
Aktivieren Sie `mod_rewrite` für saubere URLs (falls benötigt) und passen Sie den VHost an.

```bash
a2enmod rewrite
```

Bearbeiten Sie die Standard-Seite oder erstellen Sie eine neue:
`/etc/apache2/sites-available/motocalendar.conf`

```apache
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html/motocalendar

    <Directory /var/www/html/motocalendar>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

Aktivieren Sie die Seite:
```bash
a2dissite 000-default.conf
a2ensite motocalendar.conf
systemctl reload apache2
```

## 6. Migration durchführen
Führen Sie das Migrationsscript aus, um die bestehenden JSON-Daten in die Datenbank zu importieren.
**Wichtig:** Stellen Sie sicher, dass die `data/*.json` Dateien vorhanden sind.

```bash
cd /var/www/html/motocalendar
php migrate_to_mariadb.php
```
Nach erfolgreicher Migration löschen Sie das Script oder benennen es um.

## 7. Berechtigungen prüfen
Der Webserver muss Schreibrechte auf das `uploads` Verzeichnis haben (für Logos etc.).

```bash
chown -R www-data:www-data /var/www/html/motocalendar/uploads
chmod -R 755 /var/www/html/motocalendar
chmod -R 775 /var/www/html/motocalendar/uploads
```

## Zugriff
Die Anwendung ist nun unter der IP-Adresse des Containers erreichbar: `http://<IP-Adresse>/`
