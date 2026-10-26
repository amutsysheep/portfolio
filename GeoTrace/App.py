import secrets
import time
from backend.services.Database import get_profile_by_login_id
from flask import Flask, render_template, request, session, redirect, url_for, flash, jsonify
from backend.services.Auth import (
    check_login,
    update_admin_last_login,
    update_user_last_login,
    get_admin_by_login_id,
    get_user_by_login_id
)
from backend.services.Authguard import admin_required, user_required
from backend.services.Email_service import send_2fa_code, send_temporary_password
from backend.services.User_service import (
    get_all_accounts,
    get_account_by_login_id,
    update_account_email,
    generate_temporary_password,
    update_account_password,
    create_account,
    change_own_password
)
from backend.services.Ai import (
    uploaded_file_to_image,
    analyse_comparison,
    analyse_visible_text,
    analyse_visual_image,
    build_findings,
    build_clean_api_response,
    COMPARE_MODEL_NAME,
    VISUAL_MODEL_NAME,
    TEXT_MODEL_NAME,
    analyse_single_image,
    compare_two_images,
    translate_ocr_text_to_dutch
)
import os
import uuid
from werkzeug.utils import secure_filename
from datetime import datetime
from backend.services.Analyse_service import (
    create_foto_upload,
    create_analyse,
    update_foto_upload_analyse_id,
    update_analyse_status,
    save_bestandsinformatie,
    save_analysis_metadata_tables,
    save_afbeelding_vergelijking,
    get_afbeelding_vergelijking_by_id
)

nu = datetime.now()

datum = nu.strftime("%d-%m-%Y")
tijd = nu.strftime("%H:%M")

app = Flask(__name__)
app.secret_key = "geotrace_geheime_sleutel"

UPLOAD_FOLDER = "static/uploads"
ALLOWED_EXTENSIONS = {"jpg", "jpeg", "png"}

app.config["UPLOAD_FOLDER"] = UPLOAD_FOLDER

analysis_results = {}
pending_analyses = {}
comparison_results = {}
pending_comparisons = {}

os.makedirs(UPLOAD_FOLDER, exist_ok=True)


def allowed_file(filename):
    if "." not in filename:
        return False

    extension = filename.rsplit(".", 1)[1].lower()
    return extension in ALLOWED_EXTENSIONS


@app.route("/api/ai/test", methods=["GET"])
def ai_test():
    return jsonify({
        "message": "AI API werkt",
        "models": {
            "comparison": COMPARE_MODEL_NAME,
            "visual": VISUAL_MODEL_NAME,
            "text": TEXT_MODEL_NAME
        }
    })

@app.route("/api/ai/detect-text", methods=["POST"])
def detect_text_api():
    if "image" not in request.files:
        return jsonify({
            "error": "Geef één bestand mee met de key image."
        }), 400

    image_file = request.files["image"]

    try:
        image = uploaded_file_to_image(image_file)
    except Exception as error:
        return jsonify({
            "error": "Het bestand kon niet als afbeelding worden gelezen.",
            "details": str(error)
        }), 400

    text_analysis, error = analyse_visible_text(image, keep_alive=0)

    if error:
        return jsonify(error), 500

    return jsonify({
        "analysis": "Betrouwbare zichtbare tekst uitlezen",
        "image_filename": image_file.filename,
        "text": text_analysis
    })

@app.route("/api/ai/compare-images", methods=["POST"])
def compare_images_api():
    if "image_1" not in request.files or "image_2" not in request.files:
        return jsonify({
            "error": "Geef image_1 en image_2 mee als bestanden."
        }), 400

    image_1_file = request.files["image_1"]
    image_2_file = request.files["image_2"]

    try:
        image_1 = uploaded_file_to_image(image_1_file)
        image_2 = uploaded_file_to_image(image_2_file)
    except Exception as error:
        return jsonify({
            "error": "Een van de bestanden kon niet als afbeelding worden gelezen.",
            "details": str(error)
        }), 400

    comparison, error = analyse_comparison(image_1, image_2)

    if error:
        return jsonify(error), 500

    return jsonify({
        "analysis": "Technische afbeeldingsvergelijking",
        "image_1_filename": image_1_file.filename,
        "image_2_filename": image_2_file.filename,
        "comparison": comparison
    })

