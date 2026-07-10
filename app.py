"""Flask-Anwendung: Online-Anmeldung für die Weiterverarbeitung in Danis.

Routen:
  /                 -> Startseite mit Erklärung
  /anmeldung        -> öffentliches Anmeldeformular (GET/POST)
  /erfolg           -> Bestätigung nach dem Absenden
  /datenschutz      -> Datenschutzerklärung (Platzhalter)
  /admin/login      -> Login für den Verwaltungsbereich
  /admin            -> Liste aller Anmeldungen (geschützt)
  /admin/<id>       -> Detailansicht einer Anmeldung (geschützt)
  /admin/<id>/loeschen (POST)  -> Anmeldung löschen (DSGVO)
  /admin/export     -> CSV-Export für Danis (geschützt)
"""

import time
from functools import wraps

from flask import (
    Flask,
    Response,
    abort,
    flash,
    redirect,
    render_template,
    request,
    session,
    url_for,
)
from werkzeug.middleware.proxy_fix import ProxyFix
from werkzeug.security import check_password_hash, generate_password_hash

import export
from config import Config
from forms import AnmeldungForm, LoginForm
from models import Anmeldung, db


def create_app(config_object=Config):
    app = Flask(__name__)
    app.config.from_object(config_object)

    # Hinter einem Reverse-Proxy (nginx) die X-Forwarded-* Header auswerten,
    # damit die App HTTPS erkennt (nötig für sichere Cookies / korrekte URLs).
    app.wsgi_app = ProxyFix(app.wsgi_app, x_for=1, x_proto=1, x_host=1)

    db.init_app(app)
    with app.app_context():
        db.create_all()

    # Admin-Passwort nur als Hash im Speicher halten.
    admin_hash = generate_password_hash(app.config["ADMIN_PASSWORD"])

    # ---- Hilfsfunktionen -------------------------------------------------

    def ist_angemeldet():
        return session.get("admin") is True

    def login_erforderlich(view):
        @wraps(view)
        def wrapper(*args, **kwargs):
            if not ist_angemeldet():
                return redirect(url_for("admin_login", next=request.path))
            return view(*args, **kwargs)

        return wrapper

    @app.context_processor
    def inject_globals():
        return {"schulname": app.config["SCHULNAME"]}

    # ---- Öffentliche Routen ----------------------------------------------

    @app.route("/")
    def start():
        return render_template("start.html")

    @app.route("/anmeldung", methods=["GET", "POST"])
    def anmeldung():
        form = AnmeldungForm()
        if form.validate_on_submit():
            # Honeypot: von Bots ausgefülltes verstecktes Feld -> ablehnen.
            if form.website.data:
                abort(400)

            # Einfaches Rate-Limit pro Sitzung.
            jetzt = time.time()
            letzte = session.get("letzte_anmeldung", 0)
            wartezeit = app.config["MIN_SEKUNDEN_ZWISCHEN_ANMELDUNGEN"]
            if jetzt - letzte < wartezeit:
                flash(
                    "Bitte einen Moment warten, bevor eine weitere Anmeldung "
                    "abgeschickt wird.",
                    "fehler",
                )
                return render_template("form.html", form=form)

            eintrag = Anmeldung()
            # Alle Formularfelder außer Steuer-/CSRF-Feldern übernehmen.
            ueberspringen = {"csrf_token", "website", "einwilligung", "submit"}
            for feld in form:
                if feld.name in ueberspringen:
                    continue
                if hasattr(eintrag, feld.name):
                    setattr(eintrag, feld.name, feld.data)
            eintrag.einwilligung = bool(form.einwilligung.data)

            db.session.add(eintrag)
            db.session.commit()

            session["letzte_anmeldung"] = jetzt
            return redirect(url_for("erfolg"))

        return render_template("form.html", form=form)

    @app.route("/erfolg")
    def erfolg():
        return render_template("erfolg.html")

    @app.route("/datenschutz")
    def datenschutz():
        return render_template("datenschutz.html")

    # ---- Admin-Routen ----------------------------------------------------

    @app.route("/admin/login", methods=["GET", "POST"])
    def admin_login():
        form = LoginForm()
        if form.validate_on_submit():
            if check_password_hash(admin_hash, form.passwort.data):
                session["admin"] = True
                ziel = request.args.get("next") or url_for("admin")
                return redirect(ziel)
            flash("Falsches Passwort.", "fehler")
        return render_template("admin_login.html", form=form)

    @app.route("/admin/logout")
    def admin_logout():
        session.pop("admin", None)
        return redirect(url_for("start"))

    @app.route("/admin")
    @login_erforderlich
    def admin():
        anmeldungen = Anmeldung.query.order_by(Anmeldung.erstellt_am.desc()).all()
        return render_template("admin_list.html", anmeldungen=anmeldungen)

    @app.route("/admin/<int:anmeldung_id>")
    @login_erforderlich
    def admin_detail(anmeldung_id):
        eintrag = db.session.get(Anmeldung, anmeldung_id)
        if eintrag is None:
            abort(404)
        return render_template("admin_detail.html", a=eintrag)

    @app.route("/admin/<int:anmeldung_id>/loeschen", methods=["POST"])
    @login_erforderlich
    def admin_loeschen(anmeldung_id):
        eintrag = db.session.get(Anmeldung, anmeldung_id)
        if eintrag is None:
            abort(404)
        db.session.delete(eintrag)
        db.session.commit()
        flash("Anmeldung wurde gelöscht.", "ok")
        return redirect(url_for("admin"))

    @app.route("/admin/export")
    @login_erforderlich
    def admin_export():
        # nur=offen -> nur noch nicht als exportiert markierte Datensätze
        nur_offen = request.args.get("nur") == "offen"
        query = Anmeldung.query.order_by(Anmeldung.schueler_nachname)
        if nur_offen:
            query = query.filter_by(exportiert=False)
        anmeldungen = query.all()

        inhalt = export.erzeuge_csv(anmeldungen)

        # Datensätze als exportiert markieren.
        for a in anmeldungen:
            a.exportiert = True
        db.session.commit()

        return Response(
            inhalt,
            mimetype="text/csv",
            headers={
                "Content-Disposition": "attachment; filename=danis_anmeldungen.csv"
            },
        )

    return app


app = create_app()


if __name__ == "__main__":
    app.run(debug=True)
