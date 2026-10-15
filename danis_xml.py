# -*- coding: utf-8 -*-
"""
====================================================================
 DANIS-XML-EXPORT: Anmeldungen direkt in DaNiS importieren
====================================================================

Erzeugt aus Anmeldungen eine XML-Datei im DaNiS-Austauschformat
(Namensraum http://www.nils.nibis.de/DaNiS). DaNiS kann diese Datei
importieren und legt dabei die Schülerinnen und Schüler neu an.

Vorbild ist eine echte, von DaNiS 3.10 erzeugte und erfolgreich wieder
importierte Datei (Formatversion 1.5). Erlaubte Codes (Geschlecht,
Kontaktart, Art des/der Verantwortlichen) stammen aus danis.xsd.

Felder, die DaNiS als Schlüsselzahl ("kennzahl") erwartet – Konfession,
Herkunft –, werden nur geschrieben, wenn die Schlüsselzahl unten bekannt
ist. Alles andere ergänzt das Sekretariat nach dem Import in DaNiS.
Weitere Schlüsselzahlen können unten einfach nachgetragen werden.
"""

import re
import uuid
import xml.etree.ElementTree as ET
from datetime import date

# --- Schulspezifische Angaben -> HIER ANPASSEN ----------------------------

# Schulform laut DaNiS (Kennzahl und Bezeichnung wie im DaNiS-Export).
SCHULFORM_KENNZAHL = "40"
SCHULFORM_TEXT = "Oberschule"

# Konfession: Eingabe im Formular (klein geschrieben) -> (Kennzahl, Text).
KONFESSIONEN = {
    "evangelisch": ("1", "evangelisch"),
    "ev.": ("1", "evangelisch"),
    "ev": ("1", "evangelisch"),
}

# Staatsangehörigkeit: Eingabe im Formular (klein) -> (ISO-Code, Text).
STAATSANGEHOERIGKEITEN = {
    "deutsch": ("DE", "Deutschland"),
    "deutschland": ("DE", "Deutschland"),
    "de": ("DE", "Deutschland"),
}

# Herkunft bei einer Neuanmeldung für Klasse 5.
HERKUNFT_KLASSE5 = ("01", "Versetzung/Zugang aus dem vorhergehenden SJG - Grundschule")

# --- Ab hier muss normalerweise nichts angepasst werden -------------------

NS = "http://www.nils.nibis.de/DaNiS"
XSI = "http://www.w3.org/2001/XMLSchema-instance"
FORMATVERSION = "1.5"

# DaNiS kennzeichnet ein unbekanntes Geburtsdatum mit diesem Wert.
UNBEKANNTES_DATUM = "1899-12-30"

# Fester Namensraum für die GUIDs, damit eine Anmeldung bei jedem Export
# dieselben GUIDs erhält (DaNiS erkennt Personen daran wieder).
_GUID_NAMENSRAUM = uuid.UUID("5b0f6c3e-2d1a-4c7e-9a8b-0d6a1f3e7c42")

ET.register_namespace("", NS)
ET.register_namespace("xsi", XSI)


def _guid(anmeldung, rolle):
    zeit = anmeldung.erstellt_am.isoformat() if anmeldung.erstellt_am else ""
    return str(uuid.uuid5(_GUID_NAMENSRAUM, f"{anmeldung.id}|{zeit}|{rolle}"))


def _el(eltern, tag, text=None, **attribute):
    element = ET.SubElement(eltern, f"{{{NS}}}{tag}", attribute)
    if text is not None:
        element.text = str(text)
    return element


def _geschlecht(wert):
    return {"weiblich": "gWeiblich", "männlich": "gMaennlich"}.get(wert, "gUnbestimmt")


def _eb_art(verhaeltnis, anrede):
    if verhaeltnis == "Mutter" or (verhaeltnis == "Elternteil" and anrede == "Frau"):
        return "vaMutter"
    if verhaeltnis == "Vater" or (verhaeltnis == "Elternteil" and anrede == "Herr"):
        return "vaVater"
    if verhaeltnis == "Vormund":
        return "vaVormund"
    return "vaSonstige"


def _eb_geschlecht(anrede, art):
    if anrede == "Frau" or art == "vaMutter":
        return "gWeiblich"
    if anrede == "Herr" or art == "vaVater":
        return "gMaennlich"
    return "gUnbestimmt"


def schuljahresbeginn(anmeldung):
    """Aufnahmedatum; bei Klasse 5 ohne Angabe der nächste 1. August."""
    if anmeldung.aufnahmedatum:
        return anmeldung.aufnahmedatum
    bezug = anmeldung.erstellt_am.date() if anmeldung.erstellt_am else date.today()
    jahr = bezug.year if bezug.month < 8 else bezug.year + 1
    return date(jahr, 8, 1)


def _schuljahr(datum):
    """Schuljahresbezeichnung wie in DaNiS, z. B. '26/27'."""
    start = datum.year if datum.month >= 8 else datum.year - 1
    return f"{start % 100:02d}/{(start + 1) % 100:02d}"


def _jahrgangsstufe(anmeldung):
    if anmeldung.anmeldeart == "klasse5":
        return 5
    treffer = re.match(r"\s*(\d{1,2})", anmeldung.klassenstufe or "")
    return int(treffer.group(1)) if treffer else None


def _adresse(eltern, strasse, plz, ort):
    adressen = _el(eltern, "adressen")
    if not (strasse or plz or ort):
        return
    adresse = _el(adressen, "adresse")
    _el(adresse, "hauptwohnsitz", "true")
    ort_el = _el(adresse, "ort")
    _el(ort_el, "name", ort)
    _el(ort_el, "plz", plz)
    _el(adresse, "strasse", strasse)