@app.route("/api/ai/analyse-images", methods=["POST"])
def analyse_images_api():
    if "image_1" not in request.files or "image_2" not in request.files:
        return jsonify({
            "error": "Geef image_1 en image_2 mee als bestanden."
        }), 400

    image_1_file = request.files["image_1"]
    image_2_file = request.files["image_2"]

    try:
        image_1 = uploaded_file_to_image(image_1_file)
        image_2 = uploaded_file_to_image(image_2_file)
    except Exception as error:
        return jsonify({
            "error": "Een van de bestanden kon niet als afbeelding worden gelezen.",
            "details": str(error)
        }), 400

    analysis_order = []

    analysis_order.append({
        "step": 1,
        "label": "Pixelverschillen controleren",
        "model": COMPARE_MODEL_NAME
    })

    comparison, error = analyse_comparison(image_1, image_2)

    if error:
        return jsonify({
            "failed_step": "Pixelverschillen controleren",
            **error
        }), 500

    analysis_order.append({
        "step": 2,
        "label": "Objectcategorieën en locatie-aanwijzingen in afbeelding 1 analyseren",
        "model": VISUAL_MODEL_NAME
    })

    visual_image_1, error = analyse_visual_image(image_1, release_model_after=False)

    if error:
        return jsonify({
            "failed_step": "Visuele analyse afbeelding 1",
            **error
        }), 500

    analysis_order.append({
        "step": 3,
        "label": "Objectcategorieën en locatie-aanwijzingen in afbeelding 2 analyseren",
        "model": VISUAL_MODEL_NAME
    })

    visual_image_2, error = analyse_visual_image(image_2, release_model_after=True)

    if error:
        return jsonify({
            "failed_step": "Visuele analyse afbeelding 2",
            **error
        }), 500

    analysis_order.append({
        "step": 4,
        "label": "Betrouwbare tekst in afbeelding 1 uitlezen",
        "model": TEXT_MODEL_NAME
    })

    text_image_1, error = analyse_visible_text(image_1, keep_alive=300)

    if error:
        return jsonify({
            "failed_step": "Tekstanalyse afbeelding 1",
            **error
        }), 500

    analysis_order.append({
        "step": 5,
        "label": "Betrouwbare tekst in afbeelding 2 uitlezen",
        "model": TEXT_MODEL_NAME
    })

    text_image_2, error = analyse_visible_text(image_2, keep_alive=0)

    if error:
        return jsonify({
            "failed_step": "Tekstanalyse afbeelding 2",
            **error
        }), 500

    image_1_analysis = {
        "visual": visual_image_1,
        "text": text_image_1
    }

    image_2_analysis = {
        "visual": visual_image_2,
        "text": text_image_2
    }

    findings = build_findings(
        comparison,
        image_1_analysis,
        image_2_analysis
    )

    analysis_order.append({
        "step": 6,
        "label": "Gestructureerde verschillen berekenen",
        "model": "PYTHON"
    })

    clean_response = build_clean_api_response(
        image_1_file.filename,
        image_2_file.filename,
        comparison,
        image_1_analysis,
        image_2_analysis,
        findings
    )

    return jsonify(clean_response)

@app.route("/api/ai/analyse-image", methods=["POST"])
def analyse_single_image_api():
    if "image" not in request.files:
        return jsonify({
            "error": "Geef één afbeelding mee met de key image."
        }), 400

    image_file = request.files["image"]

    try:
        image = uploaded_file_to_image(image_file)
    except Exception as error:
        return jsonify({
            "error": "Het bestand kon niet als afbeelding worden gelezen.",
            "details": str(error)
        }), 400

    result, error = analyse_single_image(
        image,
        filename=image_file.filename,
        release_model_after=True
    )

    if error:
        return jsonify(error), 500

    return jsonify(result)
@app.route("/api/ai/translate-text", methods=["POST"])
def api_translate_text():
    data = request.get_json()

    if not data:
        return jsonify({
            "success": False,
            "error": "Geen JSON body meegestuurd."
        }), 400

    text = data.get("text")

    if not text:
        return jsonify({
            "success": False,
            "error": "Geen tekst meegestuurd."
        }), 400

    if isinstance(text, list):
        text_fragments = text
    else:
        text_fragments = [text]

    translation_result, error = translate_ocr_text_to_dutch(text_fragments)

    if error:
        print("API TRANSLATION ERROR:")
        print(error)

        return jsonify({
            "success": False,
            "error": "Vertaling is mislukt.",
            "details": error
        }), 500

    return jsonify({
        "success": True,
        "translation_available": translation_result.get("translation_available"),
        "detected_language": translation_result.get("detected_language"),
        "original_text": translation_result.get("original_text"),
        "translated_text": translation_result.get("translated_text"),
        "message": translation_result.get("message")
    })


@app.route("/", methods=["GET"])
def home():
    return redirect(url_for("login"))
# def navbar_test():
#     return render_template("navbar_test.html")
def navbar():
    return render_template("Navbar.html")


@app.route("/login", methods=["GET", "POST"])
def login():
    if request.method == "POST":
        email = request.form.get("email")
        password = request.form.get("password")

        login = check_login(email, password)

        if login is None:
            flash("Email of wachtwoord is onjuist.")
            return redirect(url_for("login"))

        code = f"{secrets.randbelow(1000000):06d}"

        session["pending_login_id"] = login["Login_ID"]
        session["pending_rol_id"] = login["Rol_ID"]
        session["pending_email"] = login["Email"]
        session["pending_2fa_code"] = code
        session["pending_2fa_expires"] = time.time() + 300

        try:
            send_2fa_code(login["Email"], code)
        except Exception as error:
            flash("De verificatiemail kon niet worden verstuurd.")
            print(error)
            return redirect(url_for("login"))

        flash("We hebben een verificatiecode naar je email gestuurd.")
        return redirect(url_for("verify_2fa"))

    return render_template("Login.html")

