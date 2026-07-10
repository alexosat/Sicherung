# -*- coding: utf-8 -*-
"""
====================================================================
 DANIS-EXPORT: SPALTENZUORDNUNG  ->  HIER ANPASSEN!
====================================================================

Diese Datei legt fest, wie die Anmeldedaten als CSV exportiert werden.
Das genaue Importformat von Danis kann von Schule zu Schule/Version zu
Version abweichen. Sobald euch die echte Danis-Importvorlage (oder eine
Beispiel-CSV aus Danis) vorliegt, müsst ihr NUR diese Datei anpassen –
der Rest der Anwendung bleibt unverändert.

SO PASST IHR AN:
----------------
1. Öffnet in Danis eine leere/Beispiel-Importdatei und schaut euch die
   exakten Spaltenüberschriften an (Groß-/Kleinschreibung beachten!).
2. Ersetzt unten in SPALTEN jeweils den Text rechts ("Danis-Spalte")
   durch die exakte Überschrift aus Danis.
3. Nicht benötigte Zeilen einfach löschen; Reihenfolge = Reihenfolge in
   der CSV. Neue Spalten hinzufügen: (interner_feldname, "Danis-Spalte").
4. Passt bei Bedarf CSV_TRENNZEICHEN / CSV_ENCODING / DATUMSFORMAT an.

Die "internen Feldnamen" (links) sind die Attribute aus models.py plus
einige berechnete Felder (siehe berechne_zusatzfelder in export.py).
"""

# --- CSV-Formatoptionen ---------------------------------------------------

# Deutsches Excel erwartet i. d. R. das Semikolon als Trennzeichen.
CSV_TRENNZEICHEN = ";"

# "utf-8-sig" schreibt ein BOM, damit Excel Umlaute korrekt anzeigt.
# Falls Danis Windows-Kodierung erwartet, hier "cp1252" eintragen.
CSV_ENCODING = "utf-8-sig"

# Datumsformat für den Export. Deutsch üblich: Tag.Monat.Jahr.
DATUMSFORMAT = "%d.%m.%Y"


# --- Spaltenzuordnung -----------------------------------------------------
# Format je Zeile:  ("interner_feldname", "Überschrift in der CSV/Danis")
# Reihenfolge dieser Liste = Spaltenreihenfolge in der Exportdatei.

SPALTEN = [
    ("schueler_nachname", "Nachname"),
    ("schueler_vorname", "Vorname"),
    ("schueler_rufname", "Rufname"),
    ("geschlecht", "Geschlecht"),
    ("geburtsdatum", "Geburtsdatum"),
    ("geburtsort", "Geburtsort"),
    ("staatsangehoerigkeit", "Staatsangehörigkeit"),
    ("konfession", "Konfession"),
    ("verkehrssprache", "Verkehrssprache"),
    ("strasse", "Straße"),
    ("hausnummer", "Hausnummer"),
    ("plz", "PLZ"),
    ("ort", "Ort"),
    ("klassenstufe", "Klassenstufe"),
    ("aufnahmedatum", "Aufnahmedatum"),
    ("fremdsprache1", "1. Fremdsprache"),
    ("fremdsprache2", "2. Fremdsprache"),
    ("ganztag", "Ganztag"),
    ("bisherige_schule", "Bisherige Schule"),
    ("bisherige_schule_ort", "Bisherige Schule Ort"),
    # Erziehungsberechtigte(r) 1
    ("eb1_anrede", "EB1 Anrede"),
    ("eb1_nachname", "EB1 Nachname"),
    ("eb1_vorname", "EB1 Vorname"),
    ("eb1_verhaeltnis", "EB1 Verhältnis"),
    ("eb1_strasse", "EB1 Straße"),
    ("eb1_plz", "EB1 PLZ"),
    ("eb1_ort", "EB1 Ort"),
    ("eb1_telefon", "EB1 Telefon"),
    ("eb1_mobil", "EB1 Mobil"),
    ("eb1_email", "EB1 E-Mail"),
    ("eb1_sorgerecht", "EB1 Sorgerecht"),
    # Erziehungsberechtigte(r) 2
    ("eb2_anrede", "EB2 Anrede"),
    ("eb2_nachname", "EB2 Nachname"),
    ("eb2_vorname", "EB2 Vorname"),
    ("eb2_verhaeltnis", "EB2 Verhältnis"),
    ("eb2_strasse", "EB2 Straße"),
    ("eb2_plz", "EB2 PLZ"),
    ("eb2_ort", "EB2 Ort"),
    ("eb2_telefon", "EB2 Telefon"),
    ("eb2_mobil", "EB2 Mobil"),
    ("eb2_email", "EB2 E-Mail"),
    ("eb2_sorgerecht", "EB2 Sorgerecht"),
    # Sonstiges
    ("notfallkontakt", "Notfallkontakt"),
    ("geschwister", "Geschwister an der Schule"),
    ("bemerkungen", "Bemerkungen"),
    ("anmeldeart_text", "Art der Anmeldung"),
    ("erstellt_am_text", "Eingegangen am"),
]
