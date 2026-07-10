"""Datenmodell für eine Schüler-Anmeldung.

Ein Datensatz = eine Anmeldung eines Kindes durch die Eltern. Die Feldnamen
hier sind die internen Schlüssel; die Zuordnung zu den Danis-Spalten passiert
zentral in mapping.py.
"""

from datetime import datetime, timezone

from flask_sqlalchemy import SQLAlchemy

db = SQLAlchemy()


def _now():
    return datetime.now(timezone.utc)


class Anmeldung(db.Model):
    __tablename__ = "anmeldungen"

    id = db.Column(db.Integer, primary_key=True)
    erstellt_am = db.Column(db.DateTime, default=_now, nullable=False)

    # Art der Anmeldung: "klasse5" oder "laufend"
    anmeldeart = db.Column(db.String(20), nullable=False)

    # --- Schülerdaten ---
    schueler_nachname = db.Column(db.String(100), nullable=False)
    schueler_vorname = db.Column(db.String(100), nullable=False)
    schueler_rufname = db.Column(db.String(100), default="")
    geschlecht = db.Column(db.String(20), default="")  # weiblich/männlich/divers
    geburtsdatum = db.Column(db.Date, nullable=False)
    geburtsort = db.Column(db.String(100), default="")
    staatsangehoerigkeit = db.Column(db.String(100), default="")
    konfession = db.Column(db.String(100), default="")
    verkehrssprache = db.Column(db.String(100), default="")

    # Anschrift des Kindes
    strasse = db.Column(db.String(150), nullable=False)
    hausnummer = db.Column(db.String(20), default="")
    plz = db.Column(db.String(10), nullable=False)
    ort = db.Column(db.String(100), nullable=False)

    # --- Anmeldedaten ---
    klassenstufe = db.Column(db.String(10), default="")
    aufnahmedatum = db.Column(db.Date, nullable=True)  # nur bei "laufend"
    fremdsprache1 = db.Column(db.String(50), default="")
    fremdsprache2 = db.Column(db.String(50), default="")
    ganztag = db.Column(db.String(20), default="")  # ja/nein
    bisherige_schule = db.Column(db.String(150), default="")
    bisherige_schule_ort = db.Column(db.String(100), default="")

    # --- Erziehungsberechtigte(r) 1 ---
    eb1_anrede = db.Column(db.String(20), default="")
    eb1_nachname = db.Column(db.String(100), nullable=False)
    eb1_vorname = db.Column(db.String(100), nullable=False)
    eb1_verhaeltnis = db.Column(db.String(50), default="")
    eb1_strasse = db.Column(db.String(150), default="")
    eb1_plz = db.Column(db.String(10), default="")
    eb1_ort = db.Column(db.String(100), default="")
    eb1_telefon = db.Column(db.String(50), default="")
    eb1_mobil = db.Column(db.String(50), default="")
    eb1_email = db.Column(db.String(150), default="")
    eb1_sorgerecht = db.Column(db.String(20), default="")

    # --- Erziehungsberechtigte(r) 2 (optional) ---
    eb2_anrede = db.Column(db.String(20), default="")
    eb2_nachname = db.Column(db.String(100), default="")
    eb2_vorname = db.Column(db.String(100), default="")
    eb2_verhaeltnis = db.Column(db.String(50), default="")
    eb2_strasse = db.Column(db.String(150), default="")
    eb2_plz = db.Column(db.String(10), default="")
    eb2_ort = db.Column(db.String(100), default="")
    eb2_telefon = db.Column(db.String(50), default="")
    eb2_mobil = db.Column(db.String(50), default="")
    eb2_email = db.Column(db.String(150), default="")
    eb2_sorgerecht = db.Column(db.String(20), default="")

    # --- Notfall / Sonstiges ---
    notfallkontakt = db.Column(db.String(200), default="")
    geschwister = db.Column(db.String(200), default="")
    bemerkungen = db.Column(db.Text, default="")

    # DSGVO-Einwilligung
    einwilligung = db.Column(db.Boolean, default=False, nullable=False)

    # Bearbeitungsstatus im Admin-Bereich
    exportiert = db.Column(db.Boolean, default=False, nullable=False)

    def anzeigename(self):
        return f"{self.schueler_nachname}, {self.schueler_vorname}"