@app.route("/verify-2fa", methods=["GET", "POST"])
def verify_2fa():
    if "pending_login_id" not in session:
        flash("Je moet eerst inloggen.")
        return redirect(url_for("login"))

    if request.method == "POST":
        entered_code = request.form.get("code")
        saved_code = session.get("pending_2fa_code")
        expires_at = session.get("pending_2fa_expires")

        if expires_at is None or time.time() > expires_at:
            session.clear()
            flash("De verificatiecode is verlopen. Log opnieuw in.")
            return redirect(url_for("login"))

        if entered_code != saved_code:
            flash("De verificatiecode is onjuist.")
            return redirect(url_for("verify_2fa"))

        login_id = session.get("pending_login_id")
        rol_id = session.get("pending_rol_id")
        email = session.get("pending_email")

        session.clear()

        session["login_id"] = login_id
        session["rol_id"] = rol_id
        session["email"] = email

        if rol_id == 1:
            session["rol"] = "Admin"

            update_admin_last_login(login_id)

            admin = get_admin_by_login_id(login_id)

            if admin:
                session["Admin_ID"] = admin.get("Admin_ID")
                session["voornaam"] = admin["Voornaam"]
                session["tussenvoegsel"] = admin["Tussenvoegsel"] or ""
                session["achternaam"] = admin["Achternaam"]

            return redirect(url_for("admin_dashboard"))

        if rol_id == 2:
            session["rol"] = "Gebruiker"

            update_user_last_login(login_id)

            user = get_user_by_login_id(login_id)

            if user:
                session["Gebruiker_ID"] = user.get("Gebruiker_ID")
                session["voornaam"] = user["Voornaam"]
                session["tussenvoegsel"] = user["Tussenvoegsel"] or ""
                session["achternaam"] = user["Achternaam"]

            return redirect(url_for("user_dashboard"))

        flash("Onbekende rol. Neem contact op met de beheerder.")
        session.clear()
        return redirect(url_for("login"))

    return render_template("Verify2FA.html")

# ----------\/ Julia \/----------

@app.route("/set-admin")
def set_admin():
    session["rol"] = "Admin"
    return redirect(url_for("admin_dashboard"))


@app.route("/set-gebruiker")
def set_gebruiker():
    session["rol"] = "Gebruiker"
    return redirect(url_for("user_dashboard"))


@app.route("/gebruikers")
def gebruikers():
    if not session.get("login_id"):
        return redirect(url_for("login"))

    if session.get("rol_id") == 1:
        return redirect(url_for("admin_gebruikers"))

    return redirect(url_for("user_dashboard"))


@app.route("/geschiedenis")
def geschiedenis():
    if not session.get("login_id"):
        return redirect(url_for("login"))

    return redirect(url_for("analyse"))



# ---------- \/ Thomas \/ ----------

@app.route("/dashboard")
def dashboard():
    if not session.get("login_id"):
        return redirect(url_for("login"))

    stats = {
        "total_analyses": 50,
        "images_processed": 50,
        "ocr_results": 20,
        "objects_detected": 23
    }

    uploads = [
        {
            "filename": "straatbeeld_01.jpg",
            "date_uploaded": "2026-06-08 09:22",
            "status": "Gereed"
        },
        {
            "filename": "gps_test_02.png",
            "date_uploaded": "2026-06-08 09:45",
            "status": "Gereed"
        },
        {
            "filename": "ocr_scan_03.jpeg",
            "date_uploaded": "2026-06-08 10:03",
            "status": "Gereed"
        }
    ]

    chart_labels = ["Jan", "Feb", "Mrt", "Apr", "Mei", "Jun"]
    analyses_data = [4, 8, 6, 12, 9, 11]
    ocr_data = [2, 5, 4, 8, 6, 9]
    object_data = [1, 3, 5, 6, 7, 8]

    return render_template(
        "Dashboard.html",
        stats=stats,
        uploads=uploads,
        chart_labels=chart_labels,
        analyses_data=analyses_data,
        ocr_data=ocr_data,
        object_data=object_data
    )


@app.route("/admin/dashboard", methods=["GET"])
@admin_required
def admin_dashboard():
    if "login_id" not in session:
        return redirect(url_for("login"))

    if session.get("rol_id") != 1:
        return redirect(url_for("user_dashboard"))

    return render_template("Admin_dashboard.html")

@app.route("/admin/gebruikers", methods=["GET", "POST"])
@admin_required
def admin_gebruikers():
    if request.method == "POST":
        email = request.form.get("email")
        voornaam = request.form.get("voornaam")
        tussenvoegsel = request.form.get("tussenvoegsel") or None
        achternaam = request.form.get("achternaam")
        rol_id = request.form.get("rol_id")

        if not email or not voornaam or not achternaam or not rol_id:
            flash("Vul alle verplichte velden in.")
            return redirect(url_for("admin_gebruikers"))

        rol_id = int(rol_id)

        if rol_id not in [1, 2]:
            flash("Ongeldige rol geselecteerd.")
            return redirect(url_for("admin_gebruikers"))

        temporary_password = generate_temporary_password()

        try:
            create_account(
                email,
                voornaam,
                tussenvoegsel,
                achternaam,
                rol_id,
                temporary_password
            )

            send_temporary_password(email, temporary_password)

        except Exception as error:
            print("CREATE ACCOUNT ERROR:")
            print(type(error))
            print(error)

            flash("Gebruiker aanmaken is mislukt.")
            return redirect(url_for("admin_gebruikers"))

        flash("Gebruiker succesvol aangemaakt. Het tijdelijke wachtwoord is gemaild.")
        return redirect(url_for("admin_gebruikers"))

    accounts = get_all_accounts()
    return render_template("Admin_gebruikers.html", accounts=accounts)

