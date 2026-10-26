from PIL import Image, ImageChops, ImageOps, ImageEnhance, ImageFilter, ExifTags
from io import BytesIO
import requests
import base64
import re
import json
from collections import deque

OLLAMA_URL = "http://localhost:11434/api/generate"

COMPARE_MODEL_NAME = "PYTHON_PIXEL_ANALYSIS"
VISUAL_MODEL_NAME = "qwen3-vl:8b-instruct"
TEXT_MODEL_NAME = "qwen3-vl:8b-instruct"

COMPARE_IMAGE_SIZE = (448, 448)
ANALYSIS_IMAGE_MAX_SIZE = (896, 896)

DIFFERENCE_PIXEL_THRESHOLD = 25
MINIEM_DIFFERENCE_PERCENTAGE = 0.30
ALIGNMENT_WARNING_PERCENTAGE = 30.0
MIN_REGION_PIXELS = 80
MAX_DIFFERENCE_REGIONS = 8


def uploaded_file_to_image(image_file):
    filename = getattr(image_file, "filename", None)
    content_type = getattr(image_file, "content_type", None)
    file_size = None

    try:
        current_position = image_file.tell()
        image_file.seek(0, 2)
        file_size = image_file.tell()
        image_file.seek(current_position)
    except Exception:
        try:
            current_position = image_file.stream.tell()
            image_file.stream.seek(0, 2)
            file_size = image_file.stream.tell()
            image_file.stream.seek(current_position)
        except Exception:
            file_size = None

    image = Image.open(image_file)
    image.load()

    try:
        original_exif = image.getexif()
    except Exception:
        original_exif = None

    rgb_image = image.convert("RGB")
    rgb_image.info.update(image.info)
    rgb_image.info["_original_format"] = image.format
    rgb_image.info["_original_exif"] = original_exif
    rgb_image.info["_original_filename"] = filename
    rgb_image.info["_original_content_type"] = content_type
    rgb_image.info["_original_file_size_bytes"] = file_size

    return rgb_image


def prepare_compare_image(image):
    return image.resize(COMPARE_IMAGE_SIZE, Image.Resampling.LANCZOS)


def prepare_analysis_image(image):
    prepared_image = image.copy()
    prepared_image.thumbnail(ANALYSIS_IMAGE_MAX_SIZE, Image.Resampling.LANCZOS)
    return prepared_image


def image_to_base64(image):
    image_buffer = BytesIO()
    image.save(image_buffer, format="JPEG", quality=88, optimize=True)
    return base64.b64encode(image_buffer.getvalue()).decode("utf-8")


def ask_vision_model(model_name, prompt, image, keep_alive=0, num_predict=650):
    payload = {
        "model": model_name,
        "prompt": prompt,
        "images": [image_to_base64(image)],
        "stream": False,
        "keep_alive": keep_alive,
        "options": {
            "temperature": 0,
            "num_predict": num_predict
        }
    }

    try:
        response = requests.post(OLLAMA_URL, json=payload, timeout=300)
    except requests.exceptions.RequestException as error:
        return None, {
            "error": "Er ging iets mis bij het verbinden met Ollama.",
            "details": str(error)
        }

    if response.status_code != 200:
        return None, {
            "error": f"Model {model_name} gaf een foutmelding.",
            "status_code": response.status_code,
            "details": response.text
        }

    data = response.json()
    return data.get("response", "").strip(), None



def ask_text_model(model_name, prompt, keep_alive=300, num_predict=700):
    payload = {
        "model": model_name,
        "prompt": prompt,
        "stream": False,
        "keep_alive": keep_alive,
        "options": {
            "temperature": 0,
            "num_predict": num_predict
        }
    }

    try:
        response = requests.post(OLLAMA_URL, json=payload, timeout=300)
    except requests.exceptions.RequestException as error:
        return None, {
            "error": "Er ging iets mis bij het verbinden met Ollama.",
            "details": str(error)
        }

    if response.status_code != 200:
        return None, {
            "error": f"Model {model_name} gaf een foutmelding.",
            "status_code": response.status_code,
            "details": response.text
        }

    data = response.json()
    return data.get("response", "").strip(), None

def clean_text_value(value):
    if value is None:
        return ""

    cleaned_value = str(value).strip()
    cleaned_value = re.sub(r"\s+", " ", cleaned_value)

    return cleaned_value


def normalize_name_for_category(value):
    cleaned_value = clean_text_value(value)

    if not cleaned_value:
        return ""

    cleaned_value = cleaned_value.lower()
    cleaned_value = cleaned_value.replace("_", " ")
    cleaned_value = cleaned_value.strip(".,:;")

    return cleaned_value


def normalize_confidence(confidence):
    normalized_confidence = str(confidence or "").strip().lower()

    if normalized_confidence in ["hoog", "high"]:
        return "hoog"

    if normalized_confidence in ["middel", "medium", "gemiddeld"]:
        return "middel"

    if normalized_confidence in ["laag", "low"]:
        return "laag"

    return "onbekend"


def extract_json_object(raw_answer):
    if not raw_answer:
        return None

    cleaned_answer = raw_answer.strip()
    cleaned_answer = cleaned_answer.replace("```json", "")
    cleaned_answer = cleaned_answer.replace("```", "")
    cleaned_answer = cleaned_answer.strip()

    try:
        return json.loads(cleaned_answer)
    except json.JSONDecodeError:
        pass

    start_index = cleaned_answer.find("{")
    end_index = cleaned_answer.rfind("}")

    if start_index == -1 or end_index == -1 or end_index <= start_index:
        return None

    possible_json = cleaned_answer[start_index:end_index + 1]

    try:
        return json.loads(possible_json)
    except json.JSONDecodeError:
        return None


