"""Tests für die Anmelde-Anwendung.

Ausführen:  pytest
"""

import os
import sys

import pytest

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from app import create_app  # noqa: E402
from config import Config  # noqa: E402
from models import Anmeldung, db  # noqa: E402


class TestConfig(Config):
    TESTING = True
    WTF_CSRF_ENABLED = False  # in Tests kein CSRF-Token nötig
    SQLALCHEMY_DATABASE_URI = "sqlite:///:memory:"
    ADMIN_PASSWORD = "geheim"
    SECRET_KEY = "test-key"
    MIN_SEKUNDEN_ZWISCHEN_ANMELDUNGEN = 0


@pytest.fixture
def app():
    app = create_app(TestConfig)
    yield app


@pytest.fixture
def client(app):
    return app.test_client()


def _gueltige_daten(**overrides):
    daten = {
        "anmeldeart": "klasse5",
        "schueler_nachname": "Mustermann",
        "schueler_vorname": "Max",
        "geburtsdatum": "2015-06-01",
        "strasse": "Hauptstraße",
        "hausnummer": "1",
        "plz": "12345",
        "ort": "Musterstadt",
        "eb1_nachname": "Mustermann",
        "eb1_vorname": "Erika",
        "eb1_email": "erika@example.org",
        "einwilligung": "y",
    }
    daten.update(overrides)
    return daten


def test_formular_wird_angezeigt(client):
    r = client.get("/anmeldung")
    assert r.status_code == 200
    assert "Anmeldung Ihres Kindes".encode() in r.data


def test_gueltige_anmeldung_wird_gespeichert(client, app):
    r = client.post("/anmeldung", data=_gueltige_daten(), follow_redirects=False)
    assert r.status_code == 302
    assert "/erfolg" in r.headers["Location"]
    with app.app_context():
        eintraege = Anmeldung.query.all()
        assert len(eintraege) == 1
        assert eintraege[0].schueler_nachname == "Mustermann"
        assert eintraege[0].einwilligung is True


def test_pflichtfeld_fehlt(client, app):
    daten = _gueltige_daten()
    del daten["schueler_nachname"]
    r = client.post("/anmeldung", data=daten)
    assert r.status_code == 200
    assert "Pflichtfeld".encode() in r.data
    with app.app_context():
        assert Anmeldung.query.count() == 0


def test_einwilligung_erforderlich(client, app):
    daten = _gueltige_daten()
    del daten["einwilligung"]
    r = client.post("/anmeldung", data=daten)
    assert r.status_code == 200
    with app.app_context():
        assert Anmeldung.query.count() == 0


def test_ungueltiges_datum(client, app):
    r = client.post("/anmeldung", data=_gueltige_daten(geburtsdatum="kein-datum"))
    assert r.status_code == 200
    with app.app_context():
        assert Anmeldung.query.count() == 0


def test_honeypot_blockt_bots(client, app):
    r = client.post("/anmeldung", data=_gueltige_daten(website="http://spam"))
    assert r.status_code == 400
    with app.app_context():
        assert Anmeldung.query.count() == 0


def test_admin_geschuetzt(client):
    r = client.get("/admin", follow_redirects=False)
    assert r.status_code == 302
    assert "/admin/login" in r.headers["Location"]


def test_admin_login_und_liste(client):
    client.post("/anmeldung", data=_gueltige_daten())
    r = client.post("/admin/login", data={"passwort": "geheim"}, follow_redirects=True)
    assert r.status_code == 200
    r = client.get("/admin")
    assert "Mustermann".encode() in r.data


def test_admin_falsches_passwort(client):
    r = client.post("/admin/login", data={"passwort": "falsch"}, follow_redirects=True)
    assert "Falsches Passwort".encode() in r.data


def test_csv_export(client, app):
    client.post("/anmeldung", data=_gueltige_daten())
    client.post("/admin/login", data={"passwort": "geheim"})
    r = client.get("/admin/export")
    assert r.status_code == 200
    assert "text/csv" in r.content_type
    text = r.data.decode("utf-8-sig")
    assert "Nachname" in text  # Kopfzeile
    assert "Mustermann" in text
    assert "01.06.2015" in text  # deutsches Datumsformat
    # Datensatz sollte danach als exportiert markiert sein
    with app.app_context():
        assert Anmeldung.query.first().exportiert is True


def test_csv_nur_offen(client, app):
    client.post("/anmeldung", data=_gueltige_daten())
    client.post("/admin/login", data={"passwort": "geheim"})
    client.get("/admin/export")  # markiert als exportiert
    # zweite Anmeldung mit durchgehend eindeutigem Namen
    client.post(
        "/anmeldung",
        data=_gueltige_daten(schueler_nachname="Zweitkind", eb1_nachname="Zweitkind"),
    )
    r = client.get("/admin/export?nur=offen")
    text = r.data.decode("utf-8-sig")
    assert "Zweitkind" in text
    assert "Mustermann" not in text


def test_loeschen(client, app):
    client.post("/anmeldung", data=_gueltige_daten())
    client.post("/admin/login", data={"passwort": "geheim"})
    with app.app_context():
        anmeldung_id = Anmeldung.query.first().id
    client.post(f"/admin/{anmeldung_id}/loeschen")
    with app.app_context():
        assert Anmeldung.query.count() == 0