@app.route("/admin/gebruikers/<int:login_id>/email-bewerken", methods=["GET", "POST"])
@admin_required
def admin_edit_email(login_id):
    account = get_account_by_login_id(login_id)

    if account is None:
        flash("Account niet gevonden.")
        return redirect(url_for("admin_gebruikers"))

    if request.method == "POST":
        new_email = request.form.get("email")

        if not new_email:
            flash("Email mag niet leeg zijn.")
            return redirect(url_for("admin_edit_email", login_id=login_id))

        update_account_email(login_id, new_email)
        flash("Email succesvol bijgewerkt.")
        return redirect(url_for("admin_gebruikers"))

    return render_template("AdminEditEmail.html", account=account)

@app.route("/admin/gebruikers/<int:login_id>/wachtwoord-resetten", methods=["GET"])
@admin_required
def admin_reset_password(login_id):
    account = get_account_by_login_id(login_id)

    if account is None:
        flash("Account niet gevonden.")
        return redirect(url_for("admin_gebruikers"))

    temporary_password = generate_temporary_password()

    try:
        update_account_password(login_id, temporary_password)
        send_temporary_password(account["Email"], temporary_password)
    except Exception as error:
        print("PASSWORD RESET ERROR:")
        print(type(error))
        print(error)

        flash("Wachtwoord resetten is mislukt.")
        return redirect(url_for("admin_gebruikers"))

    flash("Nieuw tijdelijk wachtwoord is naar de gebruiker gemaild.")
    return redirect(url_for("admin_gebruikers"))

# @app.route("/gebruiker/dashboard", methods=["GET"])
# @user_required
# def user_dashboard():
#     if "login_id" not in session:
#         return redirect(url_for("login"))
#
#     if session.get("rol_id") != 2:
#         return redirect(url_for("admin_dashboard"))
#
#     return render_template("Dashboard.html")

@app.route("/user-dashboard")
def user_dashboard():
    if not session.get("login_id"):
        return redirect(url_for("login"))

    stats = {
        "total_analyses": 50,
        "images_processed": 50,
        "ocr_results": 20,
        "objects_detected": 23
    }

    uploads = [
        {
            "filename": "straatbeeld_01.jpg",
            "date_uploaded": "2026-06-08 09:22",
            "status": "Gereed"
        },
        {
            "filename": "gps_test_02.png",
            "date_uploaded": "2026-06-08 09:45",
            "status": "Gereed"
        },
        {
            "filename": "ocr_scan_03.jpeg",
            "date_uploaded": "2026-06-08 10:03",
            "status": "Gereed"
        }
    ]

    chart_labels = ["Jan", "Feb", "Mrt", "Apr", "Mei", "Jun"]
    analyses_data = [4, 8, 6, 12, 9, 11]
    ocr_data = [2, 5, 4, 8, 6, 9]
    object_data = [1, 3, 5, 6, 7, 8]

    return render_template(
        "Dashboard.html",
        stats=stats,
        uploads=uploads,
        chart_labels=chart_labels,
        analyses_data=analyses_data,
        ocr_data=ocr_data,
        object_data=object_data
    )


@app.route("/logout", methods=["GET"])
def logout():
    session.clear()
    return redirect(url_for("login"))


# ---------- \/ JULIA \/ ----------
@app.route("/profiel")
def profiel():
    if not session.get("login_id"):
        return redirect(url_for("login"))

    profile = get_profile_by_login_id(
        session.get("login_id"),
        session.get("rol_id")
    )

    if profile is None:
        flash("Profielgegevens konden niet worden gevonden.")
        return redirect(url_for("login"))

    tussenvoegsel = profile["Tussenvoegsel"] or ""

    volledige_naam = f"{profile['Voornaam']} {tussenvoegsel} {profile['Achternaam']}"
    volledige_naam = " ".join(volledige_naam.split())

    return render_template(
        "Profiel.html",
        naam=volledige_naam,
        email=profile["Email"],
        rol=profile["Rol"]
    )

