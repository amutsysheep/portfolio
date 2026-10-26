import json
from backend.services.Database import get_connection


def create_foto_upload(foto_pad):
    connection = get_connection()
    cursor = connection.cursor()

    query = """
        INSERT INTO foto_uploads (
            Analyse_ID,
            Foto
        )
        VALUES (%s, %s)
    """

    values = (
        0,
        foto_pad
    )

    cursor.execute(query, values)
    connection.commit()

    foto_id = cursor.lastrowid

    cursor.close()
    connection.close()

    return foto_id


def create_analyse(gebruiker_id, foto_id, analyse_status_id=1):
    connection = get_connection()
    cursor = connection.cursor()

    query = """
        INSERT INTO analyse (
            Gebruiker_ID,
            Foto_ID,
            OCR_ID,
            Datum,
            Analyse_status
        )
        VALUES (%s, %s, %s, NOW(), %s)
    """

    values = (
        gebruiker_id,
        foto_id,
        None,
        analyse_status_id
    )

    cursor.execute(query, values)
    connection.commit()

    analyse_id = cursor.lastrowid

    cursor.close()
    connection.close()

    return analyse_id


def update_foto_upload_analyse_id(foto_id, analyse_id):
    connection = get_connection()
    cursor = connection.cursor()

    query = """
        UPDATE foto_uploads
        SET Analyse_ID = %s
        WHERE Foto_ID = %s
    """

    values = (
        analyse_id,
        foto_id
    )

    cursor.execute(query, values)
    connection.commit()

    cursor.close()
    connection.close()


def update_analyse_status(analyse_id, analyse_status_id):
    connection = get_connection()
    cursor = connection.cursor()

    query = """
        UPDATE analyse
        SET Analyse_status = %s
        WHERE Analyse_ID = %s
    """

    values = (
        analyse_status_id,
        analyse_id
    )

    cursor.execute(query, values)
    connection.commit()

    cursor.close()
    connection.close()

def save_bestandsinformatie(analyse_id, result):
    connection = get_connection()
    cursor = connection.cursor()

    metadata_box = result.get("metadata_box", {})
    bestand = metadata_box.get("bestand", {})
    opname = metadata_box.get("opname", {})

    bestandsnaam = bestand.get("file_name")
    bestandstype = bestand.get("file_type")
    bestandsgrootte = bestand.get("file_size")
    afbeelding_hoogte = bestand.get("height")
    afbeelding_breedte = bestand.get("width")
    kleurdiepte = bestand.get("color_mode")
    kleurprofiel = opname.get("color_space")
    compressie = None
    aangemaakt_op = opname.get("capture_date")
    gewijzigd_op = None

    aspect_ratio = None

    if afbeelding_breedte and afbeelding_hoogte:
        try:
            aspect_ratio = round(float(afbeelding_breedte) / float(afbeelding_hoogte), 2)
        except Exception:
            aspect_ratio = None

    foto_rotatie = opname.get("orientation")
    foto_orientatie = opname.get("orientation")
    pixel_density = None
    caption = None

    query = """
        INSERT INTO bestandsinformatie (
            Analyse_ID,
            Bestandsnaam,
            Bestandstype,
            Bestandsgrootte,
            Afbeelding_hoogte,
            Afbeelding_breedte,
            Kleurdiepte,
            Kleurprofiel,
            Compressie,
            Aangemaakt_op,
            Gewijzigd_op,
            Aspect_ratio,
            Foto_rotatie,
            Foto_orientatie,
            Pixel_density,
            Caption
        )
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """

    values = (
        analyse_id,
        bestandsnaam,
        bestandstype,
        bestandsgrootte,
        afbeelding_hoogte,
        afbeelding_breedte,
        kleurdiepte,
        kleurprofiel,
        compressie,
        aangemaakt_op,
        gewijzigd_op,
        aspect_ratio,
        foto_rotatie,
        foto_orientatie,
        pixel_density,
        caption
    )

    cursor.execute(query, values)
    connection.commit()

    cursor.close()
    connection.close()

