# 183 - KN04 -  Verschlüsselung & Kryptographie

## A Brute-Force-Angriff auf ein Web-Login

![](../../img/M183_KN04_1.png)


- Screenshot der vollständigen Ausgabe des Brute-Force-Scripts (Fortschritt und gefundenes Passwort sichtbar).

    ![](../../img/M183_KN04_3.png)
- Screenshot des erfolgreichen Logins im Browser mit dem gefundenen Passwort.
    ![](../../img/M183_KN04_2.gif)
- Schriftliche Antworten auf die drei Fragen:
    1. Wie viele Versuche und wie viele Sekunden hat der Angriff benötigt? Was würde passieren, wenn die Passwortliste statt 20 Einträgen 1 Million hätte (z.B. die bekannte `rockyou.txt`)?

        **Beobachtung:** _Der Angriff benötigte 13 Versuche und 0.02 Sekunden_

        **Skalierung auf 1 Million Einträge:** _Wenn das Skript für 13 Versuche 0.02 Sekunden benötigt, schafft es 650 Anfragen pro Sekunde. Für 1 Million Einträge würde es also ca. 1538 Sekunden oder 25.6 Minuten dauern._
        
        **Anfragen pro Sekunde**
        $$R_s = \frac{\text{Versuche}}{\text{Zeit}} = \frac{13}{0.02\,\text{s}} = 650\,\text{Anfragen/s}$$
        
    
        **Hochrechnung für 1 Million Einträge**
        $$T_{\text{Gesamt}} = \frac{\text{Gesamte Einträge}}{R_s} = \frac{1'000'000}{650\,\text{Anfragen/s}} \approx 1538.46\,\text{Sekunden} \approx 25.6\,\text{Minuten}$$

    2. Welche **zwei technischen Massnahmen** hätten diesen Angriff verhindert oder massgeblich erschwert? (Hinweis: schauen Sie sich den Kommentar im PHP-Code an)

        **Rate-Limiting (Anzahl Anfragen drosseln):** _Der Server erlaubt pro IP-Adresse oder Benutzerkonto nur eine bestimmte Anzahl von Login-Versuchen innerhalb eines Zeitfensters (z. B. maximal 5 Versuche pro Minute). Jede weitere Anfrage wird mit dem HTTP-Status `429 Too Many Requests` blockiert. Dies macht automatisierte Brute-Force-Angriffe zeitlich unmöglich._

        **Account-Lockout (Kontosperrung) / CAPTCHA:** _Nach einer vordefinierten Anzahl von Fehllogins (z. B. 3 oder 5 Fehlversuche) wird das betroffene Benutzerkonto für eine gewisse Zeit (z. B. 15 Minuten) komplett gesperrt. Alternativ kann ein CAPTCHA vorgeschaltet werden, das von automatisierten Skripten nicht gelöst werden kann._

    3. Warum ist das Passwort `sunshine` schwach – auch wenn es kein Wort wie `password` oder `123456` ist?

        - **Wörterbuch-Wort:** _`sunshine` ist ein regulärer Begriff aus der englischen Sprache. Automatisierte Angreifer nutzen für Brute-Force- und Wörterbuch-Angriffe standardisierte Wortlisten (wie z. B. `rockyou.txt`), die Millionen solcher alltäglichen Wörter, Begriffe aus der Popkultur und echten Datenlecks enthalten._

        - **Keine Komplexität:** Das Passwort besteht ausschliesslich aus Kleinbuchstaben. Es fehlen Grossbuchstaben, Zahlen und Sonderzeichen, was die Kombination für Angreifer extrem leicht vorhersehbar macht.

        - **Geringe mathematische Entropie:** Da es sich um ein logisches, existierendes Wort und keine zufällige Zeichenfolge handelt, ist der mathematische Zufallsgehalt (die Entropie) des Passworts extrem niedrig. Es gehört weltweit zu den am häufigsten verwendeten Passwörtern und wird von Cracker-Tools in Sekundenbruchteilen erraten.


---

## B AES-256 symmetrische Verschlüsselung

- Screenshot der vollständigen Ausgabe des Skripts (Klartext, Schlüssel, Ciphertext und Manipulations-Test sichtbar).
- Schriftliche Antworten auf die drei Fragen:
    1. Was ist ein Nonce und warum muss er für jede Verschlüsselung neu generiert werden?
    2. Was ist der Unterschied zwischen DES (56-Bit-Schlüssel) und AES-256 (256-Bit-Schlüssel) in Bezug auf Brute-Force-Resistenz?
    3. Was demonstriert der Manipulations-Test am Ende des Skripts? Welchen Vorteil bietet GCM gegenüber einfachem AES-CBC?

---

## C PKI-Zertifikatskette mit OpenSSL

- Screenshot der Ausgabe von `openssl x509 -text -noout` (Subject, Issuer und Validity sichtbar).
- Screenshot von `openssl verify` mit dem Ergebnis `OK`.
- Schriftliche Antworten auf die drei Fragen:
    1. Was ist der Unterschied zwischen einem selbstsignierten Zertifikat und einem CA-signierten Zertifikat?
    2. Was enthält ein CSR (Certificate Signing Request) und wozu dient er?
    3. Warum vertraut ein normaler Browser Ihrem selbst erstellten Zertifikat nicht, obwohl es technisch korrekt erstellt wurde?

---
## D Nginx mit TLS konfigurieren


Abgabe:

- Screenshot von `https://<IHRE-EC2-IP>` im Browser mit sichtbarer Sicherheitswarnung (oder der geöffneten Seite nach «Weiter»).
- Screenshot des Zertifikat-Dialogs im Browser (CN, Aussteller und Gültigkeit sichtbar).
- Schriftliche Antworten auf die drei Fragen:
    1. Welche Informationen zeigt der Browser im Zertifikat-Dialog? Was davon haben Sie selbst in Aufgabe C definiert?
    2. Warum erscheint trotz technisch korrektem Zertifikat eine Sicherheitswarnung?
    3. Erklären Sie anhand dieses Setups, wie hybride Verschlüsselung bei HTTPS funktioniert (Schlüsselaustausch vs. Datenverschlüsselung).


---

## E HTTP vs HTTPS - Traffic live mitlesen

## F Hash-Funktionen: MD5 cracken mit Python