@app.route("/profiel/wachtwoord-wijzigen", methods=["GET", "POST"])
def wachtwoord_wijzigen():
    if "login_id" not in session:
        flash("Je moet eerst inloggen.")
        return redirect(url_for("login"))

    if request.method == "POST":
        current_password = request.form.get("current_password")
        new_password = request.form.get("new_password")
        confirm_password = request.form.get("confirm_password")

        if not current_password or not new_password or not confirm_password:
            flash("Vul alle velden in.")
            return redirect(url_for("wachtwoord_wijzigen"))

        if new_password != confirm_password:
            flash("De nieuwe wachtwoorden komen niet overeen.")
            return redirect(url_for("wachtwoord_wijzigen"))

        if current_password == new_password:
            flash("Het nieuwe wachtwoord mag niet hetzelfde zijn als je huidige wachtwoord.")
            return redirect(url_for("wachtwoord_wijzigen"))

        if len(new_password) < 8:
            flash("Het nieuwe wachtwoord moet minimaal 8 tekens lang zijn.")
            return redirect(url_for("wachtwoord_wijzigen"))

        success, message = change_own_password(
            session["login_id"],
            current_password,
            new_password
        )

        flash(message)

        if success:
            return redirect(url_for("profiel"))

        return redirect(url_for("wachtwoord_wijzigen"))

    return render_template("Wachtwoord_wijzigen.html")


@app.route("/analyse")
def analyse():
    if not session.get("login_id"):
        return redirect(url_for("login"))

    return render_template("Analyse.html")


@app.route("/upload", methods=["GET", "POST"])
def upload():
    if not session.get("login_id"):
        return redirect(url_for("login"))

    if request.method == "POST":
        if "image" not in request.files:
            flash("Geen afbeelding ontvangen.")
            return redirect(url_for("analyse"))

        image_file = request.files["image"]

        if image_file.filename == "":
            flash("Geen bestand geselecteerd.")
            return redirect(url_for("analyse"))

        if not allowed_file(image_file.filename):
            flash("Alleen JPG, JPEG en PNG bestanden zijn toegestaan.")
            return redirect(url_for("analyse"))

        original_filename = secure_filename(image_file.filename)
        extension = original_filename.rsplit(".", 1)[1].lower()
        unique_filename = f"{uuid.uuid4().hex}.{extension}"

        os.makedirs(app.config["UPLOAD_FOLDER"], exist_ok=True)

        save_path = os.path.join(app.config["UPLOAD_FOLDER"], unique_filename)
        image_file.save(save_path)

        analyse_id = uuid.uuid4().hex

        pending_analyses[analyse_id] = {
            "status": "pending",
            "save_path": save_path,
            "original_filename": original_filename,
            "unique_filename": unique_filename
        }

        return redirect(url_for("analyse_loading", analyse_id=analyse_id))

    return render_template("Image_upload.html")


@app.route("/analyse-loading/<analyse_id>")
def analyse_loading(analyse_id):
    if not session.get("login_id"):
        return redirect(url_for("login"))

    if analyse_id not in pending_analyses and analyse_id not in analysis_results:
        flash("Analyse kon niet worden gevonden. Upload opnieuw een afbeelding.")
        return redirect(url_for("analyse"))

    return render_template(
        "Analyse_loading.html",
        analyse_id=analyse_id,
        loading_title="Analyse wordt uitgevoerd",
        loading_subtitle="De afbeelding wordt onderzocht op objecten, tekst en locatiegegevens.",
        start_url=url_for("start_analyse", analyse_id=analyse_id)
    )



@app.route("/vergelijk-upload", methods=["GET", "POST"])
def vergelijk_upload():
    if not session.get("login_id"):
        return redirect(url_for("login"))

    if request.method == "POST":
        image_1_file = request.files.get("image_1")
        image_2_file = request.files.get("image_2")

        if not image_1_file or not image_2_file:
            return render_template(
                "Vergelijk_upload.html",
                error="Upload twee afbeeldingen om te vergelijken."
            )

        if image_1_file.filename == "" or image_2_file.filename == "":
            return render_template(
                "Vergelijk_upload.html",
                error="Selecteer twee geldige afbeeldingen."
            )

        if not allowed_file(image_1_file.filename) or not allowed_file(image_2_file.filename):
            return render_template(
                "Vergelijk_upload.html",
                error="Alleen JPG, JPEG en PNG bestanden zijn toegestaan."
            )

        comparison_id = uuid.uuid4().hex

        image_1_original_filename = secure_filename(image_1_file.filename)
        image_2_original_filename = secure_filename(image_2_file.filename)

        image_1_extension = image_1_original_filename.rsplit(".", 1)[1].lower()
        image_2_extension = image_2_original_filename.rsplit(".", 1)[1].lower()

        image_1_unique_filename = f"{comparison_id}_image_1.{image_1_extension}"
        image_2_unique_filename = f"{comparison_id}_image_2.{image_2_extension}"

        os.makedirs(app.config["UPLOAD_FOLDER"], exist_ok=True)

        image_1_save_path = os.path.join(app.config["UPLOAD_FOLDER"], image_1_unique_filename)
        image_2_save_path = os.path.join(app.config["UPLOAD_FOLDER"], image_2_unique_filename)

        image_1_file.save(image_1_save_path)
        image_2_file.save(image_2_save_path)

        pending_comparisons[comparison_id] = {
            "status": "pending",
            "image_1_save_path": image_1_save_path,
            "image_2_save_path": image_2_save_path,
            "image_1_original_filename": image_1_original_filename,
            "image_2_original_filename": image_2_original_filename,
            "image_1_unique_filename": image_1_unique_filename,
            "image_2_unique_filename": image_2_unique_filename
        }

        return redirect(url_for("vergelijk_loading", comparison_id=comparison_id))

    return render_template("Vergelijk_upload.html")


