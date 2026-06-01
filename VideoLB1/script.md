# Video Script for LB1: Broken Access Control

## 🎬 Komplettes Videoskript: StreamCast – Broken Access Control
_⏱️ 0:00 – 1:15 | Intro & Der "Hook"_
(Regie: Du startest mit der OWASP-Webseite im Hintergrund oder sprichst direkt in die Kamera.)

**Sprechtext:**

„Hallo und herzlich willkommen zu diesem Video! Heute widmen wir uns der unangefochtenen Nummer 1 der OWASP Top 10 Web-Sicherheitsrisiken: Broken Access Control – zu Deutsch: Fehlerhafte Zugriffskontrolle.

Warum steht diese Schwachstelle ganz oben auf der Liste der gefährlichsten Sicherheitslücken?

Ganz einfach: Weil sie extrem häufig vorkommt, oft erschreckend leicht auszunutzen ist und verheerende Schäden anrichten kann. In den nächsten knapp 10 Minuten werden wir uns anschauen, was Broken Access Control genau bedeutet, wie Angreifer dabei vorgehen und wie wir als Entwickler unsere Anwendungen effektiv davor schützen können.“

## ⏱️ 1:15 – 2:45 | Theorie: Was ist Broken Access Control?
(Regie: Blende eine einfache Folie oder Grafik ein, die den Unterschied zwischen Authentifizierung und Autorisierung zeigt.)
  
**Sprechtext:**

„Um Broken Access Control zu verstehen, müssen wir daran denken, dass es zwei grundlegende Sicherheitsprozesse gibt: Authentifizierung und Autorisierung.

Authentifizierung bedeutet: Wer bist du? Das ist der Login-Prozess mit Benutzername, Passwort oder sogar Multi-Faktor-Authentisierung (MFA).

Autorisierung bedeutet: Was darfst du? Also welche Rechte hast du, nachdem du eingeloggt bist?

Broken Access Control ist ein reines Autorisierungsproblem. Das bedeutet: Ein Angreifer ist oft völlig legitim angemeldet – die Anwendung vergisst danach aber schlichtweg zu prüfen, ob dieser Benutzer die Datei, den API-Endpunkt oder die Admin-Funktion überhaupt aufrufen darf.

Diese Schwachstelle äußert sich in verschiedenen Arten:

URL-Manipulation: Das direkte Aufrufen von versteckten Adressen.

Privilege Escalation (Rechteausweitung): Entweder vertikal (vom normalen User zum Admin) oder horizontal (ein User greift auf die Daten eines gleichgestellten Users zu).

Und das bringt uns zum heutigen Fokus: IDOR – Insecure Direct Object References.“

 oder anders gesagt: Ein Nutzer greift auf fremde Daten zu, weil die Anwendung seine Rechte nicht prüft.

## ⏱️ 2:45 – 5:30 | Live-Demo: IDOR am konkreten Code-Beispiel
(Regie: Wechsel vom Slide direkt in deine Entwicklungsumgebung (z. B. VS Code) und zeige den Python-Code. Danach wechselst du in den Browser auf localhost:5000.)

**Sprechtext:**

„Genug der   Theorie, schauen wir uns das Ganze direkt in der Praxis an. Ich habe hier eine kleine Python-Flask-Anwendung vorbereitet, darin gibt es ein Rechnungsportal für Kunden. Wir simulieren folgendes Szenario: Ich bin als regulärer Benutzer eingeloggt und meine eigene Rechnungs-ID ist die 101.

Wechseln wir in den Browser zu unserem ersten, verwundbaren Endpunkt: http://localhost:5000/vulnerable/invoice/101. Wie wir sehen, wird meine Rechnung problemlos geladen.

Was passiert aber, wenn ich als Angreifer ein bisschen kreativ werde und die ID in der URL einfach auf 201 abändere? Ich drücke Enter für http://localhost:5000/vulnerable/invoice/201... und siehe da: Ich sehe die vertrauliche Rechnung eines komplett anderen Benutzers!

Schauen wir uns im Code an, warum das passiert ist:“

(Regie: Zeige im Code den @app.route('/vulnerable/invoice/<int:invoice_id>') Ausschnitt)

Python

### 1. DER VERWUNDBARE ENDPUNKT (IDOR)
@app.route('/vulnerable/invoice/<int:invoice_id>')
def vulnerable_invoice(invoice_id):
    invoice = INVOICES_DB.get(invoice_id)
    if not invoice:
        return jsonify({"error": "Rechnung nicht gefunden"}), 404
    
    # FATALER FEHLER: Keine Prüfung, ob die Rechnung dem eingeloggten User gehört!
    return jsonify(invoice)
**Sprechtext:** 

„Der Server nimmt die invoice_id aus der URL entgegen, sucht sie in der Datenbank und liefert sie direkt an den Browser zurück. Er prüft nicht, wer ich bin. Ein Angreifer kann diesen Prozess automatisiert hochzählen – man nennt das ID-Enumeration – und so in Minuten alle Rechnungen des Systems stehlen.

Wie lösen wir das? Schauen wir uns den sicheren Endpunkt an. Wenn ich jetzt versuche, die fremde Rechnung unter http://localhost:5000/secure/invoice/201 aufzurufen, blockt das System ab: 403 - Zugriff verweigert.

Der Blick in den sicheren Code zeigt uns die Lösung :“