def parse_forensic_objects(json_object):
    possible_objects = (
        json_object.get("objecten")
        or json_object.get("objects")
        or []
    )

    if not isinstance(possible_objects, list):
        return [], []

    categories = []
    details = []

    for item in possible_objects:
        if not isinstance(item, dict):
            object_name = normalize_name_for_category(item)

            if not object_name:
                continue

            if object_name not in categories:
                categories.append(object_name)

            details.append({
                "name": object_name,
                "category": "overig",
                "count": 1,
                "location_in_image": "onbekend",
                "visibility": "onbekend",
                "forensic_relevance": "onbekend",
                "description": "",
                "confidence": "onbekend",
                "evidence": ""
            })

            continue

        object_name = normalize_name_for_category(
            item.get("naam") or item.get("name")
        )

        if not object_name:
            continue

        category = clean_text_value(
            item.get("categorie") or item.get("category") or "overig"
        ).lower()

        count = item.get("aantal") or item.get("count") or 1

        try:
            count = int(count)
        except Exception:
            count = 1

        location_in_image = clean_text_value(
            item.get("locatie_in_afbeelding")
            or item.get("location_in_image")
            or "onbekend"
        )

        visibility = clean_text_value(
            item.get("zichtbaarheid")
            or item.get("visibility")
            or "onbekend"
        ).lower()

        forensic_relevance = clean_text_value(
            item.get("forensische_relevantie")
            or item.get("forensic_relevance")
            or "onbekend"
        ).lower()

        description = clean_text_value(
            item.get("beschrijving")
            or item.get("description")
            or item.get("evidence")
            or ""
        )

        confidence = normalize_confidence(
            item.get("zekerheid")
            or item.get("confidence")
        )

        if object_name not in categories:
            categories.append(object_name)

        details.append({
            "name": object_name,
            "category": category,
            "count": count,
            "location_in_image": location_in_image,
            "visibility": visibility,
            "forensic_relevance": forensic_relevance,
            "description": description,
            "confidence": confidence,
            "evidence": description
        })

    return categories, details


def parse_location_clues(json_object):
    possible_clues = (
        json_object.get("locatie_aanwijzingen")
        or json_object.get("location_clues")
        or []
    )

    if not isinstance(possible_clues, list):
        return [], []

    categories = []
    details = []

    for item in possible_clues:
        if isinstance(item, dict):
            name = normalize_name_for_category(
                item.get("naam")
                or item.get("name")
                or item.get("type")
                or item.get("categorie")
                or item.get("category")
            )

            category = clean_text_value(
                item.get("categorie")
                or item.get("category")
                or "overig"
            ).lower()

            evidence = clean_text_value(
                item.get("beschrijving")
                or item.get("description")
                or item.get("evidence")
                or ""
            )

            confidence = normalize_confidence(
                item.get("zekerheid")
                or item.get("confidence")
            )
        else:
            name = normalize_name_for_category(item)
            category = "overig"
            evidence = ""
            confidence = "onbekend"

        if not name:
            continue

        if name not in categories:
            categories.append(name)

        details.append({
            "name": name,
            "category": category,
            "confidence": confidence,
            "evidence": evidence
        })

    return categories, details


def parse_scene_context(json_object):
    possible_scene = (
        json_object.get("scene_context")
        or json_object.get("scene")
        or json_object.get("omgeving")
        or []
    )

    if isinstance(possible_scene, str):
        possible_scene = [
            {
                "naam": possible_scene,
                "beschrijving": ""
            }
        ]

    if not isinstance(possible_scene, list):
        possible_scene = []

    categories = []
    details = []

    for item in possible_scene:
        if isinstance(item, dict):
            name = normalize_name_for_category(
                item.get("naam")
                or item.get("name")
                or item.get("type")
                or item.get("categorie")
                or item.get("category")
            )

            evidence = clean_text_value(
                item.get("beschrijving")
                or item.get("description")
                or item.get("evidence")
                or ""
            )

            confidence = normalize_confidence(
                item.get("zekerheid")
                or item.get("confidence")
            )
        else:
            name = normalize_name_for_category(item)
            evidence = ""
            confidence = "onbekend"

        if not name:
            continue

        if name not in categories:
            categories.append(name)

        details.append({
            "name": name,
            "confidence": confidence,
            "evidence": evidence
        })

    if not categories:
        categories = ["onzeker"]
        details = [
            {
                "name": "onzeker",
                "confidence": "laag",
                "evidence": "De omgeving kon niet betrouwbaar worden bepaald."
            }
        ]

    return categories[:3], details[:3]


def parse_text_fragments(raw_answer):
    json_object = extract_json_object(raw_answer)

    if not isinstance(json_object, dict):
        return []

    possible_fragments = (
        json_object.get("text_fragments")
        or json_object.get("tekst_fragments")
        or json_object.get("texts")
        or json_object.get("text")
        or []
    )

    if isinstance(possible_fragments, str):
        possible_fragments = [possible_fragments]

    if not isinstance(possible_fragments, list):
        return []

    fragments = []

    for fragment in possible_fragments:
        if isinstance(fragment, dict):
            cleaned_fragment = clean_text_value(
                fragment.get("text")
                or fragment.get("tekst")
                or fragment.get("value")
                or fragment.get("content")
                or ""
            )
        else:
            cleaned_fragment = clean_text_value(fragment)

        if not cleaned_fragment:
            continue

        if cleaned_fragment.startswith("{") or cleaned_fragment.startswith("["):
            continue

        if len(cleaned_fragment) > 160:
            continue

        if cleaned_fragment not in fragments:
            fragments.append(cleaned_fragment)

    return fragments


def create_ocr_variant(image, variant_name):
    analysis_image = prepare_analysis_image(image)

    if variant_name == "hoog_contrast":
        grayscale_image = ImageOps.grayscale(analysis_image)
        high_contrast_image = ImageEnhance.Contrast(grayscale_image).enhance(2.0)
        sharpened_image = high_contrast_image.filter(ImageFilter.SHARPEN)
        return ImageOps.colorize(sharpened_image, black="black", white="white").convert("RGB")

    return analysis_image


def make_metadata_value_safe(value):
    if value is None:
        return None

    if isinstance(value, (str, int, float, bool)):
        return value

    if isinstance(value, bytes):
        try:
            decoded_value = value.decode("utf-8", errors="ignore").strip()

            if decoded_value:
                return decoded_value

            return f"{len(value)} bytes"
        except Exception:
            return f"{len(value)} bytes"

    if isinstance(value, tuple):
        return [make_metadata_value_safe(item) for item in value]

    if isinstance(value, list):
        return [make_metadata_value_safe(item) for item in value]

    if hasattr(value, "numerator") and hasattr(value, "denominator"):
        try:
            return float(value)
        except Exception:
            return str(value)

    if hasattr(value, "items"):
        return {
            str(key): make_metadata_value_safe(item)
            for key, item in value.items()
        }

    return str(value)