@app.route("/vergelijk-loading/<comparison_id>")
def vergelijk_loading(comparison_id):
    if not session.get("login_id"):
        return redirect(url_for("login"))

    if comparison_id not in pending_comparisons and comparison_id not in comparison_results:
        flash("Vergelijking kon niet worden gevonden. Upload opnieuw twee afbeeldingen.")
        return redirect(url_for("vergelijk_upload"))

    return render_template(
        "Vergelijk_loading.html",
        comparison_id=comparison_id
    )


@app.route("/vergelijk/start/<comparison_id>", methods=["POST"])
def vergelijk_start(comparison_id):
    if not session.get("login_id"):
        return jsonify({
            "success": False,
            "error": "Niet ingelogd."
        }), 401

    if comparison_id in comparison_results:
        return jsonify({
            "success": True,
            "redirect_url": url_for("vergelijk_detail", comparison_id=comparison_id)
        })

    pending = pending_comparisons.get(comparison_id)

    if pending is None:
        return jsonify({
            "success": False,
            "error": "Vergelijking kon niet worden gevonden."
        }), 404

    if pending.get("status") == "running":
        return jsonify({
            "success": False,
            "running": True,
            "message": "Vergelijking is al bezig."
        }), 202

    pending["status"] = "running"

    try:
        with open(pending["image_1_save_path"], "rb") as image_1_file:
            image_1 = uploaded_file_to_image(image_1_file)

        with open(pending["image_2_save_path"], "rb") as image_2_file:
            image_2 = uploaded_file_to_image(image_2_file)

        image_1.info["_original_filename"] = pending["image_1_original_filename"]
        image_2.info["_original_filename"] = pending["image_2_original_filename"]

        image_1.info["_original_file_size_bytes"] = os.path.getsize(
            pending["image_1_save_path"]
        )
        image_2.info["_original_file_size_bytes"] = os.path.getsize(
            pending["image_2_save_path"]
        )

        result, error = compare_two_images(
            image_1,
            image_2,
            image_1_filename=pending["image_1_original_filename"],
            image_2_filename=pending["image_2_original_filename"]
        )

        if error:
            print("COMPARISON AI ERROR:")
            print(error)

            pending["status"] = "failed"

            return jsonify({
                "success": False,
                "error": "De vergelijking is mislukt.",
                "details": error
            }), 500

        image_1_url = url_for(
            "static",
            filename=f"uploads/{pending['image_1_unique_filename']}"
        )

        image_2_url = url_for(
            "static",
            filename=f"uploads/{pending['image_2_unique_filename']}"
        )

        result["image_1_url"] = image_1_url
        result["image_2_url"] = image_2_url

        gebruiker_id = (
            session.get("Gebruiker_ID")
            or session.get("gebruiker_id")
            or session.get("login_id")
        )

        database_vergelijking_id = save_afbeelding_vergelijking(
            gebruiker_id=gebruiker_id,
            foto_1=image_1_url,
            foto_2=image_2_url,
            originele_bestandsnaam_1=pending["image_1_original_filename"],
            originele_bestandsnaam_2=pending["image_2_original_filename"],
            result=result
        )

        database_vergelijking_id = str(database_vergelijking_id)

        comparison_results[database_vergelijking_id] = result
        pending_comparisons.pop(comparison_id, None)

        return jsonify({
            "success": True,
            "redirect_url": url_for(
                "vergelijk_detail",
                comparison_id=database_vergelijking_id
            )
        })

    except Exception as error:
        print("COMPARISON START ERROR:")
        print(type(error))
        print(error)

        pending["status"] = "failed"

        return jsonify({
            "success": False,
            "error": "Er ging iets mis tijdens het vergelijken.",
            "details": str(error)
        }), 500


@app.route("/vergelijk-detail/<comparison_id>")
def vergelijk_detail(comparison_id):
    if not session.get("login_id"):
        return redirect(url_for("login"))

    result = comparison_results.get(str(comparison_id))

    if result is None:
        vergelijking = get_afbeelding_vergelijking_by_id(comparison_id)

        if vergelijking is None:
            flash("Vergelijking kon niet worden gevonden.")
            return redirect(url_for("vergelijk_upload"))

        result = vergelijking.get("Volledig_resultaat")

        if not result:
            flash("Vergelijkingsresultaat kon niet worden geladen.")
            return redirect(url_for("vergelijk_upload"))

        result["image_1_url"] = vergelijking.get("Foto_1")
        result["image_2_url"] = vergelijking.get("Foto_2")

        comparison_results[str(comparison_id)] = result

    return render_template(
        "Vergelijk_detail.html",
        comparison_id=comparison_id,
        result=result
    )


