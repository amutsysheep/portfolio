from functools import wraps
from flask import session, redirect, url_for, flash


def login_required(route_function):
    @wraps(route_function)
    def wrapper(*args, **kwargs):
        if "login_id" not in session:
            flash("Je moet eerst inloggen.")
            return redirect(url_for("login"))

        return route_function(*args, **kwargs)

    return wrapper


def admin_required(route_function):
    @wraps(route_function)
    def wrapper(*args, **kwargs):
        if "login_id" not in session:
            flash("Je moet eerst inloggen.")
            return redirect(url_for("login"))

        if session.get("rol_id") != 1:
            flash("Je hebt geen toegang tot deze pagina.")
            return redirect(url_for("dashboard"))

        return route_function(*args, **kwargs)

    return wrapper


def user_required(route_function):
    @wraps(route_function)
    def wrapper(*args, **kwargs):
        if "login_id" not in session:
            flash("Je moet eerst inloggen.")
            return redirect(url_for("login"))

        if session.get("rol_id") != 2:
            flash("Je hebt geen toegang tot deze pagina.")
            return redirect(url_for("dashboard"))

        return route_function(*args, **kwargs)

    return wrapper