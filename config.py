"""Konfiguration der Anwendung.

Alle sicherheitsrelevanten Werte werden aus Umgebungsvariablen gelesen
(siehe .env.example). So landen keine Geheimnisse im Quellcode.
"""

import os
import secrets

# .env laden, falls vorhanden (nur für lokale Entwicklung nötig).
try:
    from dotenv import load_dotenv

    load_dotenv()
except ImportError:  # python-dotenv ist optional
    pass


class Config:
    # Geheimer Schlüssel für Sitzungen und CSRF-Schutz.
    # In Produktion MUSS SECRET_KEY gesetzt sein; sonst wird pro Start ein
    # zufälliger Wert erzeugt (Nutzer werden dann bei Neustart abgemeldet).
    SECRET_KEY = os.environ.get("SECRET_KEY") or secrets.token_hex(32)

    # Passwort für den Admin-Bereich.
    ADMIN_PASSWORD = os.environ.get("ADMIN_PASSWORD", "admin")

    # Name der Schule (erscheint in der Oberfläche).
    SCHULNAME = os.environ.get("SCHULNAME", "Unsere Schule")

    # Datenbank: standardmäßig SQLite im Ordner "instance".
    SQLALCHEMY_DATABASE_URI = os.environ.get(
        "DATABASE_URL", "sqlite:///anmeldungen.db"
    )
    SQLALCHEMY_TRACK_MODIFICATIONS = False

    # Sicherheits-Cookies. SESSION_COOKIE_SECURE sollte in Produktion (HTTPS)
    # auf True stehen; per ENV steuerbar.
    SESSION_COOKIE_HTTPONLY = True
    SESSION_COOKIE_SAMESITE = "Lax"
    SESSION_COOKIE_SECURE = os.environ.get("COOKIE_SECURE", "0") == "1"

    # Einfaches Rate-Limit für das Formular (Sekunden zwischen zwei
    # Anmeldungen aus derselben Sitzung/IP).
    MIN_SEKUNDEN_ZWISCHEN_ANMELDUNGEN = int(
        os.environ.get("MIN_SEKUNDEN_ZWISCHEN_ANMELDUNGEN", "20")
    )
