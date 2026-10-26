import mysql.connector


def get_connection():
    connection = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="geotrace",
        port=3306
    )

    return connection



def get_profile_by_login_id(login_id, rol_id):
    db = get_connection()
    cursor = db.cursor(dictionary=True)

    if rol_id == 1:
        query = """
            SELECT 
                admins.Voornaam,
                admins.Tussenvoegsel,
                admins.Achternaam,
                login.Email,
                rollen.Rol
            FROM admins
            INNER JOIN login ON admins.Login_ID = login.Login_ID
            INNER JOIN rollen ON login.Rol_ID = rollen.Rol_ID
            WHERE login.Login_ID = %s
        """
    else:
        query = """
            SELECT 
                gebruikers.Voornaam,
                gebruikers.Tussenvoegsel,
                gebruikers.Achternaam,
                login.Email,
                rollen.Rol
            FROM gebruikers
            INNER JOIN login ON gebruikers.Login_ID = login.Login_ID
            INNER JOIN rollen ON login.Rol_ID = rollen.Rol_ID
            WHERE login.Login_ID = %s
        """

    cursor.execute(query, (login_id,))
    profile = cursor.fetchone()

    cursor.close()
    db.close()

    return profile