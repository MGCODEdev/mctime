# Outlaw Agenda / MotoCalendar

Eine PHP-Webanwendung zur Verwaltung und Darstellung von Motorrad-Club Terminen (Open Road / Iron Circle).

## Dokumentation

- 📖 **[Benutzerhandbuch (Manual)](MANUAL.md)** - Anleitung zur Benutzung.
- 📝 **[Changelog](CHANGELOG.md)** - Versionshistorie und Änderungen.

## Features

- **Multi-User Kalender**: Trennung zwischen öffentlichen und internen Terminen.
- **Rollen-System**: Super Admins, Club Admins und öffentliche Besucher.
- **Club-Verwaltung**: Eigene Profile, Farben und Logos für jeden Club.
- **Responsive Design**: Optimiert für Desktop und Mobile (Dark Theme).

## Installation

1. Repository klonen.
2. Datenbank erstellen (MariaDB/MySQL) und `schema.sql` importieren.
3. `inc/config.example.php` zu `inc/config.php` umbenennen und DB-Zugangsdaten eintragen.
4. `update_schema.php` und `update_schema_admins.php` ausführen, um die Datenbank auf den neuesten Stand zu bringen.

---
*Version 1.2.1*