def save_analysis_metadata_tables(analyse_id, result):
    connection = get_connection()
    cursor = connection.cursor()

    metadata_box = result.get("metadata_box", {})
    camera = metadata_box.get("camera", {})
    opname = metadata_box.get("opname", {})

    query_cameragegevens = """
        INSERT INTO cameragegevens (
            Analyse_ID,
            Camera_merk,
            Camera_model,
            Lens_model,
            Serienummer_camera,
            Serienummer_lens,
            Software_firmware
        )
        VALUES (%s, %s, %s, %s, %s, %s, %s)
    """

    values_cameragegevens = (
        analyse_id,
        camera.get("make"),
        camera.get("model"),
        None,
        None,
        None,
        camera.get("software")
    )

    cursor.execute(query_cameragegevens, values_cameragegevens)

    query_camerainstellingen = """
        INSERT INTO camerainstellingen (
            Analyse_ID,
            Sluitertijd,
            Diafragma,
            ISO,
            Brandpuntafstand,
            Belichtingscompensatie,
            Belichtingsmodus,
            Meetmethode,
            Flits,
            Witbalans,
            Focusafstand,
            Digitale_zoom,
            Scenemodus,
            HDR,
            Burst_mode,
            Panorama,
            Night_mode,
            Portrait_mode,
            Live_foto_info
        )
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """

    values_camerainstellingen = (
        analyse_id,
        opname.get("shutter_speed"),
        opname.get("aperture"),
        opname.get("iso"),
        opname.get("focal_length"),
        None,
        None,
        None,
        None,
        opname.get("white_balance"),
        None,
        None,
        None,
        None,
        None,
        None,
        None,
        None,
        None
    )

    cursor.execute(query_camerainstellingen, values_camerainstellingen)

    query_datum_tijd = """
        INSERT INTO datum_tijd (
            Analyse_ID,
            Date_time_original,
            Create_date,
            Modified_data,
            Sub_sec_time,
            Time_zone_offset,
            Offset_time_original
        )
        VALUES (%s, %s, %s, %s, %s, %s, %s)
    """

    capture_date = opname.get("capture_date")

    values_datum_tijd = (
        analyse_id,
        capture_date,
        capture_date,
        None,
        None,
        None,
        None
    )

    cursor.execute(query_datum_tijd, values_datum_tijd)

    query_bewerking_softwaredata = """
        INSERT INTO bewerking_softwaredata (
            Analyse_ID,
            Software,
            Creator_tool,
            Date_modified,
            Export_settings,
            Crop_info,
            Exposure,
            Brilliance,
            Highlights,
            Shadows,
            Contrast,
            Brightness,
            Black_point,
            Saturation,
            Vibrance,
            Warmth,
            Tint,
            Sharpness,
            Definition,
            Noise_reduction,
            Vignette
        )
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """

    values_bewerking_softwaredata = (
        analyse_id,
        camera.get("software"),
        camera.get("host_computer"),
        None,
        None,
        None,
        None,
        None,
        None,
        None,
        None,
        None,
        None,
        None,
        None,
        None,
        None,
        None,
        None,
        None,
        None
    )

    cursor.execute(query_bewerking_softwaredata, values_bewerking_softwaredata)

    connection.commit()

    cursor.close()
    connection.close()

def save_afbeelding_vergelijking(
    gebruiker_id,
    foto_1,
    foto_2,
    originele_bestandsnaam_1,
    originele_bestandsnaam_2,
    result
):
    connection = get_connection()
    cursor = connection.cursor()

    antwoord = result.get("antwoord", {})
    technische_vergelijking = antwoord.get("technische_vergelijking", {})

    query = """
        INSERT INTO afbeelding_vergelijkingen (
            Gebruiker_ID,
            Foto_1,
            Foto_2,
            Originele_bestandsnaam_1,
            Originele_bestandsnaam_2,
            Resultaat,
            Verschilpercentage,
            Risiconiveau,
            Samenvatting,
            Volledig_resultaat,
            Datum,
            Status
        )
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NOW(), %s)
    """

    values = (
        gebruiker_id,
        foto_1,
        foto_2,
        originele_bestandsnaam_1,
        originele_bestandsnaam_2,
        technische_vergelijking.get("resultaat"),
        technische_vergelijking.get("verschilpercentage"),
        antwoord.get("risiconiveau"),
        antwoord.get("samenvatting"),
        json.dumps(result, ensure_ascii=False),
        "Gereed"
    )

    cursor.execute(query, values)
    connection.commit()

    vergelijking_id = cursor.lastrowid

    cursor.close()
    connection.close()

    return vergelijking_id


def get_afbeelding_vergelijking_by_id(vergelijking_id):
    connection = get_connection()
    cursor = connection.cursor(dictionary=True)

    query = """
        SELECT *
        FROM afbeelding_vergelijkingen
        WHERE Vergelijking_ID = %s
    """

    cursor.execute(query, (vergelijking_id,))
    vergelijking = cursor.fetchone()

    cursor.close()
    connection.close()

    if vergelijking and vergelijking.get("Volledig_resultaat"):
        vergelijking["Volledig_resultaat"] = json.loads(vergelijking["Volledig_resultaat"])

    return vergelijking


def get_afbeelding_vergelijkingen_by_gebruiker(gebruiker_id):
    connection = get_connection()
    cursor = connection.cursor(dictionary=True)

    query = """
        SELECT *
        FROM afbeelding_vergelijkingen
        WHERE Gebruiker_ID = %s
        ORDER BY Datum DESC
    """

    cursor.execute(query, (gebruiker_id,))
    vergelijkingen = cursor.fetchall()

    cursor.close()
    connection.close()

    return vergelijkingen