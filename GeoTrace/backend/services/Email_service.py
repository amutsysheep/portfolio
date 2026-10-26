import os
import smtplib
from email.message import EmailMessage
from dotenv import load_dotenv

load_dotenv()


def send_2fa_code(receiver_email, code):
    mail_username = os.getenv("MAIL_USERNAME")
    mail_password = os.getenv("MAIL_PASSWORD")
    mail_server = os.getenv("MAIL_SERVER")
    mail_port = int(os.getenv("MAIL_PORT"))

    message = EmailMessage()
    message["Subject"] = "Je GeoTrace verificatiecode"
    message["From"] = f"GeoTrace Security <{mail_username}>"
    message["To"] = receiver_email

    plain_text = f"""
Hallo,

Je verificatiecode voor GeoTrace is:

{code}

Deze code is tijdelijk geldig.

Als jij dit niet was, kun je deze mail negeren.

GeoTrace Security
"""

    html_content = f"""
<!doctype html>
<html lang="nl">
<head>
  <meta charset="utf-8">
</head>
<body style="margin:0; padding:0; background-color:#050607; font-family:Arial, Helvetica, sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#050607; padding:40px 0;">
    <tr>
      <td align="center">
        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background-color:#0c0e12; border:1px solid #2a2f38; border-radius:18px; overflow:hidden;">
          <tr>
            <td style="padding:34px 34px 20px 34px;">
              

              <h1 style="color:#ffffff; font-size:26px; margin:24px 0 8px 0;">
                GeoTrace verificatie
              </h1>

              <p style="color:#8f9aaa; font-size:15px; line-height:1.6; margin:0;">
                Gebruik de onderstaande code om je login te bevestigen.
              </p>
            </td>
          </tr>

          <tr>
            <td style="padding:10px 34px 24px 34px;">
              <div style="background-color:#080a0d; border:1px solid #28303a; border-radius:14px; padding:24px; text-align:center;">
                <p style="color:#8f9aaa; font-size:13px; margin:0 0 12px 0; text-transform:uppercase; letter-spacing:1px;">
                  Verificatiecode
                </p>

                <div style="color:#ffffff; font-size:38px; font-weight:700; letter-spacing:8px;">
                  {code}
                </div>
              </div>
            </td>
          </tr>

          <tr>
            <td style="padding:0 34px 34px 34px;">
              <p style="color:#c9d1dc; font-size:14px; line-height:1.6; margin:0 0 18px 0;">
                Deze code is tijdelijk geldig. Deel deze code met niemand.
              </p>

              <p style="color:#7d8794; font-size:13px; line-height:1.6; margin:0;">
                Was jij dit niet? Dan kun je deze mail veilig negeren.
              </p>
            </td>
          </tr>

          <tr>
            <td style="background-color:#080a0d; border-top:1px solid #252b34; padding:18px 34px;">
              <p style="color:#5d6673; font-size:12px; margin:0;">
                GeoTrace Security • Automatisch verzonden bericht
              </p>
            </td>
          </tr>
        </table>

        <p style="color:#4f5865; font-size:12px; margin-top:18px;">
          © GeoTrace
        </p>
      </td>
    </tr>
  </table>
</body>
</html>
"""

    message.set_content(plain_text)
    message.add_alternative(html_content, subtype="html")

    with smtplib.SMTP(mail_server, mail_port) as smtp:
        smtp.starttls()
        smtp.login(mail_username, mail_password)
        smtp.send_message(message)

def send_temporary_password(receiver_email, temporary_password):
            mail_username = os.getenv("MAIL_USERNAME")
            mail_password = os.getenv("MAIL_PASSWORD")
            mail_server = os.getenv("MAIL_SERVER")
            mail_port = int(os.getenv("MAIL_PORT"))

            message = EmailMessage()
            message["Subject"] = "Je GeoTrace tijdelijke wachtwoord"
            message["From"] = f"GeoTrace Security <{mail_username}>"
            message["To"] = receiver_email

            plain_text = f"""
        Hallo,

        Er is een nieuw tijdelijk wachtwoord aangemaakt voor je GeoTrace-account.

        Je tijdelijke wachtwoord is:

        {temporary_password}

        Log hiermee in en wijzig daarna je wachtwoord zodra die functie beschikbaar is.

        Als jij dit niet hebt aangevraagd, neem dan contact op met de beheerder.

        GeoTrace Security
        """

            html_content = f"""
        <!doctype html>
        <html lang="nl">
        <head>
          <meta charset="utf-8">
        </head>
        <body style="margin:0; padding:0; background-color:#050607; font-family:Arial, Helvetica, sans-serif;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#050607; padding:40px 0;">
            <tr>
              <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background-color:#0c0e12; border:1px solid #2a2f38; border-radius:18px; overflow:hidden;">
                  <tr>
                    <td style="padding:34px 34px 20px 34px;">
                      <div style="width:54px; height:54px; border-radius:16px; background:linear-gradient(135deg,#0077ff,#8b4dff); color:#ffffff; font-size:24px; font-weight:700; display:flex; align-items:center; justify-content:center;">
                        G
                      </div>

                      <h1 style="color:#ffffff; font-size:26px; margin:24px 0 8px 0;">
                        Tijdelijk wachtwoord
                      </h1>

                      <p style="color:#8f9aaa; font-size:15px; line-height:1.6; margin:0;">
                        Er is een nieuw tijdelijk wachtwoord aangemaakt voor je GeoTrace-account.
                      </p>
                    </td>
                  </tr>

                  <tr>
                    <td style="padding:10px 34px 24px 34px;">
                      <div style="background-color:#080a0d; border:1px solid #28303a; border-radius:14px; padding:24px; text-align:center;">
                        <p style="color:#8f9aaa; font-size:13px; margin:0 0 12px 0; text-transform:uppercase; letter-spacing:1px;">
                          Tijdelijk wachtwoord
                        </p>

                        <div style="color:#ffffff; font-size:26px; font-weight:700; letter-spacing:2px;">
                          {temporary_password}
                        </div>
                      </div>
                    </td>
                  </tr>

                  <tr>
                    <td style="padding:0 34px 34px 34px;">
                      <p style="color:#c9d1dc; font-size:14px; line-height:1.6; margin:0 0 18px 0;">
                        Log in met dit tijdelijke wachtwoord. Deel dit wachtwoord met niemand.
                      </p>

                      <p style="color:#7d8794; font-size:13px; line-height:1.6; margin:0;">
                        Was jij dit niet? Neem dan contact op met de beheerder.
                      </p>
                    </td>
                  </tr>

                  <tr>
                    <td style="background-color:#080a0d; border-top:1px solid #252b34; padding:18px 34px;">
                      <p style="color:#5d6673; font-size:12px; margin:0;">
                        GeoTrace Security • Automatisch verzonden bericht
                      </p>
                    </td>
                  </tr>
                </table>

                <p style="color:#4f5865; font-size:12px; margin-top:18px;">
                  © GeoTrace
                </p>
              </td>
            </tr>
          </table>
        </body>
        </html>
        """

            message.set_content(plain_text)
            message.add_alternative(html_content, subtype="html")

            with smtplib.SMTP(mail_server, mail_port) as smtp:
                smtp.starttls()
                smtp.login(mail_username, mail_password)
                smtp.send_message(message)