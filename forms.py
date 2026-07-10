"""Formulardefinition mit WTForms.

Flask-WTF liefert automatisch CSRF-Schutz. Die serverseitige Validierung
(Pflichtfelder, Datum, E-Mail) läuft ebenfalls hierüber – unabhängig davon,
was der Browser prüft.
"""

from flask_wtf import FlaskForm
from wtforms import (
    BooleanField,
    DateField,
    HiddenField,
    SelectField,
    StringField,
    TextAreaField,
)
from wtforms.validators import (
    DataRequired,
    Email,
    Length,
    Optional,
)

ANREDE = [("", "–"), ("Frau", "Frau"), ("Herr", "Herr"), ("Divers", "Divers")]
GESCHLECHT = [
    ("", "–"),
    ("weiblich", "weiblich"),
    ("männlich", "männlich"),
    ("divers", "divers"),
]
JA_NEIN = [("", "–"), ("ja", "ja"), ("nein", "nein")]
VERHAELTNIS = [
    ("", "–"),
    ("Mutter", "Mutter"),
    ("Vater", "Vater"),
    ("Elternteil", "Elternteil"),
    ("Vormund", "Vormund"),
    ("Sonstige", "Sonstige"),
]


class AnmeldungForm(FlaskForm):
    # Steuerung, welche Felder relevant sind.
    anmeldeart = SelectField(
        "Art der Anmeldung",
        choices=[
            ("klasse5", "Neuanmeldung Klasse 5"),
            ("laufend", "Aufnahme im laufenden Schuljahr"),
        ],
        validators=[DataRequired(message="Bitte wählen.")],
    )

    # Honeypot gegen Bots – muss leer bleiben (per CSS versteckt).
    website = HiddenField()

    # --- Schülerdaten ---
    schueler_nachname = StringField(
        "Nachname des Kindes",
        validators=[DataRequired("Pflichtfeld."), Length(max=100)],
    )
    schueler_vorname = StringField(
        "Vorname(n) des Kindes",
        validators=[DataRequired("Pflichtfeld."), Length(max=100)],
    )
    schueler_rufname = StringField("Rufname", validators=[Optional(), Length(max=100)])
    geschlecht = SelectField("Geschlecht", choices=GESCHLECHT, validators=[Optional()])
    geburtsdatum = DateField(
        "Geburtsdatum", validators=[DataRequired("Bitte gültiges Datum angeben.")]
    )
    geburtsort = StringField("Geburtsort", validators=[Optional(), Length(max=100)])
    staatsangehoerigkeit = StringField(
        "Staatsangehörigkeit", validators=[Optional(), Length(max=100)]
    )
    konfession = StringField(
        "Konfession / Religion", validators=[Optional(), Length(max=100)]
    )
    verkehrssprache = StringField(
        "Verkehrssprache in der Familie", validators=[Optional(), Length(max=100)]
    )

    strasse = StringField(
        "Straße", validators=[DataRequired("Pflichtfeld."), Length(max=150)]
    )
    hausnummer = StringField("Hausnummer", validators=[Optional(), Length(max=20)])
    plz = StringField("PLZ", validators=[DataRequired("Pflichtfeld."), Length(max=10)])
    ort = StringField("Ort", validators=[DataRequired("Pflichtfeld."), Length(max=100)])

    # --- Anmeldedaten ---
    klassenstufe = StringField(
        "Gewünschte Klassenstufe", validators=[Optional(), Length(max=10)]
    )
    aufnahmedatum = DateField(
        "Gewünschtes Aufnahmedatum", validators=[Optional()]
    )
    fremdsprache1 = StringField(
        "1. Fremdsprache (Wunsch)", validators=[Optional(), Length(max=50)]
    )
    fremdsprache2 = StringField(
        "2. Fremdsprache (Wunsch)", validators=[Optional(), Length(max=50)]
    )
    ganztag = SelectField(
        "Ganztag / Hort gewünscht", choices=JA_NEIN, validators=[Optional()]
    )
    bisherige_schule = StringField(
        "Bisher besuchte Schule", validators=[Optional(), Length(max=150)]
    )
    bisherige_schule_ort = StringField(
        "Ort der bisherigen Schule", validators=[Optional(), Length(max=100)]
    )

    # --- Erziehungsberechtigte(r) 1 ---
    eb1_anrede = SelectField("Anrede", choices=ANREDE, validators=[Optional()])
    eb1_nachname = StringField(
        "Nachname", validators=[DataRequired("Pflichtfeld."), Length(max=100)]
    )
    eb1_vorname = StringField(
        "Vorname", validators=[DataRequired("Pflichtfeld."), Length(max=100)]
    )
    eb1_verhaeltnis = SelectField(
        "Verhältnis zum Kind", choices=VERHAELTNIS, validators=[Optional()]
    )
    eb1_strasse = StringField(
        "Straße/Hausnr. (falls abweichend)", validators=[Optional(), Length(max=150)]
    )
    eb1_plz = StringField("PLZ (falls abweichend)", validators=[Optional(), Length(max=10)])
    eb1_ort = StringField("Ort (falls abweichend)", validators=[Optional(), Length(max=100)])
    eb1_telefon = StringField("Telefon", validators=[Optional(), Length(max=50)])
    eb1_mobil = StringField("Mobil", validators=[Optional(), Length(max=50)])
    eb1_email = StringField(
        "E-Mail", validators=[Optional(), Email("Bitte gültige E-Mail."), Length(max=150)]
    )
    eb1_sorgerecht = SelectField(
        "Sorgeberechtigt", choices=JA_NEIN, validators=[Optional()]
    )

    # --- Erziehungsberechtigte(r) 2 (optional) ---
    eb2_anrede = SelectField("Anrede", choices=ANREDE, validators=[Optional()])
    eb2_nachname = StringField("Nachname", validators=[Optional(), Length(max=100)])
    eb2_vorname = StringField("Vorname", validators=[Optional(), Length(max=100)])
    eb2_verhaeltnis = SelectField(
        "Verhältnis zum Kind", choices=VERHAELTNIS, validators=[Optional()]
    )
    eb2_strasse = StringField(
        "Straße/Hausnr. (falls abweichend)", validators=[Optional(), Length(max=150)]
    )
    eb2_plz = StringField("PLZ (falls abweichend)", validators=[Optional(), Length(max=10)])
    eb2_ort = StringField("Ort (falls abweichend)", validators=[Optional(), Length(max=100)])
    eb2_telefon = StringField("Telefon", validators=[Optional(), Length(max=50)])
    eb2_mobil = StringField("Mobil", validators=[Optional(), Length(max=50)])
    eb2_email = StringField(
        "E-Mail", validators=[Optional(), Email("Bitte gültige E-Mail."), Length(max=150)]
    )
    eb2_sorgerecht = SelectField(
        "Sorgeberechtigt", choices=JA_NEIN, validators=[Optional()]
    )

    # --- Notfall / Sonstiges ---
    notfallkontakt = StringField(
        "Notfallkontakt (Name, Telefon)", validators=[Optional(), Length(max=200)]
    )
    geschwister = StringField(
        "Geschwister an dieser Schule", validators=[Optional(), Length(max=200)]
    )
    bemerkungen = TextAreaField("Bemerkungen", validators=[Optional(), Length(max=2000)])

    einwilligung = BooleanField(
        "Ich habe die Datenschutzerklärung gelesen und willige in die "
        "Verarbeitung der angegebenen Daten zum Zweck der Schulanmeldung ein.",
        validators=[DataRequired("Ohne Einwilligung ist keine Anmeldung möglich.")],
    )


class LoginForm(FlaskForm):
    passwort = StringField("Passwort", validators=[DataRequired()])
