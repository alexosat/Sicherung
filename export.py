"""CSV-Export der Anmeldungen für den Danis-Import.

Die konkrete Spaltenzuordnung und die Formatoptionen stehen in mapping.py.
Dieses Modul erzeugt anhand dieser Zuordnung eine CSV-Datei als Text.
"""

import csv
import io

import mapping

# Übersetzung der internen Codes in lesbaren Text für die Ausgabe.
ANMELDEART_TEXT = {
    "klasse5": "Neuanmeldung Klasse 5",
    "laufend": "Aufnahme im laufenden Schuljahr",
}


def _formatiere_wert(feldname, wert):
    """Wandelt einen Datenbankwert in den passenden CSV-Text um."""
    if wert is None:
        return ""
    # Datumswerte im deutschen Format ausgeben.
    if feldname in ("geburtsdatum", "aufnahmedatum") and hasattr(wert, "strftime"):
        return wert.strftime(mapping.DATUMSFORMAT)
    if isinstance(wert, bool):
        return "ja" if wert else "nein"
    return str(wert)


def berechne_zusatzfelder(anmeldung):
    """Zusätzliche, abgeleitete Felder, die es nicht direkt im Modell gibt."""
    return {
        "anmeldeart_text": ANMELDEART_TEXT.get(
            anmeldung.anmeldeart, anmeldung.anmeldeart
        ),
        "erstellt_am_text": anmeldung.erstellt_am.strftime(mapping.DATUMSFORMAT)
        if anmeldung.erstellt_am
        else "",
    }


def zeile_fuer(anmeldung):
    """Erzeugt eine Liste von Zellwerten in der Reihenfolge aus mapping.SPALTEN."""
    zusatz = berechne_zusatzfelder(anmeldung)
    zeile = []
    for feldname, _ueberschrift in mapping.SPALTEN:
        if feldname in zusatz:
            wert = zusatz[feldname]
        else:
            wert = getattr(anmeldung, feldname, "")
        zeile.append(_formatiere_wert(feldname, wert))
    return zeile


def erzeuge_csv(anmeldungen):
    """Gibt den CSV-Inhalt (als Bytes) für die übergebenen Anmeldungen zurück.

    Bytes, weil das Encoding (inkl. BOM) in mapping.py konfigurierbar ist und
    Danis/Excel bestimmte Kodierungen erwarten kann.
    """
    puffer = io.StringIO()
    writer = csv.writer(
        puffer,
        delimiter=mapping.CSV_TRENNZEICHEN,
        quoting=csv.QUOTE_MINIMAL,
        lineterminator="\r\n",  # Windows-Zeilenenden für Excel/Danis
    )

    # Kopfzeile mit den Danis-Spaltenüberschriften.
    writer.writerow([ueberschrift for _feld, ueberschrift in mapping.SPALTEN])

    for anmeldung in anmeldungen:
        writer.writerow(zeile_fuer(anmeldung))

    return puffer.getvalue().encode(mapping.CSV_ENCODING)