def get_original_exif(image):
    original_exif = image.info.get("_original_exif")

    if original_exif:
        return original_exif

    try:
        return image.getexif()
    except Exception:
        return None


def dms_to_decimal(value):
    if not value or len(value) != 3:
        return None

    try:
        degrees = float(value[0])
        minutes = float(value[1])
        seconds = float(value[2])
    except Exception:
        return None

    return degrees + (minutes / 60.0) + (seconds / 3600.0)


def extract_gps_data(image):
    exif = get_original_exif(image)

    if not exif:
        return {
            "gps_available": False,
            "latitude": None,
            "longitude": None,
            "altitude": None,
            "google_maps_url": None,
            "google_maps_embed_url": None,
            "raw_gps": {}
        }

    gps_ifd = None

    try:
        gps_info_tag = None

        for tag_id, tag_name in ExifTags.TAGS.items():
            if tag_name == "GPSInfo":
                gps_info_tag = tag_id
                break

        if gps_info_tag is not None and hasattr(exif, "get_ifd"):
            gps_ifd = exif.get_ifd(gps_info_tag)

        if not gps_ifd and gps_info_tag is not None:
            gps_ifd = exif.get(gps_info_tag)

    except Exception:
        gps_ifd = None

    if not gps_ifd or not hasattr(gps_ifd, "items"):
        return {
            "gps_available": False,
            "latitude": None,
            "longitude": None,
            "altitude": None,
            "google_maps_url": None,
            "google_maps_embed_url": None,
            "raw_gps": {}
        }

    gps_tags = {}

    for key, value in gps_ifd.items():
        gps_tag_name = ExifTags.GPSTAGS.get(key, key)
        gps_tags[gps_tag_name] = value

    latitude = dms_to_decimal(gps_tags.get("GPSLatitude"))
    longitude = dms_to_decimal(gps_tags.get("GPSLongitude"))

    if latitude is not None and gps_tags.get("GPSLatitudeRef") == "S":
        latitude = -latitude

    if longitude is not None and gps_tags.get("GPSLongitudeRef") == "W":
        longitude = -longitude

    altitude = gps_tags.get("GPSAltitude")

    if altitude is not None:
        try:
            altitude = float(altitude)
        except Exception:
            altitude = None

    gps_available = latitude is not None and longitude is not None

    rounded_latitude = round(latitude, 6) if latitude is not None else None
    rounded_longitude = round(longitude, 6) if longitude is not None else None

    google_maps_url = None
    google_maps_embed_url = None

    if gps_available:
        google_maps_url = f"https://www.google.com/maps?q={rounded_latitude},{rounded_longitude}"
        google_maps_embed_url = f"https://www.google.com/maps?q={rounded_latitude},{rounded_longitude}&z=16&output=embed"

    return {
        "gps_available": gps_available,
        "latitude": rounded_latitude,
        "longitude": rounded_longitude,
        "altitude": round(altitude, 2) if altitude is not None else None,
        "google_maps_url": google_maps_url,
        "google_maps_embed_url": google_maps_embed_url,
        "raw_gps": {
            key: make_metadata_value_safe(value)
            for key, value in gps_tags.items()
        }
    }


def extract_all_metadata(image):
    metadata = {
        "image_properties": {
            "width": image.width,
            "height": image.height,
            "mode": image.mode,
            "format": image.info.get("_original_format"),
            "has_transparency": image.mode in ["RGBA", "LA"] or "transparency" in image.info,
            "file_name": image.info.get("_original_filename"),
            "content_type": image.info.get("_original_content_type"),
            "file_size_bytes": image.info.get("_original_file_size_bytes")
        },
        "gps": extract_gps_data(image),
        "exif": {},
        "image_info": {}
    }

    exif = get_original_exif(image)

    if exif:
        for tag_id, value in exif.items():
            tag_name = ExifTags.TAGS.get(tag_id, str(tag_id))

            if tag_name == "GPSInfo":
                continue

            metadata["exif"][tag_name] = make_metadata_value_safe(value)

    for key, value in image.info.items():
        if str(key).startswith("_original"):
            continue

        metadata["image_info"][str(key)] = make_metadata_value_safe(value)

    return metadata


def format_file_size(size_bytes):
    if size_bytes is None:
        return None

    try:
        size_bytes = int(size_bytes)
    except Exception:
        return None

    if size_bytes < 1024:
        return f"{size_bytes} B"

    if size_bytes < 1024 * 1024:
        return f"{round(size_bytes / 1024, 1)} KB"

    return f"{round(size_bytes / (1024 * 1024), 1)} MB"


def normalize_orientation(value):
    orientation_map = {
        1: "Horizontal",
        2: "Horizontal gespiegeld",
        3: "180 graden gedraaid",
        4: "Verticaal gespiegeld",
        5: "90 graden gespiegeld",
        6: "90 graden gedraaid",
        7: "270 graden gespiegeld",
        8: "270 graden gedraaid"
    }

    return orientation_map.get(value, value)


def build_metadata_cards(metadata):
    gps = metadata.get("gps", {})
    exif = metadata.get("exif", {})
    properties = metadata.get("image_properties", {})

    width = properties.get("width")
    height = properties.get("height")

    file_type = properties.get("format") or properties.get("content_type")
    file_size = format_file_size(properties.get("file_size_bytes"))

    resolution = None

    x_resolution = exif.get("XResolution")
    y_resolution = exif.get("YResolution")

    if x_resolution and y_resolution:
        if x_resolution == y_resolution:
            resolution = f"{x_resolution} dpi"
        else:
            resolution = f"{x_resolution} x {y_resolution} dpi"

    cards = [
        {"label": "FILE NAME", "value": properties.get("file_name")},
        {"label": "FILE TYPE", "value": file_type},
        {"label": "FILE SIZE", "value": file_size},
        {"label": "IMAGE DIMENSIONS", "value": f"{width} x {height}" if width and height else None},
        {"label": "CAPTURE DATE", "value": exif.get("DateTimeOriginal") or exif.get("DateTime")},
        {"label": "CAMERA MAKE", "value": exif.get("Make")},
        {"label": "CAMERA MODEL", "value": exif.get("Model")},
        {"label": "FOCAL LENGTH", "value": exif.get("FocalLength")},
        {"label": "APERTURE", "value": exif.get("FNumber")},
        {"label": "SHUTTER SPEED", "value": exif.get("ExposureTime")},
        {"label": "ISO", "value": exif.get("ISOSpeedRatings")},
        {"label": "WHITE BALANCE", "value": exif.get("WhiteBalance")},
        {"label": "GPS LATITUDE", "value": gps.get("latitude")},
        {"label": "GPS LONGITUDE", "value": gps.get("longitude")},
        {"label": "ORIENTATION", "value": normalize_orientation(exif.get("Orientation"))},
        {"label": "RESOLUTION", "value": resolution},
        {"label": "COLOR SPACE", "value": exif.get("ColorSpace")},
        {"label": "DEVICE MANUFACTURER", "value": exif.get("Make")},
        {"label": "DEVICE MODEL", "value": exif.get("Model")}
    ]

    return [
        card for card in cards
        if card.get("value") is not None and card.get("value") != ""
    ]


