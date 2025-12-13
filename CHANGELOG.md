# Changelog

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
