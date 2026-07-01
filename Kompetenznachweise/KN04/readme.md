# 183 - KN04 -  Verschlüsselung & Kryptographie

## A Brute-Force-Angriff auf ein Web-Login

- Screenshot der vollständigen Ausgabe des Brute-Force-Scripts (Fortschritt und gefundenes Passwort sichtbar).
- Screenshot des erfolgreichen Logins im Browser mit dem gefundenen Passwort.
- Schriftliche Antworten auf die drei Fragen:
    1. Wie viele Versuche und wie viele Sekunden hat der Angriff benötigt? Was würde passieren, wenn die Passwortliste statt 20 Einträgen 1 Million hätte (z.B. die bekannte `rockyou.txt`)?
    2. Welche **zwei technischen Massnahmen** hätten diesen Angriff verhindert oder massgeblich erschwert? (Hinweis: schauen Sie sich den Kommentar im PHP-Code an)
    3. Warum ist das Passwort `sunshine` schwach – auch wenn es kein Wort wie `password` oder `123456` ist?

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