def build_metadata_box(metadata):
    gps = metadata.get("gps", {})
    exif = metadata.get("exif", {})
    properties = metadata.get("image_properties", {})

    return {
        "cards": build_metadata_cards(metadata),
        "bestand": {
            "file_name": properties.get("file_name"),
            "file_type": properties.get("format") or properties.get("content_type"),
            "file_size": format_file_size(properties.get("file_size_bytes")),
            "width": properties.get("width"),
            "height": properties.get("height"),
            "dimensions": f"{properties.get('width')} x {properties.get('height')}" if properties.get("width") and properties.get("height") else None,
            "color_mode": properties.get("mode"),
            "transparency": properties.get("has_transparency")
        },
        "camera": {
            "make": exif.get("Make"),
            "model": exif.get("Model"),
            "software": exif.get("Software"),
            "host_computer": exif.get("HostComputer")
        },
        "opname": {
            "capture_date": exif.get("DateTimeOriginal") or exif.get("DateTime"),
            "orientation": normalize_orientation(exif.get("Orientation")),
            "shutter_speed": exif.get("ExposureTime"),
            "aperture": exif.get("FNumber"),
            "iso": exif.get("ISOSpeedRatings"),
            "focal_length": exif.get("FocalLength"),
            "white_balance": exif.get("WhiteBalance"),
            "color_space": exif.get("ColorSpace")
        },
        "gps": {
            "available": gps.get("gps_available"),
            "latitude": gps.get("latitude"),
            "longitude": gps.get("longitude"),
            "altitude": gps.get("altitude"),
            "google_maps_url": gps.get("google_maps_url"),
            "google_maps_embed_url": gps.get("google_maps_embed_url")
        },
        "metadata_count": {
            "exif_fields": len(metadata.get("exif", {})),
            "image_info_fields": len(metadata.get("image_info", {})),
            "gps_fields": len(metadata.get("gps", {}).get("raw_gps", {}))
        }
    }


def analyse_visual_overview(image, keep_alive=300):
    analysis_image = prepare_analysis_image(image)

    prompt = """
Je bent een forensisch beeldanalysemodel. Analyseer de afbeelding zorgvuldig en identificeer alle zichtbare objecten die relevant kunnen zijn voor een forensische analyse.

Je taak is om objecten te herkennen op een praktisch en bruikbaar niveau. Noem duidelijke objecten zoals voertuigen, mensen, dieren, meubels, deuren, ramen, tassen, flessen, telefoons, wapens, kledingstukken, boeken, documenten, elektronische apparaten, gereedschap, verkeersborden, gebouwen, planten en andere herkenbare objecten.

Vermijd extreem gedetailleerde of onbelangrijke onderdelen van grotere objecten.

Noem bijvoorbeeld wel:
- raam
- deur
- auto
- fiets
- tafel
- fles
- telefoon
- persoon
- jas
- tas
- laptop
- boek
- stoel
- helikopter
- boot
- vliegtuig
- verkeersbord
- gereedschap
- gebouw
- dier

Noem niet onnodig kleine onderdelen zoals:
- schroef van een raam
- deurklink, tenzij deze duidelijk belangrijk of opvallend is
- wielbout
- knoop van een jas
- rits van een tas
- krasje op een tafel
- randje van papier
- losse pixels, patronen of texturen

Gebruik gezonde visuele inschatting. Een object moet zelfstandig herkenbaar zijn en mogelijk relevant zijn voor de situatie, locatie, gebeurtenis of bewijsvoering.

Let speciaal op objecten die forensisch interessant kunnen zijn:
- mensen of lichaamsdelen die zichtbaar belangrijk zijn
- voertuigen, vliegtuigen, helikopters, boten, fietsen en andere vervoersmiddelen
- kentekengebieden of herkenbare voertuigdetails
- telefoons, laptops, camera’s of andere elektronische apparaten
- tassen, koffers, dozen of verpakkingen
- documenten, boeken, brieven of papieren
- wapens of voorwerpen die als wapen gebruikt kunnen worden
- gereedschap
- flessen, glazen, blikjes of etenswaren
- kledingstukken en accessoires
- deuren, ramen, poorten, hekken en ingangen
- straatobjecten zoals verkeersborden, lantaarnpalen, bankjes of prullenbakken
- opvallende sporen zoals bloed, glas, rook, vuur, schade of vloeistoffen
- gebouwen, terreinen, landschapselementen of locatie-aanwijzingen

Geef alleen objecten terug die je daadwerkelijk kunt zien of redelijk duidelijk kunt afleiden uit de afbeelding. Fantaseer niet en verzin geen objecten die niet zichtbaar zijn.

Als een object deels zichtbaar is, vermeld dit als “gedeeltelijk zichtbaar”. Als je onzeker bent, vermeld dit met een lage zekerheid.

Analyseer daarnaast ook:
1. locatie-aanwijzingen zoals straatnamen, borden, gebouwen, voertuigen, landschappen, water, spoor, bruggen, winkels of andere herkenbare omgevingskenmerken.
2. de algemene scènecontext zoals kantoor, straat, parkeerplaats, natuurgebied, vliegveld, woning, winkel, voertuigomgeving, spooromgeving of onbekend.

Antwoord uitsluitend met geldige JSON.
Gebruik exact dit formaat:

{
  "objecten": [
    {
      "naam": "objectnaam",
      "categorie": "persoon / voertuig / dier / meubel / elektronica / gebouwdeel / document / kleding / gereedschap / spoor / verpakking / voedsel_drinken / natuur / straatobject / overig",
      "aantal": 1,
      "locatie_in_afbeelding": "linksboven / boven / rechtsboven / links / midden / rechts / linksonder / onder / rechtsonder / voorgrond / achtergrond",
      "zichtbaarheid": "volledig zichtbaar / gedeeltelijk zichtbaar / vaag zichtbaar",
      "forensische_relevantie": "laag / middel / hoog",
      "beschrijving": "korte beschrijving van het object en waarom het mogelijk relevant is",
      "zekerheid": "hoog / middel / laag"
    }
  ],
  "locatie_aanwijzingen": [
    {
      "naam": "korte naam van de aanwijzing",
      "categorie": "bord / gebouw / straat / voertuig / landschap / water / spoor / tekst / overig",
      "beschrijving": "wat zichtbaar is en waarom het een locatie-aanwijzing kan zijn",
      "zekerheid": "hoog / middel / laag"
    }
  ],
  "scene_context": [
    {
      "naam": "korte omschrijving van de omgeving",
      "beschrijving": "waarom deze context wordt herkend",
      "zekerheid": "hoog / middel / laag"
    }
  ],
  "samenvatting": {
    "belangrijkste_objecten": [
      "lijst met belangrijkste objecten"
    ],
    "mogelijke_forensische_aandachtspunten": [
      "korte lijst met objecten of sporen die extra aandacht verdienen"
    ]
  }
}

Belangrijke regels:
1. Herken objecten op normaal menselijk niveau, niet op microscopisch detailniveau.
2. Groepeer kleine onderdelen onder het grotere object wanneer dat logisch is.
3. Noem alleen kleine onderdelen als ze zelfstandig relevant zijn voor forensisch onderzoek.
4. Geef geen lange verhalen, maar gestructureerde en bruikbare JSON.
5. Beschrijf wat zichtbaar is, niet wat je denkt dat er gebeurd is.
6. Als er geen duidelijke objecten zichtbaar zijn, gebruik "objecten": [].
7. Gebruik geen markdown.
8. Gebruik geen uitleg buiten JSON.
"""

    raw_answer, error = ask_vision_model(
        VISUAL_MODEL_NAME,
        prompt,
        analysis_image,
        keep_alive=keep_alive,
        num_predict=1400
    )

    if error:
        return None, error

    json_object = extract_json_object(raw_answer)

    if not isinstance(json_object, dict):
        json_object = {}

    object_categories, object_details = parse_forensic_objects(json_object)
    location_categories, location_details = parse_location_clues(json_object)
    scene_categories, scene_details = parse_scene_context(json_object)

    return {
        "raw_answer": raw_answer,
        "summary": json_object.get("samenvatting", {}),
        "objects": {
            "question": "Welke relevante objecten zijn zichtbaar?",
            "model": VISUAL_MODEL_NAME,
            "objects_visible": "JA" if object_categories else "NEE",
            "categories": object_categories,
            "details": object_details
        },
        "location_clues": {
            "question": "Welke locatie-aanwijzingen zijn zichtbaar?",
            "model": VISUAL_MODEL_NAME,
            "location_clues_visible": "JA" if location_categories else "NEE",
            "categories": location_categories,
            "details": location_details
        },
        "scene_context": {
            "question": "Wat voor soort omgeving is zichtbaar?",
            "model": VISUAL_MODEL_NAME,
            "categories": scene_categories,
            "details": scene_details
        }
    }, None


