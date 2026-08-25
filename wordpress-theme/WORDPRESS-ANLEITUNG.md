# WordPress-Theme „Aller-Weser-Oberschule" — Installation & Bedienung

Dieses Block-Theme bildet die neue Landingpage 1:1 in WordPress ab. Alle Abschnitte
sind im WordPress-Editor bearbeitbar. Schriften sind **lokal eingebunden** (kein
Google-Fonts-Datenschutzproblem).

---

## 1. Theme installieren

1. Datei **`aws-oberschule.zip`** herunterladen.
2. Im WordPress-Adminbereich: **Design → Themes → Theme hinzufügen → Theme hochladen**.
3. Die ZIP-Datei auswählen, **Jetzt installieren**, dann **Aktivieren**.

> Voraussetzung: WordPress 6.4 oder neuer (für Full-Site-Editing/Block-Themes).

## 2. Logo setzen (einmalig)

Das Logo wird über das WordPress-„Site-Logo" gesetzt (Header und Footer nutzen es):

1. **Design → Editor → Kopfbereich** öffnen.
2. Auf den **Logo-Block** klicken → **Medien hochladen** → Datei **`logo-mark.png`**
   auswählen (liegt im Theme unter `assets/img/`, ist aber auch separat beigefügt).
3. Speichern.

## 3. Startseite festlegen

1. **Einstellungen → Lesen → „Deine Startseite zeigt" → „Eine statische Seite".**
2. Eine neue, **leere** Seite mit dem Titel *Startseite* anlegen und als Startseite wählen.
   Den Inhalt liefert automatisch die Theme-Vorlage „Front Page" — die Seite selbst bleibt leer.

## 4. Rechtsseiten anlegen (Impressum, Datenschutz, Barrierefreiheit)

Für jede der drei Seiten:

1. **Seiten → Erstellen.** Titel z. B. *Impressum* (Permalink muss `/impressum/` sein;
   ebenso `/datenschutz/` und `/barrierefreiheit/` — dann stimmen die Footer-Links).
2. Rechts unter **Vorlage** die Vorlage **„Rechtliche Seite"** auswählen.
3. Im Inhalt: **Block einfügen (+) → Muster → Kategorie „Aller-Weser-Oberschule"** →
   das passende Muster (*Impressum* / *Datenschutzerklärung* / *Erklärung zur Barrierefreiheit*) einfügen.
4. **Die gelb markierten Stellen** ausfüllen (siehe unten) und **veröffentlichen**.

## 5. Texte & Bilder der Startseite bearbeiten

- **Texte:** **Design → Editor → Vorlagen → „Front Page"**. Dort jeden Abschnitt direkt
  anklicken und ändern. Änderungen speichern.
- **Fotos einsetzen:** Die farbigen Platzhalter (Hero-Bild „Tag der offenen Tür",
  „Schulalltag", „Karte") lassen sich durch **Bild-Blöcke** ersetzen — Block anklicken,
  löschen, an gleicher Stelle einen **Bild-Block** einfügen und ein Foto hochladen.
- **Anmelde-Button:** verlinkt bereits auf `https://anmeldung.obs-doerverden.de`.

### Termine im Bereich „Aktuelles" pflegen

Der Fluss-Zeitstrahl im Abschnitt **Aktuelles** besteht aus Karten (Block „news-item").
Im Site-Editor (Front Page):
- **Text/Datum ändern:** Karte anklicken und Tag, Titel, Datum oder Text überschreiben.
- **Termin hinzufügen:** eine bestehende „news-item"-Gruppe in der Listenansicht auswählen,
  **duplizieren** und die Inhalte anpassen. Die Flusslinie und die Punkte passen sich
  automatisch an (die Animation läuft im Front-End).
- **Termin entfernen:** die „news-item"-Gruppe löschen.
- Die Kennzeichnung **Termin/News** steuert die kleine Markierung oben auf der Karte
  (Klasse `termin` = grün, ohne = blau).

## 6. Noch auszufüllen (rechtlich prüfen)

Die gelb markierten Stellen in den Rechtstexten:
- **Impressum:** Schulträger + Anschrift; zuständiger RLSB-Standort.
- **Datenschutz:** Datenschutzbeauftragte/r der Schule; Hostinger-Firmierung/Anschrift + EU-Serverstandort.
- **Barrierefreiheit:** Erstellungsdatum, Stand der Vereinbarkeit, ggf. Einschränkungen,
  Kontakt der Durchsetzungs-/Schlichtungsstelle.

> **Wichtig:** Impressum und Datenschutz vor dem Livegang rechtlich prüfen lassen.

## 7. Domain / Livegang

Wenn alles passt, wird die Domain **obs-doerverden.de** auf diese WordPress-Installation
gezeigt. Das ist ein separater Schritt (DNS/Hosting) — sprich mich gern an.

---

## Enthaltene Dateien (Theme-Aufbau)

```
aws-oberschule/
├── style.css              Theme-Info + komplettes Design
├── theme.json             Farben, Schriften, Layout
├── functions.php          Einbindung, Muster-Kategorie
├── templates/             Front Page, Seite, Rechtliche Seite, Index, 404
├── parts/                 Kopf- und Fußbereich
├── patterns/              Impressum, Datenschutz, Barrierefreiheit
└── assets/                Logo (img) und Schriften (fonts, lokal)
```