def _kontakte(eltern, telefon="", mobil="", email=""):
    kontakte = _el(eltern, "kontakte")
    for typ, wert in (("kaTelefon", telefon), ("kaTelefon", mobil), ("kaEMail", email)):
        if wert:
            kontakt = _el(kontakte, "kontakt", typ=typ)
            _el(kontakt, "text", wert)


def _person(eltern, *, rufname, vornamen, nachname, geburtsdatum, geschlecht, guid):
    person = _el(eltern, "person")
    _el(person, "rufname", rufname)
    _el(person, "vorname", "")
    _el(person, "vornamen", vornamen)
    _el(person, "nachname", nachname)
    _el(person, "geburtsdatum", geburtsdatum)
    _el(person, "geschlecht", geschlecht)
    _el(person, "guid", guid)
    return person


def _verantwortlicher(eltern, a, nr):
    p = f"eb{nr}_"
    feld = lambda name: getattr(a, p + name) or ""  # noqa: E731
    if not feld("nachname"):
        return

    art = _eb_art(feld("verhaeltnis"), feld("anrede"))
    eb = _el(eltern, "verantwortlicher")
    person = _person(
        eb,
        rufname=feld("vorname"),
        vornamen="",
        nachname=feld("nachname"),
        geburtsdatum=UNBEKANNTES_DATUM,
        geschlecht=_eb_geschlecht(feld("anrede"), art),
        guid=_guid(a, f"eb{nr}"),
    )
    # Ohne abweichende Anschrift wohnt die Person beim Kind.
    if feld("strasse"):
        _adresse(person, feld("strasse"), feld("plz"), feld("ort"))
    else:
        _adresse(person, _kind_strasse(a), a.plz, a.ort)
    _kontakte(person, feld("telefon"), feld("mobil"), feld("email"))

    sorge = "false" if feld("sorgerecht") == "nein" else "true"
    _el(eb, "erziehungsberechtigt", sorge)
    _el(eb, "art", art)
    _el(eb, "erziehungsberechtigtdefault", sorge)
    _el(eb, "artdefault", art)


def _kind_strasse(a):
    return " ".join(t for t in (a.strasse, a.hausnummer) if t)


def _schueler(wurzel, a, schulname):
    s = _el(wurzel, "schueler")

    vornamen = a.schueler_vorname.strip()
    rufname = (a.schueler_rufname or "").strip() or vornamen.split()[0]
    person = _person(
        s,
        rufname=rufname,
        vornamen=vornamen,
        nachname=a.schueler_nachname.strip(),
        geburtsdatum=a.geburtsdatum.isoformat(),
        geschlecht=_geschlecht(a.geschlecht),
        guid=_guid(a, "schueler"),
    )
    _adresse(person, _kind_strasse(a), a.plz, a.ort)
    _kontakte(person)

    beginn = schuljahresbeginn(a)
    _el(s, "aufnahmedatum", beginn.isoformat())
    if a.bisherige_schule:
        vonschule = a.bisherige_schule
        if a.bisherige_schule_ort:
            vonschule += f", {a.bisherige_schule_ort}"
        _el(s, "vonschule", vonschule)

    konfession = KONFESSIONEN.get((a.konfession or "").strip().lower())
    if konfession:
        _el(s, "konfession", konfession[1], kennzahl=konfession[0])
    if a.geburtsort:
        _el(s, "geburtsort", a.geburtsort)
    staat = STAATSANGEHOERIGKEITEN.get((a.staatsangehoerigkeit or "").strip().lower())
    if staat:
        _el(s, "staatsangehoerigkeitiso", staat[1], code=staat[0])

    verantwortliche = _el(s, "verantwortliche")
    for nr in (1, 2):
        _verantwortlicher(verantwortliche, a, nr)

    if a.anmeldeart == "klasse5":
        _el(s, "herkunft", HERKUNFT_KLASSE5[1], kennzahl=HERKUNFT_KLASSE5[0])

    liste = _el(s, "jahrgangsdatenliste")
    jg = _el(liste, "jahrgangsdaten")
    _el(jg, "schulname", schulname)
    _el(jg, "schulgliederungsbezeichnung", _schuljahr(beginn))
    _el(jg, "schulform", SCHULFORM_TEXT, kennzahl=SCHULFORM_KENNZAHL)
    stufe = _jahrgangsstufe(a)
    if stufe is not None:
        _el(jg, "jahrgangsstufe", stufe)


def erzeuge_xml(anmeldungen, schulname):
    """Gibt die DaNiS-XML-Datei (Bytes, UTF-8, CRLF) für die Anmeldungen zurück."""
    wurzel = ET.Element(
        f"{{{NS}}}danis",
        {
            f"{{{XSI}}}schemaLocation": f"{NS} danis.xsd",
            "version": FORMATVERSION,
        },
    )
    for a in anmeldungen:
        _schueler(wurzel, a, schulname)

    ET.indent(wurzel, space="  ")
    text = ET.tostring(wurzel, encoding="unicode", short_empty_elements=True)
    text = '<?xml version="1.0" encoding="UTF-8"?>\n' + text + "\n"
    return text.replace("\n", "\r\n").encode("utf-8")


def dateiname(anmeldung):
    """Dateiname wie bei DaNiS, z. B. 'MustermannMax.xml'."""
    roh = f"{anmeldung.schueler_nachname}{anmeldung.schueler_vorname.split()[0]}"
    roh = roh.translate(str.maketrans({"ä": "ae", "ö": "oe", "ü": "ue", "Ä": "Ae",
                                       "Ö": "Oe", "Ü": "Ue", "ß": "ss"}))
    return (re.sub(r"[^A-Za-z0-9_-]", "", roh) or "anmeldung") + ".xml"