def analyse_visible_text(image, keep_alive=0):
    prompt = """
Je bent een OCR-systeem.

Je taak:
Lees letterlijk alle tekst die zichtbaar is in de afbeelding.

Belangrijke regels:
- Geef alleen tekst terug die je echt kunt lezen.
- Geef geen beschrijving van de afbeelding.
- Noem geen objecten, tenzij het woord letterlijk zichtbaar op het object staat.
- Vertaal niets.
- Vat niets samen.
- Raad geen tekst die je niet kunt lezen.
- Behoud hoofdletters, kleine letters, cijfers, symbolen en merknamen zo goed mogelijk.
- Tekst op etiketten, verpakkingen, schermen, borden, kleding, documenten en objecten mag worden uitgelezen.
- Als tekst deels zichtbaar maar herkenbaar is, geef het herkenbare fragment terug.
- Als er geen leesbare tekst zichtbaar is, geef een lege lijst terug.

Antwoord uitsluitend met geldige JSON in dit formaat:

{
  "text_fragments": ["tekst 1", "tekst 2"]
}

Geen markdown.
Geen uitleg buiten JSON.
"""

    raw_answers = []
    all_fragments = []

    original_image = create_ocr_variant(image, "origineel")

    raw_answer, error = ask_vision_model(
        TEXT_MODEL_NAME,
        prompt,
        original_image,
        keep_alive=300,
        num_predict=500
    )

    if error:
        return None, error

    raw_answers.append({
        "variant": "origineel",
        "raw_answer": raw_answer
    })

    for fragment in parse_text_fragments(raw_answer):
        if fragment not in all_fragments:
            all_fragments.append(fragment)

    if not all_fragments:
        contrast_image = create_ocr_variant(image, "hoog_contrast")

        raw_answer, error = ask_vision_model(
            TEXT_MODEL_NAME,
            prompt,
            contrast_image,
            keep_alive=keep_alive,
            num_predict=500
        )

        if error:
            return None, error

        raw_answers.append({
            "variant": "hoog_contrast",
            "raw_answer": raw_answer
        })

        for fragment in parse_text_fragments(raw_answer):
            if fragment not in all_fragments:
                all_fragments.append(fragment)

    return {
        "question": "Welke tekst is letterlijk zichtbaar?",
        "model": TEXT_MODEL_NAME,
        "text_visible": "JA" if all_fragments else "NEE",
        "fragments": all_fragments,
        "raw_answers": raw_answers
    }, None



