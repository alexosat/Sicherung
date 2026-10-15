# Online-Anmeldung für Danis

Eine schlanke, selbst gehostete Web-Anwendung, mit der sich Eltern online
anmelden können – für die **neuen fünften Klassen** oder für eine **Aufnahme im
laufenden Schuljahr**. Die Schulverwaltung lädt die Anmeldungen als
**DaNiS-XML** herunter und importiert sie direkt in das Schulverwaltungsprogramm
**DaNiS** – ohne Abtippen. Ein CSV-Export ist zusätzlich vorhanden.

Technik: Python + Flask, SQLite als Datenspeicher, keine externen Dienste/CDNs.

---

## Schnellstart (lokal)

```bash
# 1. Abhängigkeiten installieren (idealerweise in einer virtuellen Umgebung)
python3 -m venv .venv
source .venv/bin/activate          # Windows: .venv\Scripts\activate
pip install -r requirements.txt

# 2. Konfiguration anlegen
cp .env.example .env
#   -> in .env SECRET_KEY und ADMIN_PASSWORD setzen

# 3. Starten
flask --app app run
```

Die Anwendung läuft dann auf http://127.0.0.1:5000

- Anmeldeformular: `/anmeldung`
- Verwaltungsbereich: `/admin` (Login mit `ADMIN_PASSWORD`)

---

## DaNiS-XML-Import  ← empfohlener Weg

Im Verwaltungsbereich (`/admin`) gibt es:

- **„Neue als DaNiS-XML“** – alle noch nicht exportierten Anmeldungen in einer Datei,
- **„Alle als DaNiS-XML“** – alle Anmeldungen,
- **„XML“** in jeder Zeile bzw. auf der Detailseite – eine einzelne Anmeldung
  (Dateiname wie in DaNiS, z. B. `MustermannMax.xml`).

Die Datei folgt dem DaNiS-Austauschformat (Version 1.5, wie es DaNiS 3.10
selbst exportiert). DaNiS legt beim Import die Schülerin/den Schüler samt
Erziehungsberechtigten an.

Erzeugt werden: Namen, Geburtsdatum/-ort, Geschlecht, Anschrift,
Erziehungsberechtigte mit Telefon/E-Mail und Sorgerecht, Aufnahmedatum
(Klasse 5 ohne Angabe: nächster 1. August), bisherige Schule, Schuljahr,
Jahrgangsstufe und Schulform. **Nach dem Import im Sekretariat ergänzen:**
Klasse, Identnummer (vergibt DaNiS) sowie Konfession/Staatsangehörigkeit,
falls deren Schlüsselzahl in der App noch nicht hinterlegt ist.

Schulform und Schlüsselzahlen (Konfession, Staatsangehörigkeit, Herkunft)
stehen oben in **`danis_xml.py`** und lassen sich dort ergänzen. Der Schulname
kommt aus `SCHULNAME` in der `.env` und sollte exakt wie in DaNiS lauten.

---

## Danis-CSV-Export anpassen

Das genaue Importformat von Danis ist je nach Schule/Version unterschiedlich.
Die komplette Spaltenzuordnung steckt in **einer einzigen Datei**:

> **`mapping.py`**

So passt ihr sie an, sobald euch die echte Danis-Importvorlage vorliegt:

1. In Danis eine leere oder Beispiel-Importdatei öffnen und die **exakten
   Spaltenüberschriften** notieren.
2. In `mapping.py` in der Liste `SPALTEN` jeweils den rechten Text (die
   Überschrift) an Danis anpassen. Reihenfolge der Liste = Spaltenreihenfolge.
3. Bei Bedarf `CSV_TRENNZEICHEN`, `CSV_ENCODING` und `DATUMSFORMAT` anpassen
   (Standard: Semikolon, UTF-8 mit BOM, `TT.MM.JJJJ` – passt für deutsches
   Excel).
4. Über `/admin` → **„Alle als CSV exportieren"** eine Testdatei ziehen und
   einen Testimport in Danis durchführen.

Der Rest der Anwendung muss dafür **nicht** verändert werden.

---

## Betrieb / Hosting

Für den produktiven Einsatz **nicht** den eingebauten Flask-Server nutzen,
sondern einen WSGI-Server hinter einem Reverse-Proxy mit **HTTPS**:

```bash
pip install gunicorn
gunicorn "app:app" --bind 127.0.0.1:8000
```

Davor gehört ein Reverse-Proxy (z. B. **nginx** oder **Caddy**), der
TLS/HTTPS bereitstellt.

> **Betrieb auf einem Hostinger-VPS?** Eine komplette Schritt-für-Schritt-
> Anleitung (systemd, nginx, HTTPS) steht in **[`DEPLOY-hostinger.md`](DEPLOY-hostinger.md)**.
> Fertige Vorlagen liegen im Ordner `deploy/`.

Empfehlungen:

- `SECRET_KEY` und `ADMIN_PASSWORD` sicher und einmalig setzen.
- In der Umgebung `COOKIE_SECURE=1` setzen, wenn über HTTPS ausgeliefert wird.
- Server-Standort in der **EU**; regelmäßige Backups der Datei
  `instance/anmeldungen.db`.
- Die Datei `instance/anmeldungen.db` enthält **personenbezogene Daten** –
  sie ist bereits per `.gitignore` vom Einchecken ausgeschlossen.

---

## Datenschutz (DSGVO)

Diese Anwendung verarbeitet personenbezogene Daten von Kindern und Eltern.
Vor dem öffentlichen Einsatz unbedingt:

- Die **Datenschutzerklärung** unter `/datenschutz` (Datei
  `templates/datenschutz.html`) an die tatsächlichen Gegebenheiten anpassen –
  der aktuelle Text ist nur ein Platzhalter.
- Verfahren mit der/dem **Datenschutzbeauftragten** der Schule abstimmen.
- Nur die wirklich benötigten Felder erheben (Datensparsamkeit).
- Datensätze im Admin-Bereich löschen, sobald sie nach Danis übernommen wurden
  bzw. nach Ablauf der Aufbewahrungsfrist (Löschfunktion je Anmeldung
  vorhanden).

---

## Tests

```bash
pip install pytest
pytest
```

Die Tests decken Formularabsenden, Validierung, Honeypot, Admin-Login sowie den
CSV- und DaNiS-XML-Export ab.

---

## Projektstruktur

| Datei/Ordner        | Zweck                                                |
|---------------------|------------------------------------------------------|
| `app.py`            | Flask-App und Routen                                 |
| `config.py`         | Konfiguration (aus Umgebungsvariablen)               |
| `models.py`         | Datenmodell einer Anmeldung                          |
| `forms.py`          | Formular + serverseitige Validierung                 |
| `danis_xml.py`      | DaNiS-XML-Erzeugung (Schlüsselzahlen oben anpassbar) |
| `export.py`         | CSV-Erzeugung                                         |
| **`mapping.py`**    | **Danis-Spaltenzuordnung – hier anpassen**           |
| `templates/`        | HTML-Vorlagen                                         |
| `static/style.css`  | Gestaltung                                            |
| `tests/`            | Automatisierte Tests                                 |