@app.route("/analyse/start/<analyse_id>", methods=["POST"])
def start_analyse(analyse_id):
    if not session.get("login_id"):
        return jsonify({
            "success": False,
            "error": "Niet ingelogd."
        }), 401

    if analyse_id in analysis_results:
        return jsonify({
            "success": True,
            "redirect_url": url_for("analyse_detail", analyse_id=analyse_id)
        })

    pending_analyse_id = analyse_id
    pending = pending_analyses.get(pending_analyse_id)

    if pending is None:
        return jsonify({
            "success": False,
            "error": "Analyse kon niet worden gevonden."
        }), 404

    if pending.get("status") == "running":
        return jsonify({
            "success": False,
            "running": True,
            "message": "Analyse is al bezig."
        }), 202

    pending["status"] = "running"

    save_path = pending["save_path"]
    original_filename = pending["original_filename"]
    unique_filename = pending["unique_filename"]

    database_analyse_id = None

    try:
        gebruiker_id = (
            session.get("Gebruiker_ID")
            or session.get("gebruiker_id")
            or session.get("login_id")
        )

        foto_id = create_foto_upload(
            foto_pad=f"uploads/{unique_filename}"
        )

        database_analyse_id = create_analyse(
            gebruiker_id=gebruiker_id,
            foto_id=foto_id,
            analyse_status_id=1
        )

        update_foto_upload_analyse_id(
            foto_id=foto_id,
            analyse_id=database_analyse_id
        )

        update_analyse_status(
            analyse_id=database_analyse_id,
            analyse_status_id=2
        )

        with open(save_path, "rb") as saved_file:
            image = uploaded_file_to_image(saved_file)

        image.info["_original_filename"] = original_filename
        image.info["_original_file_size_bytes"] = os.path.getsize(save_path)

        result, error = analyse_single_image(
            image,
            filename=original_filename,
            release_model_after=True
        )

        if error:
            print("AI ERROR:")
            print(error)

            update_analyse_status(
                analyse_id=database_analyse_id,
                analyse_status_id=4
            )

            return jsonify({
                "success": False,
                "error": "De AI-analyse is mislukt."
            }), 500

        save_bestandsinformatie(
            analyse_id=database_analyse_id,
            result=result
        )

        save_analysis_metadata_tables(
            analyse_id=database_analyse_id,
            result=result
        )

        update_analyse_status(
            analyse_id=database_analyse_id,
            analyse_status_id=3
        )

    except Exception as error:
        print("AI UPLOAD ANALYSE ERROR:")
        print(type(error))
        print(error)

        if database_analyse_id is not None:
            update_analyse_status(
                analyse_id=database_analyse_id,
                analyse_status_id=4
            )

        return jsonify({
            "success": False,
            "error": "De afbeelding kon niet worden geanalyseerd."
        }), 500

    database_analyse_id = str(database_analyse_id)

    result["display_data"]["image_preview"] = {
        "available": True,
        "filename": original_filename,
        "url": url_for("static", filename=f"uploads/{unique_filename}")
    }

    analysis_results[database_analyse_id] = result
    pending_analyses.pop(pending_analyse_id, None)

    return jsonify({
        "success": True,
        "redirect_url": url_for("analyse_detail", analyse_id=database_analyse_id)
    })


@app.route("/analyse/map/<analyse_id>")
def analyse_map(analyse_id):
    if not session.get("login_id"):
        return redirect(url_for("login"))

    result = analysis_results.get(analyse_id)

    if result is None:
        flash("Analyse kon niet worden gevonden. Upload opnieuw een afbeelding.")
        return redirect(url_for("upload"))

    map_data = result["display_data"]["map_box"]

    return render_template(
        "Analyse_map.html",
        map_data=map_data,
        analyse_id=analyse_id
    )


@app.route("/analyse-map")
def analyse_map_old():
    if not session.get("login_id"):
        return redirect(url_for("login"))

    flash("Open eerst een analyse om de kaart te bekijken.")
    return redirect(url_for("upload"))

@app.route("/analyse/ocr/<analyse_id>")
def analyse_ocr(analyse_id):
    if not session.get("login_id"):
        return redirect(url_for("login"))

    result = analysis_results.get(analyse_id)

    if result is None:
        flash("Analyse kon niet worden gevonden. Upload opnieuw een afbeelding.")
        return redirect(url_for("upload"))

    display_data = result.get("display_data", {})
    ocr_data = display_data.get("ocr_box", {})
    image_preview = display_data.get("image_preview", {})

    return render_template(
        "Analyse_ocr.html",
        ocr_data=ocr_data,
        image_preview=image_preview,
        analyse_id=analyse_id
    )