def translate_ocr_text_to_dutch(text_fragments):
    if not text_fragments:
        return {
            "translation_available": False,
            "detected_language": "Onbekend",
            "original_text": "",
            "translated_text": "",
            "message": "Geen OCR tekst beschikbaar om te vertalen."
        }, None

    original_text = "\n".join(text_fragments)

    prompt = f"""
Je bent een vertaalsysteem.

Je taak:
1. Detecteer de taal van de tekst.
2. Vertaal de tekst altijd naar Nederlands.
3. Behoud de betekenis zo goed mogelijk.
4. Vertaal merknamen niet.
5. Vertaal afkortingen alleen als dat logisch is.
6. Voeg geen extra uitleg toe.
7. Verzin geen tekst die niet in de originele tekst staat.
8. Houd elke tekstregel op een aparte regel als dat logisch is.

Originele tekst:
{original_text}

Antwoord uitsluitend met geldige JSON in dit formaat:

{{
  "detected_language": "taalnaam",
  "translated_text": "Nederlandse vertaling"
}}

Geen markdown.
Geen uitleg buiten JSON.
"""

    raw_answer, error = ask_text_model(
        TEXT_MODEL_NAME,
        prompt,
        keep_alive=300,
        num_predict=700
    )

    if error:
        return None, error

    json_object = extract_json_object(raw_answer)

    if not isinstance(json_object, dict):
        return {
            "translation_available": False,
            "detected_language": "Onbekend",
            "original_text": original_text,
            "translated_text": "",
            "raw_answer": raw_answer,
            "message": "De vertaling kon niet betrouwbaar worden verwerkt."
        }, None

    detected_language = clean_text_value(
        json_object.get("detected_language")
        or json_object.get("language")
        or json_object.get("taal")
        or "Onbekend"
    )

    translated_text = clean_text_value(
        json_object.get("translated_text")
        or json_object.get("translation")
        or json_object.get("vertaling")
        or ""
    )

    return {
        "translation_available": bool(translated_text),
        "detected_language": detected_language,
        "original_text": original_text,
        "translated_text": translated_text,
        "raw_answer": raw_answer,
        "message": "Vertaling succesvol uitgevoerd." if translated_text else "Geen vertaling beschikbaar."
    }, None

def build_display_data(filename, metadata_box, visual_overview, text_analysis):
    gps = metadata_box.get("gps", {})
    text_fragments = text_analysis.get("fragments", [])
    object_details = visual_overview["objects"].get("details", [])

    return {
        "page_title": "Analyse details",
        "subtitle": "Bekijk de gevonden metadata, OCR tekst en locatiegegevens.",
        "image_preview": {
            "filename": filename,
            "available": False
        },
        "metadata_cards": metadata_box.get("cards", []),
        "ocr_box": {
            "title": "OCR tekst",
            "text_found": text_analysis.get("text_visible") == "JA",
            "fragments": text_fragments,
            "display_text": "\n".join(text_fragments) if text_fragments else "Geen betrouwbare tekst gevonden."
        },
        "objects_box": {
            "title": "Gedetecteerde objecten",
            "objects_found": visual_overview["objects"].get("objects_visible") == "JA",
            "categories": visual_overview["objects"].get("categories", []),
            "details": object_details
        },
        "map_box": {
            "title": "Map",
            "gps_available": gps.get("available"),
            "latitude": gps.get("latitude"),
            "longitude": gps.get("longitude"),
            "altitude": gps.get("altitude"),
            "google_maps_url": gps.get("google_maps_url"),
            "google_maps_embed_url": gps.get("google_maps_embed_url"),
            "message": "GPS locatie beschikbaar." if gps.get("available") else "Geen GPS beschikbaar voor deze afbeelding."
        },
        "location_details": {
            "location_clues_found": visual_overview["location_clues"].get("location_clues_visible") == "JA",
            "categories": visual_overview["location_clues"].get("categories", []),
            "details": visual_overview["location_clues"].get("details", [])
        },
        "scene_details": {
            "categories": visual_overview["scene_context"].get("categories", []),
            "details": visual_overview["scene_context"].get("details", [])
        }
    }


def analyse_single_image(image, filename=None, release_model_after=True):
    metadata = extract_all_metadata(image)
    metadata_box = build_metadata_box(metadata)

    if filename:
        metadata_box["bestand"]["file_name"] = filename

        for card in metadata_box["cards"]:
            if card.get("label") == "FILE NAME":
                card["value"] = filename

    visual_overview, error = analyse_visual_overview(
        image,
        keep_alive=300 if not release_model_after else 0
    )

    if error:
        return None, error

    text_analysis, error = analyse_visible_text(
        image,
        keep_alive=0 if release_model_after else 300
    )

    if error:
        return None, error

    gps = metadata_box.get("gps", {})
    display_data = build_display_data(filename, metadata_box, visual_overview, text_analysis)

    answer = {
        "samenvatting": "Afbeelding succesvol geanalyseerd.",
        "filename": filename,
        "gps_beschikbaar": gps.get("available"),
        "google_maps_url": gps.get("google_maps_url"),
        "google_maps_embed_url": gps.get("google_maps_embed_url"),
        "tekst_gevonden": text_analysis.get("text_visible") == "JA",
        "objecten_gevonden": visual_overview["objects"].get("objects_visible") == "JA",
        "locatie_aanwijzingen_gevonden": visual_overview["location_clues"].get("location_clues_visible") == "JA",
        "scene": visual_overview["scene_context"].get("categories", [])
    }

    return {
        "antwoord": answer,
        "display_data": display_data,
        "metadata_box": metadata_box,
        "analyse": {
            "objecten": visual_overview["objects"],
            "ocr": {
                "tekst_gevonden": text_analysis.get("text_visible"),
                "fragmenten": text_analysis.get("fragments", []),
                "display_text": "\n".join(text_analysis.get("fragments", [])) if text_analysis.get("fragments", []) else "Geen betrouwbare tekst gevonden."
            },
            "gps": {
                "beschikbaar": gps.get("available"),
                "latitude": gps.get("latitude"),
                "longitude": gps.get("longitude"),
                "altitude": gps.get("altitude"),
                "google_maps_url": gps.get("google_maps_url"),
                "google_maps_embed_url": gps.get("google_maps_embed_url")
            },
            "locatie_aanwijzingen": visual_overview["location_clues"],
            "scene_context": visual_overview["scene_context"]
        },
        "technical_details": {
            "models_used": {
                "visual_analysis": VISUAL_MODEL_NAME,
                "text_extraction": TEXT_MODEL_NAME
            },
            "raw_visual_answer": visual_overview.get("raw_answer"),
            "raw_ocr_answers": text_analysis.get("raw_answers", [])
        }
    }, None


def create_difference_image_gray(image_1, image_2):
    difference = ImageChops.difference(image_1, image_2)
    return ImageOps.grayscale(difference)