(Regie: Zeige im Code den @app.route('/secure/invoice/<int:invoice_id>') Ausschnitt)

Python
### 2. DER SICHERE ENDPUNKT
@app.route('/secure/invoice/<int:invoice_id>')
def secure_invoice(invoice_id):
    current_user = get_current_logged_in_user() # Gibt "user_A" zurück
    invoice = INVOICES_DB.get(invoice_id)
    
    if not invoice:
        return jsonify({"error": "Rechnung nicht gefunden"}), 404
        
    # SCHUTZMASSNAHME: Der Server prüft die Besitzrechte!
    if invoice["user_id"] != current_user:
        return "Zugriff verweigert! Diese Rechnung gehört Ihnen nicht.", 403
        
    return jsonify(invoice)
**Sprechtext:**

„Hier wird die Identität des aktuell angemeldeten Nutzers serverseitig ermittelt und in einer if-Bedingung strikt mit dem Besitzer der Rechnung abgeglichen. 

Stimmen sie nicht überein, wirft der Server den Angreifer raus. Genau so muss eine saubere Autorisierung aussehen!“

## ⏱️ 5:30 – 7:30 | Real-World Examples (Beispiele aus der Praxis)
(Regie: Zeige Nachrichtenseiten oder Logos/Berichte der genannten Vorfälle.)

**Sprechtext:**

„Dass dieses IDOR-Problem keine reine Entwickler-Spielerei ist, zeigen reale Vorfälle mit enormer Reichweite:

Beispiel 1: Das Passar-Warenverkehrssystem (BAZG). Hier war es vor kurzem möglich, Ausfuhrbescheinigungen fremder Unternehmen einzusehen. Man benötigte lediglich einen eigenen Account und manipulierte anschließend die URL. Da die Dokumenten-ID in einem leicht zu erratenden Datumsformat aufgebaut war, konnte man sie einfach hochzählen. Behoben wurde das Ganze, indem man die IDs auf krypto-sichere, nicht erratbare UUIDs umstellte.

Beispiel 2: First American Financial (2019). Bei diesem Riesen-Leak waren über 885 Millionen sensible Dokumente wie Steuerbelege und Bankdaten über Jahre hinweg komplett ungeschützt im Netz. Man musste nicht einmal eingeloggt sein – das bloße Ändern der ID in der Browser-Adresszeile reichte aus.

Beispiel 3: Optus Daten-Breach. Hier zeigte sich, dass ungeschützte API-Endpunkte ohne Autorisierungsprüfung im Internet ein offenes Tor für automatisierten Datendiebstahl sind.“

⏱️ 7:30 – 9:00 | Maßnahmen: Wie entwickeln wir sicher?
(Regie: Blende eine übersichtliche Bullet-Point-Liste ein.)

**Sprechtext:**

„Was müssen wir also tun, um Broken Access Control in unseren eigenen Projekten zu verhindern? Hier sind die 4 wichtigsten Best Practices:

Deny by Default (Standardmäßig verbieten): Jeder API-Endpunkt und jede Route muss standardmäßig für alle gesperrt sein. Zugriff gibt es nur durch explizite, dokumentierte Freigabe.

Prinzip der geringsten Privilegien (Least Privilege): Benutzer und Prozesse sollten nur genau die minimalen Rechte besitzen, die sie für ihre aktuelle Aufgabe zwingend benötigen.

Rollen- und Attributbasierte Zugriffskontrolle (RBAC & ABAC): Setzt auf klare Rollenkonzepte und prüft bei sensiblen Daten zusätzlich Attribute – wie den Kontext oder die Verbindung zum Objekt.

Niemals dem Client vertrauen: Zugriffskontrollen dürfen niemals im Browser (z. B. via JavaScript) oder durch ungeprüfte Cookie-Werte wie role=admin stattfinden. Die Validierung muss immer zwingend auf dem Server passieren, basierend auf einer sicheren Session oder kryptografisch signierten Tokens (wie JWTs).

Ein wichtiger Zusatz fürs Testen: Verlasst euch nicht blind auf automatisierte Schwachstellenscanner! Da ein Scanner bei einer IDOR-Lücke ein sauberes Dokument mit dem HTTP-Status 200 OK zurückbekommt, denkt er, alles sei in Ordnung. Er versteht die Business-Logik nicht. Broken Access Control findet man am effektivsten durch manuelle Penetrationstests und gezielte Code-Reviews.“

## ⏱️ 9:00 – 10:00 | Outro & Fazit
(Regie: Du bist wieder voll im Bild oder zeigst eine prägnante Zusammenfassung.)

**Sprechtext:**

„Zusammenfassend lässt sich sagen: Die Sicherheit einer Anwendung entscheidet sich nicht an der Haustür beim Login. Multi-Faktor-Authentisierung ist wichtig, nützt aber nichts, wenn die Anwendung dahinter die Rechte nicht prüft. Die Autorisierung muss bei jeder einzelnen Anfrage auf dem Server stattfinden.

Ich hoffe, dieses praktische Beispiel hat euch gezeigt, wie schnell Broken Access Control entsteht und wie leicht es sich verhindern lässt. Schreibt mir gerne in die Kommentare, ob ihr selbst schon mal über ein IDOR-Problem gestolpert seid.

Vielen Dank fürs Zuschauen, lasst ein Abo da und bis zum nächsten Mal bei StreamCast!“

www.bazg.admin.ch