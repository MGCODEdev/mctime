# Changelog

## v1.3.0 - Club Management & Visuals (2025-12-17)

- **Club-Design**: Unterstützung für zwei Clubfarben (Dual-Color) mit Gradient-Anzeige in UI und Badges.
- **Self-Service**: Clubs können ihr eigenes Profil (Logo, Kontakt) bearbeiten (`club_profile.php`).
- **Interne Übersicht**: Neue Club-Liste (`clubs.php`) exklusiv für eingeloggte Mitglieder.
- **Security-Fix**: Kritische Datenlecks (Passwort-Hashes) im Frontend geschlossen.
- **Admin-Power**: Super-Admins haben vollen Schreibzugriff auf alle Club-Daten inkl. Farben.
- **UI-Update**: Tabs umbenannt in "Veranstaltungen" / "Clubabende".

## v1.2.2 - System Logging (2025-12-13)

- **Logging-System**: Vollständige Erfassung von Logins und CRUD-Operationen (Erstellen, Bearbeiten, Löschen).
- **Admin-Interface**: Neue Seite `admin_logs.php` für Super-Admins.
- **Accountability**: Erfassung von Benutzer, Rolle, IP-Adresse und Zeitstempel.
- **Datenschutz**: Automatische Löschung von Einträgen nach 30 Tagen.

## v1.2.1 - Security Hardening (2025-12-13)

- **CSRF-Schutz**: Implementierung von Anti-CSRF-Tokens in allen Formularen.
- **Brute-Force-Schutz**: Login-Sperre nach 5 Fehlversuchen (erfordert Datenbank-Update).
- **Upload-Sicherheit**: Fix für Path Traversal Schwachstelle bei Club-Logos.
- **Header**: Hinzufügen von Security-Headern (X-Frame-Options, X-Content-Type-Options, etc.).
- **Session**: Optimierte Session-Konfiguration (HttpOnly, Strict).

## v1.2.0 - Permissions & Styling Update (2025-12-13)

- **Erweitertes Admin-System**: Unterstützung für mehrere Super-Admins mit eigener Verwaltungsoberfläche (`admin_users.php`).
- **Berechtigungs-Konzept**: Clubs sehen jetzt ALLE Termine, können aber nur ihre EIGENEN bearbeiten ("Read-All, Edit-Own").
- **Design-Optimierungen**: Verbesserte Lesbarkeit in Tabellen (Dark Mode Hover Fix) und deutlichere Wochenend-Markierung.
- **Navigation**: Einheitliche Menüleiste auf allen Seiten mit Hover-Effekten.

## v1.1.0 - Multi-Calendar Update

- **Zwei Kalender-System**: Trennung in "Open Road" (Öffentlich) und "Iron Circle" (Intern).
- **Kombinierte Ansicht**: Neuer Tab "Alle Termine" für die Übersicht aller Events.
- **Verbesserte Zugriffssteuerung**: Interne Termine nur im "Iron Circle" oder "Alle" Modus sichtbar.
- **Admin-Übersicht**: Neue Spalte "Kalender" in der Terminverwaltung zeigt Sichtbarkeit an.
- **Datenbank-Upgrade**: Automatische Schema-Erweiterung für die Sichtbarkeits-Option.

## v1.0.0 - Initial Release

- **Basis-Kalender**: Monatsansicht mit Terminen.
- **Club-Verwaltung**: Anlegen und Bearbeiten von Clubs mit Logos und Farben.
- **Termin-Verwaltung**: CRUD-Funktionen für Events.
- **Login-System**: Abgesicherter Admin-Bereich und öffentlicher Zugangsschutz.