def create_difference_image(image_1, image_2):
    difference = ImageChops.difference(image_1, image_2)
    return ImageOps.grayscale(difference)


def create_difference_mask(image_1, image_2, threshold=DIFFERENCE_PIXEL_THRESHOLD):
    grayscale_difference = create_difference_image(image_1, image_2)

    return grayscale_difference.point(
        lambda pixel: 255 if pixel > threshold else 0
    )


def calculate_raw_pixel_difference_percentage(difference_mask):
    pixels = list(difference_mask.getdata())
    total_pixels = len(pixels)
    different_pixels = sum(1 for pixel in pixels if pixel == 255)

    if total_pixels == 0:
        return 0

    percentage = (different_pixels / total_pixels) * 100

    return round(percentage, 2)


def calculate_average_difference_strength(image_1, image_2):
    grayscale_difference = create_difference_image(image_1, image_2)
    pixels = list(grayscale_difference.getdata())

    if not pixels:
        return 0

    return round(sum(pixels) / len(pixels), 2)


def find_difference_regions(difference_mask, min_region_pixels=MIN_REGION_PIXELS, max_regions=MAX_DIFFERENCE_REGIONS):
    width, height = difference_mask.size
    pixels = difference_mask.load()
    visited = set()
    regions = []

    for y in range(height):
        for x in range(width):
            if pixels[x, y] != 255 or (x, y) in visited:
                continue

            queue = deque([(x, y)])
            visited.add((x, y))

            min_x = x
            max_x = x
            min_y = y
            max_y = y
            pixel_count = 0

            while queue:
                current_x, current_y = queue.popleft()
                pixel_count += 1

                min_x = min(min_x, current_x)
                max_x = max(max_x, current_x)
                min_y = min(min_y, current_y)
                max_y = max(max_y, current_y)

                neighbors = [
                    (current_x - 1, current_y),
                    (current_x + 1, current_y),
                    (current_x, current_y - 1),
                    (current_x, current_y + 1)
                ]

                for neighbor_x, neighbor_y in neighbors:
                    if neighbor_x < 0 or neighbor_y < 0 or neighbor_x >= width or neighbor_y >= height:
                        continue

                    if (neighbor_x, neighbor_y) in visited:
                        continue

                    if pixels[neighbor_x, neighbor_y] == 255:
                        visited.add((neighbor_x, neighbor_y))
                        queue.append((neighbor_x, neighbor_y))

            if pixel_count >= min_region_pixels:
                regions.append({
                    "x": min_x,
                    "y": min_y,
                    "width": max_x - min_x + 1,
                    "height": max_y - min_y + 1,
                    "pixel_count": pixel_count
                })

    regions.sort(key=lambda region: region["pixel_count"], reverse=True)

    return regions[:max_regions]


def classify_difference(raw_pixel_difference_percentage):
    if raw_pixel_difference_percentage == 0:
        return "HETZELFDE"

    if raw_pixel_difference_percentage < MINIEM_DIFFERENCE_PERCENTAGE:
        return "MINIEM_VERSCHIL"

    return "VERSCHILLEND"


def analyse_comparison(image_1, image_2):
    compare_image_1 = prepare_compare_image(image_1)
    compare_image_2 = prepare_compare_image(image_2)

    difference_mask = create_difference_mask(compare_image_1, compare_image_2)
    raw_pixel_difference_percentage = calculate_raw_pixel_difference_percentage(difference_mask)
    average_difference_strength = calculate_average_difference_strength(compare_image_1, compare_image_2)
    difference_regions = find_difference_regions(difference_mask)

    result = classify_difference(raw_pixel_difference_percentage)

    alignment_warning = None

    if raw_pixel_difference_percentage >= ALIGNMENT_WARNING_PERCENTAGE:
        alignment_warning = (
            "De afbeeldingen lijken niet exact uitgelijnd. "
            "Ze zijn waarschijnlijk anders gekaderd, verschoven of vanuit een andere hoek genomen. "
            "Pixelverschillen kunnen daardoor groter lijken dan de inhoudelijke verschillen."
        )

    if result == "HETZELFDE":
        validation = "CONSISTENT"
        explanation = "Er zijn geen technische pixelverschillen gevonden boven de ingestelde drempel."
    elif result == "MINIEM_VERSCHIL":
        validation = "KLEIN_VERSCHIL"
        explanation = "Er zijn kleine technische verschillen gevonden. Dit kan door ruis, compressie of lichte beeldafwijking komen."
    else:
        validation = "CONSISTENT"
        explanation = "Er zijn duidelijke technische verschillen gevonden boven de ingestelde drempel."

    return {
        "model": COMPARE_MODEL_NAME,
        "resultaat": result,
        "verschilpercentage": f"{raw_pixel_difference_percentage}%",
        "difference_threshold": DIFFERENCE_PIXEL_THRESHOLD,
        "gemiddelde_verschilsterkte": average_difference_strength,
        "aantal_verschilgebieden": len(difference_regions),
        "verschilgebieden": difference_regions,
        "waarschuwing": alignment_warning,
        "validatie": validation,
        "uitleg": explanation
    }, None


def calculate_list_differences(image_1_values, image_2_values):
    filtered_image_1_values = [
        value for value in image_1_values
        if value.lower() != "onzeker"
    ]

    filtered_image_2_values = [
        value for value in image_2_values
        if value.lower() != "onzeker"
    ]

    set_image_1 = set(filtered_image_1_values)
    set_image_2 = set(filtered_image_2_values)

    return {
        "in_beide": sorted(list(set_image_1 & set_image_2)),
        "alleen_in_afbeelding_1": sorted(list(set_image_1 - set_image_2)),
        "alleen_in_afbeelding_2": sorted(list(set_image_2 - set_image_1))
    }


def calculate_text_differences(image_1_text, image_2_text):
    lookup_image_1 = {text.upper(): text for text in image_1_text}
    lookup_image_2 = {text.upper(): text for text in image_2_text}

    keys_image_1 = set(lookup_image_1.keys())
    keys_image_2 = set(lookup_image_2.keys())

    return {
        "in_beide": [
            lookup_image_1[key] for key in sorted(keys_image_1 & keys_image_2)
        ],
        "alleen_in_afbeelding_1": [
            lookup_image_1[key] for key in sorted(keys_image_1 - keys_image_2)
        ],
        "alleen_in_afbeelding_2": [
            lookup_image_2[key] for key in sorted(keys_image_2 - keys_image_1)
        ]
    }


