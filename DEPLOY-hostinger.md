# Anleitung: App auf einem Hostinger-VPS betreiben

Diese Anleitung bringt die Anmelde-App auf einem **Hostinger-VPS** dauerhaft
online – mit automatischem Neustart (systemd), einem nginx-Reverse-Proxy und
kostenlosem HTTPS (Let's Encrypt).

> **Wichtig:** Das einfache **Webhosting / Shared Hosting** von Hostinger ist
> für diese Python-App **nicht** geeignet (dort läuft im Wesentlichen nur PHP).
> Du brauchst ein **VPS**-Paket. Der kleinste Tarif (z. B. „KVM 1") genügt.

---

## 0. Vorbereitung bei Hostinger

1. Einen **VPS** bestellen, als Betriebssystem **Ubuntu 22.04 (oder 24.04)**
   wählen.
2. **Rechenzentrum in der EU** auswählen (Datenschutz – es geht um Schülerdaten).
3. Eine (Sub-)Domain auf die IP des VPS zeigen lassen, z. B.
   `anmeldung.deineschule.de` (A-Record auf die Server-IP).
4. **DSGVO:** Mit der Schulleitung / dem/der Datenschutzbeauftragten einen
   **Auftragsverarbeitungsvertrag (AVV)** mit Hostinger klären, bevor echte
   Daten erfasst werden.

Platzhalter in dieser Anleitung ersetzen:
- `anmeldung.deineschule.de` → deine echte Domain
- Der Dienst läuft unter dem Benutzer **`anmeldung`** im Verzeichnis
  `/home/anmeldung/Sicherung`.

---

## 1. Per SSH auf den Server

```bash
ssh root@DEINE-SERVER-IP
```

## 2. Grundpakete + eigenen Benutzer anlegen

```bash
apt update && apt upgrade -y
apt install -y python3-venv python3-pip git nginx

# Eigenen, nicht-privilegierten Benutzer für die App (nicht als root laufen lassen)
adduser --disabled-password --gecos "" anmeldung
```

## 3. Projekt holen und einrichten

```bash
sudo -u anmeldung -i        # als Benutzer "anmeldung" weiterarbeiten

git clone https://github.com/alexosat/Sicherung.git
cd Sicherung
git checkout claude/danis-parent-registration-lz48ub   # oder der gemergte Branch

python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt gunicorn
```

## 4. Konfiguration (.env) anlegen

```bash
cp .env.example .env
nano .env
```

In der `.env` setzen:

```
SECRET_KEY=<lange-zufaellige-zeichenkette>
ADMIN_PASSWORD=<sicheres-passwort>
SCHULNAME=Name deiner Schule
COOKIE_SECURE=1
```

Einen guten `SECRET_KEY` erzeugen:

```bash
python -c "import secrets; print(secrets.token_hex(32))"
```

Danach die Benutzer-Sitzung verlassen: `exit` (zurück zu root).

## 5. Dienst mit systemd einrichten (Autostart + Neustart)

Die Vorlage aus dem Repo kopieren und aktivieren:

```bash
cp /home/anmeldung/Sicherung/deploy/anmeldung.service /etc/systemd/system/anmeldung.service
# bei Bedarf Pfade/Benutzer in der Datei prüfen:
nano /etc/systemd/system/anmeldung.service

systemctl daemon-reload
systemctl enable --now anmeldung
systemctl status anmeldung        # sollte "active (running)" zeigen
```

Die App läuft jetzt intern auf `127.0.0.1:8000`.

## 6. nginx als Reverse-Proxy

```bash
cp /home/anmeldung/Sicherung/deploy/nginx-anmeldung.conf /etc/nginx/sites-available/anmeldung
nano /etc/nginx/sites-available/anmeldung     # server_name auf deine Domain setzen

ln -s /etc/nginx/sites-available/anmeldung /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default        # Standardseite entfernen
nginx -t && systemctl reload nginx
```

Jetzt ist die Seite unter `http://anmeldung.deineschule.de` erreichbar.

## 7. HTTPS aktivieren (kostenlos, Let's Encrypt)

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d anmeldung.deineschule.de
```

certbot trägt HTTPS automatisch in die nginx-Konfiguration ein und richtet die
Zertifikatsverlängerung ein. Danach ist die Seite über **`https://`** erreichbar.

Fertig! 🎉

---

## Updates einspielen (neuer Stand aus Git)

```bash
sudo -u anmeldung -i
cd Sicherung && git pull
source .venv/bin/activate && pip install -r requirements.txt
exit
systemctl restart anmeldung
```

## Danis-Export anpassen

Sobald die echte Danis-Importvorlage vorliegt: in `mapping.py` die
Spaltenüberschriften anpassen (siehe README) und den Dienst neu starten
(`systemctl restart anmeldung`).

## Backups (wichtig!)

Die Datenbank `instance/anmeldungen.db` enthält personenbezogene Daten.
Regelmäßig sichern, z. B. per Cron:

```bash
# als Benutzer anmeldung, täglich 2 Uhr eine Kopie mit Datum
crontab -e
0 2 * * * cp ~/Sicherung/instance/anmeldungen.db ~/backup-anmeldungen-$(date +\%F).db
```

Alte Sicherungen und exportierte CSV-Dateien nach Übernahme in Danis wieder
löschen (Datensparsamkeit).

## Fehlersuche

```bash
systemctl status anmeldung        # läuft der Dienst?
journalctl -u anmeldung -n 50     # Log der App
nginx -t                          # nginx-Konfiguration korrekt?
tail -f /var/log/nginx/error.log  # nginx-Fehler
```
