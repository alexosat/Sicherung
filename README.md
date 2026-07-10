# Online-Anmeldung für Danis

Eine schlanke, selbst gehostete Web-Anwendung, mit der sich Eltern online
anmelden können – für die **neuen fünften Klassen** oder für eine **Aufnahme im
laufenden Schuljahr**. Die Schulverwaltung kann die gesammelten Daten als
**CSV** exportieren und in das Schulverwaltungsprogramm **Danis** übernehmen.

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

## Danis-Export anpassen  ← wichtig

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
TLS/HTTPS bereitstellt. Empfehlungen:

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

Die Tests decken Formularabsenden, Validierung, Honeypot, Admin-Login und den
CSV-Export ab.

---

## Projektstruktur

| Datei/Ordner        | Zweck                                                |
|---------------------|------------------------------------------------------|
| `app.py`            | Flask-App und Routen                                 |
| `config.py`         | Konfiguration (aus Umgebungsvariablen)               |
| `models.py`         | Datenmodell einer Anmeldung                          |
| `forms.py`          | Formular + serverseitige Validierung                 |
| `export.py`         | CSV-Erzeugung                                         |
| **`mapping.py`**    | **Danis-Spaltenzuordnung – hier anpassen**           |
| `templates/`        | HTML-Vorlagen                                         |
| `static/style.css`  | Gestaltung                                            |
| `tests/`            | Automatisierte Tests                                 |