def compare_two_images(image_1, image_2, image_1_filename=None, image_2_filename=None):
    comparison, error = analyse_comparison(image_1, image_2)

    if error:
        return None, error

    image_1_analysis, error = analyse_single_image(
        image_1,
        filename=image_1_filename,
        release_model_after=False
    )

    if error:
        return None, error

    image_2_analysis, error = analyse_single_image(
        image_2,
        filename=image_2_filename,
        release_model_after=True
    )

    if error:
        return None, error

    image_1_objects = image_1_analysis["analyse"]["objecten"]["categories"]
    image_2_objects = image_2_analysis["analyse"]["objecten"]["categories"]

    image_1_locations = image_1_analysis["analyse"]["locatie_aanwijzingen"]["categories"]
    image_2_locations = image_2_analysis["analyse"]["locatie_aanwijzingen"]["categories"]

    image_1_scene = image_1_analysis["analyse"]["scene_context"]["categories"]
    image_2_scene = image_2_analysis["analyse"]["scene_context"]["categories"]

    image_1_text = image_1_analysis["analyse"]["ocr"]["fragmenten"]
    image_2_text = image_2_analysis["analyse"]["ocr"]["fragmenten"]

    object_differences = calculate_list_differences(image_1_objects, image_2_objects)
    location_differences = calculate_list_differences(image_1_locations, image_2_locations)
    scene_differences = calculate_list_differences(image_1_scene, image_2_scene)
    text_differences = calculate_text_differences(image_1_text, image_2_text)

    gps_1 = image_1_analysis["analyse"]["gps"]
    gps_2 = image_2_analysis["analyse"]["gps"]

    same_gps_coordinates = (
        gps_1.get("latitude") == gps_2.get("latitude")
        and gps_1.get("longitude") == gps_2.get("longitude")
        and gps_1.get("latitude") is not None
        and gps_1.get("longitude") is not None
    )

    reasons = []

    if comparison["resultaat"] == "VERSCHILLEND":
        reasons.append("De technische pixelanalyse vond duidelijke verschillen.")

    if comparison["resultaat"] == "MINIEM_VERSCHIL":
        reasons.append("De technische pixelanalyse vond kleine verschillen.")

    if comparison["resultaat"] == "HETZELFDE":
        reasons.append("Er zijn geen duidelijke technische pixelverschillen gevonden.")

    if object_differences["alleen_in_afbeelding_1"] or object_differences["alleen_in_afbeelding_2"]:
        reasons.append("Er zijn verschillen gevonden in zichtbare objecten.")

    if text_differences["alleen_in_afbeelding_1"] or text_differences["alleen_in_afbeelding_2"]:
        reasons.append("Er zijn verschillen gevonden in leesbare tekst.")

    if location_differences["alleen_in_afbeelding_1"] or location_differences["alleen_in_afbeelding_2"]:
        reasons.append("Er zijn verschillen gevonden in zichtbare locatie-aanwijzingen.")

    if scene_differences["alleen_in_afbeelding_1"] or scene_differences["alleen_in_afbeelding_2"]:
        reasons.append("Er zijn verschillen gevonden in de scènecontext.")

    if gps_1.get("beschikbaar") and gps_2.get("beschikbaar"):
        if same_gps_coordinates:
            reasons.append("Beide afbeeldingen hebben dezelfde GPS-coördinaten.")
        else:
            reasons.append("Beide afbeeldingen hebben GPS-data, maar de coördinaten verschillen.")
    elif gps_1.get("beschikbaar") or gps_2.get("beschikbaar"):
        reasons.append("Slechts één van de twee afbeeldingen bevat GPS-data.")

    if comparison.get("waarschuwing"):
        reasons.append(comparison["waarschuwing"])

    if comparison["resultaat"] == "HETZELFDE":
        summary = "De afbeeldingen lijken technisch hetzelfde."
        risk_level = "laag"
    elif comparison["resultaat"] == "MINIEM_VERSCHIL":
        summary = "De afbeeldingen lijken sterk op elkaar, maar hebben kleine technische verschillen."
        risk_level = "middel"
    else:
        summary = "De afbeeldingen verschillen duidelijk van elkaar."
        risk_level = "hoog"

    return {
        "antwoord": {
            "samenvatting": summary,
            "risiconiveau": risk_level,
            "belangrijkste_redenen": reasons,
            "technische_vergelijking": comparison,
            "objecten": object_differences,
            "tekst": text_differences,
            "locatie_aanwijzingen": location_differences,
            "scene": scene_differences,
            "gps": {
                "afbeelding_1": gps_1,
                "afbeelding_2": gps_2,
                "zelfde_gps_coordinaten": same_gps_coordinates
            }
        },
        "afbeelding_1": {
            "filename": image_1_filename,
            "analyse": image_1_analysis["analyse"],
            "metadata_box": image_1_analysis["metadata_box"],
            "display_data": image_1_analysis["display_data"]
        },
        "afbeelding_2": {
            "filename": image_2_filename,
            "analyse": image_2_analysis["analyse"],
            "metadata_box": image_2_analysis["metadata_box"],
            "display_data": image_2_analysis["display_data"]
        },
        "technical_details": {
            "models_used": {
                "technical_comparison": COMPARE_MODEL_NAME,
                "visual_analysis": VISUAL_MODEL_NAME,
                "text_extraction": TEXT_MODEL_NAME
            }
        }
    }, None


def analyse_visual_image(image, release_model_after=False):
    result, error = analyse_single_image(
        image,
        release_model_after=release_model_after
    )

    if error:
        return None, error

    return {
        "objects": result["analyse"]["objecten"],
        "location_clues": result["analyse"]["locatie_aanwijzingen"],
        "scene_context": result["analyse"]["scene_context"],
        "metadata": result["metadata_box"],
        "metadata_box": result["metadata_box"]
    }, None


def build_findings(comparison, image_1_analysis, image_2_analysis):
    return {
        "message": "Gebruik voortaan compare_two_images() voor volledige vergelijking."
    }


def build_clean_api_response(image_1_filename, image_2_filename, comparison, image_1_analysis, image_2_analysis, findings):
    return {
        "message": "Gebruik voortaan compare_two_images() voor volledige vergelijking."
    }