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

    ![](../../img/M183_KN04_4.png)

- Schriftliche Antworten auf die drei Fragen:
    1. Was ist ein Nonce und warum muss er für jede Verschlüsselung neu generiert werden?

        **Bedeutung:** _Ein Nonce (Number used once) ist eine eindeutige zufällige  oder fortlaufende Zahl, die bei einem Verschlüsselungsverfahren zusammen mit em geheimen Schlüssel verwendet wird._

        **Warum neu generieren?** _Wenn derselbe Schlüssel und derselbe Nonce für zwei unterschiedliche Nachrichten verwendet werden, führt dies bei vielen Krypto-Modi (wie AES-GCM) dazu, dass identische Klartext-Muster zu identischen Ciphertext-Mustern führen. Ein Angreifer könnte dadurch Nachrichten vergleichen, Muster erkennen oder sogar Teile des Klartexts mathematisch rekonstruieren (Replay-Angriffe und Key-Stream-Reconstruction). Der Nonce sorgt dafür, dass derselbe Klartext jedes Mal völlig anders verschlüsselt aussieht._


    2. Was ist der Unterschied zwischen DES (56-Bit-Schlüssel) und AES-256 (256-Bit-Schlüssel) in Bezug auf Brute-Force-Resistenz?

        **DES (56-Bit-Schlüssel):** _ Besitzt einen winzigen Schlüsselraum von nur $2^{56}$ (ca. $7.2 \times 10^{16}$) Möglichkeiten. Moderne Computer oder spezialisierte Hardware (ASICs) können diesen gesamten Schlüsselraum in wenigen Stunden vollständig durchsuchen. DES gilt daher seit Jahren als absolut unsicher._

        **AES-256 (256-Bit-Schlüssel):** _ Besitzt einen gigantischen Schlüsselraum von $2^{256}$ (ca. $1.1 \times 10^{77}$) Möglichkeiten. Um diese Zahl zu veranschaulichen: Selbst wenn man alle Supercomputer der Erde zusammenschalten würde, bräuchten sie astronomisch viel länger als das Alter des Universums, um den Schlüssel per Brute-Force zu erraten. AES-256 ist nach heutigem Stand der Wissenschaft absolut resistent gegen Brute-Force-Angriffe._

    3. Was demonstriert der Manipulations-Test am Ende des Skripts? Welchen Vorteil bietet GCM gegenüber einfachem AES-CBC?

        ![](../../img/M183_KN04_5.png)

        **Demonstration des Tests:** _Der Test zeigt, dass der **GCM-Modus** (Galois/Counter Mode) eine Veränderung von auch nur einem einzigen Bit im Ciphertext sofort bemerkt. Er bricht die Entschlüsselung mit einer Fehlermeldung ab, anstatt korrupte Daten auszugeben._

        **Vorteil gegenüber AES-CBC:** _**AES-CBC** bietet nur Vertraulichkeit. Ein Angreifer kann den Ciphertext manipulieren (Bit-Flipping-Angriff). Der Empfänger entschlüsselt die manipulierten Daten zwar zu Fehlern oder veränderten Werten, merkt aber nicht zwingend, dass die Nachricht von einem Angreifer manipuliert wurde._

        - AES-GCM bietet Vertraulichkeit und Integrität (Authenticated Encryption). Es berechnet während der Verschlüsselung ein kryptographisches Siegel (Authentication Tag). Wird der Ciphertext manipuliert, passt das Siegel nicht mehr und die Manipulation fliegt sofort auf.

---

## C PKI-Zertifikatskette mit OpenSSL

- Screenshot der Ausgabe von `openssl x509 -text -noout` (Subject, Issuer und Validity sichtbar).
- Screenshot von `openssl verify` mit dem Ergebnis `OK`.

    ![](../../img/M183_KN04_6.png)
- Schriftliche Antworten auf die drei Fragen:

    1. Was ist der Unterschied zwischen einem selbstsignierten Zertifikat und einem CA-signierten Zertifikat?

        __

    2. Was enthält ein CSR (Certificate Signing Request) und wozu dient er?

        __

    3. Warum vertraut ein normaler Browser Ihrem selbst erstellten Zertifikat nicht, obwohl es technisch korrekt erstellt wurde?

---
## D Nginx mit TLS konfigurieren

![](../../img/M183_KN04_9.png)

- Screenshot von `https://<IHRE-EC2-IP>` im Browser mit sichtbarer Sicherheitswarnung (oder der geöffneten Seite nach «Weiter»).

    ![](../../img/M183_KN04_7.png)
- Screenshot des Zertifikat-Dialogs im Browser (CN, Aussteller und Gültigkeit sichtbar).

    ![](../../img/M183_KN04_8.png)

- Schriftliche Antworten auf die drei Fragen:
    1. Welche Informationen zeigt der Browser im Zertifikat-Dialog? Was davon haben Sie selbst in Aufgabe C definiert?

        __

    2. Warum erscheint trotz technisch korrektem Zertifikat eine Sicherheitswarnung?

        __

    3. Erklären Sie anhand dieses Setups, wie hybride Verschlüsselung bei HTTPS funktioniert (Schlüsselaustausch vs. Datenverschlüsselung).

        __

---

## E HTTP vs HTTPS - Traffic live mitlesen

- Screenshot der vollständigen nmap-Ausgabe.
    ![](../../img/M183_KN04_10.png)
- Screenshot von Terminal 1 mit dem tcpdump-Output (Benutzername und Passwort müssen im Klartext sichtbar sein).

    ![](../../img/M183_KN04_11.png)
- Screenshot von Terminal 2 mit dem curl-Befehl.
    ![](../../img/M183_KN04_10.gif)


- Screenshot von Terminal 1 mit dem tcpdump-Output auf Port 443 (verschlüsselte Bytes sichtbar, kein Klartext).



    1. Was zeigt nmap über Port 80 und Port 443? Welche Information erhält ein Angreifer bereits durch einen Port-Scan, bevor er auch nur eine einzige Anfrage an die App gestellt hat?

        __

    2. Was genau ist im tcpdump-Output sichtbar? Markieren Sie die Zeile, die das Passwort im Klartext enthält.
    3. Was müsste ein Angreifer in einem realen Netzwerk tun, um diesen Traffic mitzulesen? (Stichwort: ARP-Spoofing / Man-in-the-Middle)
    4. Was ist der Unterschied zwischen dem tcpdump-Output auf Port 80 und Port 443? Was sieht ein Angreifer beim HTTPS-Traffic?
    5. Was passiert beim TLS-Handshake, bevor die eigentlichen Daten (Benutzername/Passwort) übertragen werden? (Stichwort: Hybride Verschlüsselung aus Aufgabe D)
    6. Sie sehen bei Port 443 noch immer die IP-Adressen von Client und Server im tcpdump-Output. Warum ist das so, obwohl die Verbindung verschlüsselt ist?

## F Hash-Funktionen: MD5 cracken mit Python