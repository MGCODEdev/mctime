# MotoCalendar - Benutzerhandbuch

Willkommen beim MotoCalendar. Diese Webanwendung dient zur Verwaltung und Darstellung von Motorrad-Club-Terminen.

## Inhaltsverzeichnis
1. [Rollen & Berechtigungen](#rollen--berechtigungen)
2. [Login](#login)
3. [Termine verwalten](#termine-verwalten)
4. [Club Verwaltung](#club-verwaltung)
5. [Super Admin Funktionen](#super-admin-funktionen)

---

## Rollen & Berechtigungen

Das System unterscheidet drei Zugriffsebenen:

1.  **Öffentlich (Gast)**: Kann nur öffentliche Termine ("Open Road") sehen.
2.  **Club Admin**:
    - Kann ALLE Termine (auch interne) lesen.
    - Kann nur **befeigene** Termine bearbeiten oder löschen.
    - Kann keine anderen Clubs oder Admins verwalten.
3.  **Super Admin**:
    - Voller Zugriff auf alles.
    - Kann Clubs anlegen/löschen.
    - Kann andere Super Admins verwalten.
    - Kann Termine aller Clubs bearbeiten.

---

## Login

Der Login erfolgt über `login.php`.
- **Benutzername/Passwort**: Entweder die Daten eines Clubs ODER eines Super Admins.
- **Öffentlicher Zugang**: Benötigt nur das globale Passwort für den öffentlichen Bereich.

---

## Termine verwalten

Nach dem Login erreichen Sie die Terminverwaltung über "Termine" oder "Verwaltung".

### Neuer Termin
1.  Klicken Sie auf **"Neuer Termin"**.
2.  Füllen Sie die Felder aus:
    - **Titel**: Name des Events.
    - **Sichtbarkeit**:
        - *Öffentlich (Open Road)*: Für alle sichtbar.
        - *Intern (Iron Circle)*: Nur für eingeloggte User sichtbar.
    - **Datum/Zeit/Ort**.
3.  Klicken Sie auf **"Speichern"**.

### Termin bearbeiten / löschen
- In der Tabelle sehen Sie alle Termine.
- **Club Admins**: Sie sehen Bearbeiten/Löschen-Buttons nur bei Ihren eigenen Terminen. Bei anderen steht "Nur Lesen".
- **Super Admins**: Haben immer Zugriff.

---

## Club Verwaltung

*(Nur für Super Admins)*

Unter dem Menüpunkt **"Clubs"** können Sie die teilnehmenden Motorrad-Clubs verwalten.
- **Login Name**: Dies ist der Benutzername, mit dem sich der Club-Admin einloggt.
- **Farbe**: Die Farbe, in der die Termine dieses Clubs im Kalender erscheinen.
- **Logo**: Kann hochgeladen werden.

---

## Super Admin Funktionen

*(Nur für Super Admins)*

Unter dem Menüpunkt **"Admins"** verwalten Sie den Zugang für Administratoren.
- **Neuer Admin**: Legen Sie weitere Personen an, die vollen Zugriff haben sollen.
- **Passwort ändern**: Hier können Sie Passwörter von anderen Admins zurücksetzen.

---

*Stand: v1.2.0 (Dezember 2025)*