@app.route("/analyse/ocr/translate/<analyse_id>", methods=["POST"])
def translate_ocr(analyse_id):
    if not session.get("login_id"):
        return jsonify({
            "success": False,
            "error": "Niet ingelogd."
        }), 401

    result = analysis_results.get(analyse_id)

    if result is None:
        return jsonify({
            "success": False,
            "error": "Analyse kon niet worden gevonden."
        }), 404

    try:
        ocr_data = result.get("display_data", {}).get("ocr_box", {})
        text_fragments = ocr_data.get("fragments", [])

        if not text_fragments:
            return jsonify({
                "success": False,
                "translation_available": False,
                "detected_language": "Onbekend",
                "translated_text": "",
                "message": "Geen OCR tekst beschikbaar om te vertalen."
            }), 200

        translation_result, error = translate_ocr_text_to_dutch(text_fragments)

        if error:
            print("TRANSLATION ERROR:")
            print(error)

            return jsonify({
                "success": False,
                "error": "Vertaling is mislukt.",
                "details": error
            }), 500

        result["translation"] = translation_result

        return jsonify({
            "success": True,
            "translation_available": translation_result.get("translation_available"),
            "detected_language": translation_result.get("detected_language"),
            "original_text": translation_result.get("original_text"),
            "translated_text": translation_result.get("translated_text"),
            "message": translation_result.get("message")
        })

    except Exception as error:
        print("TRANSLATION ROUTE ERROR:")
        print(type(error))
        print(error)

        return jsonify({
            "success": False,
            "error": "Er ging iets mis tijdens het vertalen.",
            "details": str(error)
        }), 500

def build_metadata_sections_from_result(result):
    display_data = result.get("display_data", {})
    metadata_cards = display_data.get("metadata_cards", [])

    if not metadata_cards:
        return []

    return [
        {
            "title": "Metadata",
            "items": [
                {
                    "label": card.get("label"),
                    "value": card.get("value")
                }
                for card in metadata_cards
                if card.get("value") is not None and str(card.get("value")).strip() != ""
            ]
        }
    ]

@app.route("/analyse-detail/<analyse_id>")
def analyse_detail(analyse_id):
    if not session.get("login_id"):
        return redirect(url_for("login"))

    result = analysis_results.get(analyse_id)

    if result is None:
        flash("Analyse kon niet worden gevonden. Upload opnieuw een afbeelding.")
        return redirect(url_for("upload"))

    display_data = result.get("display_data", {})
    metadata_sections = build_metadata_sections_from_result(result)

    return render_template(
        "Analyse_detail.html",
        result=result,
        analyse=result,
        analyse_id=analyse_id,
        display_data=display_data,
        metadata_sections=metadata_sections
    )


@app.route("/analyse/metadata/<analyse_id>")
def analyse_metadata(analyse_id):
    if not session.get("login_id"):
        return redirect(url_for("login"))

    result = analysis_results.get(analyse_id)

    if result is None:
        flash("Analyse kon niet worden gevonden. Upload opnieuw een afbeelding.")
        return redirect(url_for("upload"))

    metadata_sections = build_metadata_sections_from_result(result)

    return render_template(
        "Analyse_metadata.html",
        result=result,
        analyse=result,
        analyse_id=analyse_id,
        metadata_sections=metadata_sections
    )


@app.route("/analyse-detail/metadata")
def analyse_metadata_old():
    if not session.get("login_id"):
        return redirect(url_for("login"))

    flash("Open eerst een analyse om metadata te bekijken.")
    return redirect(url_for("upload"))

@app.route("/tijdlijn")
def tijdlijn():
    if not session.get("login_id"):
        return redirect(url_for("login"))

    gebruiker_naam = (
        session.get("naam")
        or session.get("email")
        or "Onbekende gebruiker"
    )

    huidige_tijd = datetime.now().strftime("%d-%m-%Y %H:%M")

    timeline_items = [
        {
            "datum": huidige_tijd,
            "titel": "Afbeelding geüpload",
            "type": "Upload",
            "beschrijving": "De gebruiker heeft een afbeelding toegevoegd voor analyse.",
            "gebruiker": gebruiker_naam,
            "icon": "image"
        },
        {
            "datum": huidige_tijd,
            "titel": "Metadata uitgelezen",
            "type": "Metadata",
            "beschrijving": "Bestandsinformatie, cameragegevens en GPS-data zijn gecontroleerd.",
            "gebruiker": gebruiker_naam,
            "icon": "metadata"
        },
        {
            "datum": huidige_tijd,
            "titel": "OCR analyse uitgevoerd",
            "type": "OCR",
            "beschrijving": "Zichtbare tekst in de afbeelding is onderzocht.",
            "gebruiker": gebruiker_naam,
            "icon": "ocr"
        },
        {
            "datum": huidige_tijd,
            "titel": "Objectdetectie uitgevoerd",
            "type": "Objecten",
            "beschrijving": "Zichtbare objecten zijn herkend en forensisch beoordeeld.",
            "gebruiker": gebruiker_naam,
            "icon": "object"
        },
        {
            "datum": huidige_tijd,
            "titel": "Analyse afgerond",
            "type": "Resultaat",
            "beschrijving": "De analyse is afgerond en de resultaten zijn beschikbaar.",
            "gebruiker": gebruiker_naam,
            "icon": "check"
        }
    ]

    return render_template(
        "Tijdlijn.html",
        timeline_items=timeline_items
    )

# ---------- \/ ONDERAAN LATEN STAAN \/ ----------
if __name__ == "__main__":
    app.run(debug=True, port=5000)