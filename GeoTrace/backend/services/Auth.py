from argon2 import PasswordHasher
from argon2.exceptions import VerifyMismatchError, InvalidHashError
from backend.services.Database import get_connection

password_hasher = PasswordHasher()


def get_login_by_email(email):
    connection = get_connection()
    cursor = connection.cursor(dictionary=True)

    query = """
        SELECT Login_ID, Rol_ID, Email, Huidig_wachtwoord
        FROM login
        WHERE Email = %s
        LIMIT 1
    """

    cursor.execute(query, (email,))
    login = cursor.fetchone()

    cursor.close()
    connection.close()

    return login


def check_login(email, password):
    login = get_login_by_email(email)

    if login is None:
        return None

    try:
        password_is_correct = password_hasher.verify(
            login["Huidig_wachtwoord"],
            password
        )
    except VerifyMismatchError:
        return None
    except InvalidHashError:
        return None

    if not password_is_correct:
        return None

    return login


def update_admin_last_login(login_id):
    connection = get_connection()
    cursor = connection.cursor()

    query = """
        UPDATE admins
        SET Laatste_inlogd = NOW()
        WHERE Login_ID = %s
    """

    cursor.execute(query, (login_id,))
    connection.commit()

    cursor.close()
    connection.close()

def update_user_last_login(login_id):
    connection = get_connection()
    cursor = connection.cursor()

    query = """
    UPDATE gebruikers
    SET Laatste_inlog = NOW()
    WHERE Login_ID = %s \
  """

    cursor.execute(query, (login_id,))
    connection.commit()

    cursor.close()
    connection.close()

def get_admin_by_login_id(login_id):
        connection = get_connection()
        cursor = connection.cursor(dictionary=True)

        query = """
                SELECT Admin_ID, Login_ID, Voornaam, Tussenvoegsel, Achternaam, Laatste_inlogd
                FROM admins
                WHERE Login_ID = %s LIMIT 1 \
                """

        cursor.execute(query, (login_id,))
        admin = cursor.fetchone()

        cursor.close()
        connection.close()

        return admin

def get_user_by_login_id(login_id):
        connection = get_connection()
        cursor = connection.cursor(dictionary=True)

        query = """
                SELECT Gebruiker_ID, Login_ID, Voornaam, Tussenvoegsel, Achternaam, Laatste_inlog
                FROM gebruikers
                WHERE Login_ID = %s LIMIT 1 \
                """

        cursor.execute(query, (login_id,))
        user = cursor.fetchone()

        cursor.close()
        connection.close()

        return user