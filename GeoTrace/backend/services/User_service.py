import secrets
import string
from argon2 import PasswordHasher
from argon2.exceptions import VerifyMismatchError, InvalidHashError
from backend.services.Database import get_connection

password_hasher = PasswordHasher()



def get_all_accounts():
    connection = get_connection()
    cursor = connection.cursor(dictionary=True)

    query = """
        SELECT 
            l.Login_ID,
            l.Email,
            l.Rol_ID,
            r.Rol,
            a.Voornaam,
            a.Tussenvoegsel,
            a.Achternaam,
            a.Laatste_inlogd AS Laatste_inlog,
            'Admin' AS Account_type
        FROM login l
        INNER JOIN rollen r ON l.Rol_ID = r.Rol_ID
        INNER JOIN admins a ON l.Login_ID = a.Login_ID

        UNION ALL

        SELECT 
            l.Login_ID,
            l.Email,
            l.Rol_ID,
            r.Rol,
            g.Voornaam,
            g.Tussenvoegsel,
            g.Achternaam,
            g.Laatste_inlog,
            'Gebruiker' AS Account_type
        FROM login l
        INNER JOIN rollen r ON l.Rol_ID = r.Rol_ID
        INNER JOIN gebruikers g ON l.Login_ID = g.Login_ID

        ORDER BY Login_ID ASC
    """

    cursor.execute(query)
    accounts = cursor.fetchall()

    cursor.close()
    connection.close()

    return accounts

def get_account_by_login_id(login_id):
    connection = get_connection()
    cursor = connection.cursor(dictionary=True)

    query = """
        SELECT 
            l.Login_ID,
            l.Email,
            l.Rol_ID,
            r.Rol
        FROM login l
        INNER JOIN rollen r ON l.Rol_ID = r.Rol_ID
        WHERE l.Login_ID = %s
        LIMIT 1
    """

    cursor.execute(query, (login_id,))
    account = cursor.fetchone()

    cursor.close()
    connection.close()

    return account


def update_account_email(login_id, new_email):
    connection = get_connection()
    cursor = connection.cursor()

    query = """
        UPDATE login
        SET Email = %s
        WHERE Login_ID = %s
    """

    cursor.execute(query, (new_email, login_id))
    connection.commit()

    cursor.close()
    connection.close()

def generate_temporary_password(length=12):
        characters = string.ascii_letters + string.digits + "!@#$%&*"
        return "".join(secrets.choice(characters) for _ in range(length))

def update_account_password(login_id, new_password):
        hashed_password = password_hasher.hash(new_password)

        connection = get_connection()
        cursor = connection.cursor()

        query = """
                UPDATE login
                SET Vorig_wachtwoord  = Huidig_wachtwoord,
                    Huidig_wachtwoord = %s
                WHERE Login_ID = %s \
                """

        cursor.execute(query, (hashed_password, login_id))
        connection.commit()

        cursor.close()
        connection.close()


def create_account(email, voornaam, tussenvoegsel, achternaam, rol_id, temporary_password):
    hashed_password = password_hasher.hash(temporary_password)

    connection = get_connection()
    cursor = connection.cursor()

    try:
        login_query = """
            INSERT INTO login (Rol_ID, Email, Huidig_wachtwoord, Vorig_wachtwoord)
            VALUES (%s, %s, %s, NULL)
        """

        cursor.execute(login_query, (rol_id, email, hashed_password))
        login_id = cursor.lastrowid

        if rol_id == 1:
            admin_query = """
                INSERT INTO admins (Login_ID, Voornaam, Tussenvoegsel, Achternaam, Laatste_inlogd)
                VALUES (%s, %s, %s, %s, NULL)
            """

            cursor.execute(admin_query, (login_id, voornaam, tussenvoegsel, achternaam))

        if rol_id == 2:
            user_query = """
                INSERT INTO gebruikers (Login_ID, Aangemaakt_op, Voornaam, Tussenvoegsel, Achternaam, Laatste_inlog)
                VALUES (%s, NOW(), %s, %s, %s, NULL)
            """

            cursor.execute(user_query, (login_id, voornaam, tussenvoegsel, achternaam))

        connection.commit()

        return login_id

    except Exception as error:
        connection.rollback()
        raise error

    finally:
        cursor.close()
        connection.close()

def change_own_password(login_id, current_password, new_password):
    connection = get_connection()
    cursor = connection.cursor(dictionary=True)

    try:
        select_query = """
            SELECT Huidig_wachtwoord
            FROM login
            WHERE Login_ID = %s
            LIMIT 1
        """

        cursor.execute(select_query, (login_id,))
        account = cursor.fetchone()

        if account is None:
            return False, "Account niet gevonden."

        try:
            password_is_correct = password_hasher.verify(
                account["Huidig_wachtwoord"],
                current_password
            )
        except VerifyMismatchError:
            return False, "Huidig wachtwoord is onjuist."
        except InvalidHashError:
            return False, "Wachtwoord hash is ongeldig."

        if not password_is_correct:
            return False, "Huidig wachtwoord is onjuist."

        new_hashed_password = password_hasher.hash(new_password)

        update_query = """
            UPDATE login
            SET Vorig_wachtwoord = Huidig_wachtwoord,
                Huidig_wachtwoord = %s
            WHERE Login_ID = %s
        """

        cursor.execute(update_query, (new_hashed_password, login_id))
        connection.commit()

        return True, "Wachtwoord succesvol gewijzigd."

    except Exception as error:
        connection.rollback()
        print("CHANGE PASSWORD ERROR:")
        print(type(error))
        print(error)

        return False, "Wachtwoord wijzigen is mislukt."

    finally:
        cursor.close()
        connection.close()