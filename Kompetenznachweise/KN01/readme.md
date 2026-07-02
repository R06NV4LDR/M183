# 183 - KN01 - XSS, CSRF, Client-State Manipulation

- [A Gruyere starten und Accounts erstellen](#a-gruyere-starten-und-accounts-erstellen)
- [B Stored XSS in Gruyere](#b-stored-xss-in-gruyere)
	- [B1 - DOM-Manipulation als Proof of Concept](#b1---dom-manipulation-als-proof-of-concept)
	- [B2 – Cookies: Was sie sind und warum sie gefährlich sind](#b2--cookies-was-sie-sind-und-warum-sie-gefährlich-sind)
	- [B3 – Session-Hijacking: Cookie-Exfiltration zum Angreifer-Server](#b3--session-hijacking-cookie-exfiltration-zum-angreifer-server)
- [C Reflected XSS in Gruyere](#c-reflected-xss-in-gruyere)

---
---

M183_KN01_3
Serveo: https://a42379c259cb7d7e-54-165-155-69.serveousercontent.com

Gruyere: https://google-gruyere.appspot.com/530823917152430333453354905065921721830/

---
---

## A Gruyere starten und Accounts erstellen

![Gruyere Accounts](../../img/M183_KN01_1.png)

---
---

## B Stored XSS in Gruyere

### B1 - DOM-Manipulation als Proof of Concept

1. Warum konnte dieser Payload die Sicherheitsprüfung des Browsers umgehen, obwohl `<script>` blockiert wird?

    _Da der Payload ein reguläres HTML-Bild-Tag verwendet und `src="x"` auf ein nicht existierendes Bild verweist, wirft der Browser einen Fehler und führt automatisch den im Event-Handler `onerror` definierten JavaScript-Code aus._

2. Was bedeutet es für die Sicherheit, dass der Payload auch im Browser des Verteidigers ausgeführt wird?

    _Da der Payload auf dem Server gespeichert und an alle Benutzer ausgeliefert wird, führt dies dazu, dass der schädliche Code auch im Browser des Verteidigers ausgeführt wird._

3. Welche OWASP Top 10 Kategorie (2025) beschreibt Stored XSS? Nennen Sie Nummer und Bezeichnung.

    _Stored XSS fällt unter die **[OWASP Top 10 Kategorie A5: Injection](https://owasp.org/Top10/2025/A05_2025-Injection/)**._

4. Was hätte die Applikation tun müssen, damit dieser Payload harmlos bleibt? (Stichwort: Output Encoding)

    _Die Applikation hätte den Payload vor der Ausgabe an die Benutzer ordnungsgemäß encodieren müssen, um sicherzustellen, dass er als reiner Text und nicht als ausführbarer Code interpretiert wird. Zum Beispiel könnte sie HTML-Entities verwenden, um `<` in `&lt;` und `>` in `&gt;` umzuwandeln._

    ![Gruyere Stored XSS](../../img/M183_KN01_2.png)

---

### B2 – Cookies: Was sie sind und warum sie gefährlich sind

- Screenshot der DevTools mit sichtbarem Cookie-Wert.
![Gruyere Stored XSS](../../img/M183_KN01_3.png)
- Screenshot des roten Kastens im Fenster des Angreifers (eigenes Cookie).
![Gruyere Stored XSS](../../img/M183_KN01_4.png)
- Screenshot des roten Kastens im Fenster des Verteidigers (Cookie des Verteidigers).
![Gruyere Stored XSS](../../img/M183_KN01_2.png)
- Schriftliche Antworten auf die drei Fragen.

1. Was kann ein Angreifer tun, wenn er den Session-Cookie eines anderen Benutzers kennt?

    _Da das Cookie die Identität des Nutzers gegenüber dem Server legitimiert, ist der Angreifer sofort als Opfer eingeloggt - ohne dass er das Passwort des Opfers kennen muss._

2. Was bewirkt das `HttpOnly`-Flag bei einem Cookie und wie schützt es vor diesem Angriff?

    _Das `HttpOnly`-Flag verhindert, dass der Cookie-Wert über clientseitiges JavaScript ausgelesen werden kann. Dadurch wird es für Angreifer schwieriger, den Session-Cookie zu stehlen, da er nicht über XSS-Angriffe oder andere JavaScript-Exploits zugänglich ist._

3. Warum ist es gefährlich, Session-Cookies im `localStorage` statt in einem `HttpOnly`-Cookie zu speichern?

    _Auf den `localStorage` kann immer per JavaScript zugegriffen werden (`localStorage.getItem(...)`) auch wenn das `HttpOnly`-Flag gesetzt ist. Es gibt dort kein Äquivalent zum `HttpOnly`-Flag, weshalb Angreifer, die eine XSS-Schwachstelle ausnutzen, problemlos auf die Session-Cookies zugreifen und sie stehlen können._

---

### B3 – Session-Hijacking: Cookie-Exfiltration zum Angreifer-Server

Abgabe B3:

- Screenshot von SSH-Terminal 1 (Python-Server) mit der eingehenden GET-Anfrage (Cookie sichtbar).
    
    ![](../../img/M183_KN01_5.png)

- Screenshot von SSH-Terminal 2 (Serveo) mit der zugewiesenen HTTPS-URL.
    
    ![](../../img/M183_KN01_6.png)

- Screenshot des Gruyere-Fensters des Angreifers nach der Cookie-Übernahme, mit dem Benutzernamen des Verteidigers sichtbar.

    ![](../../img/M183_KN01_7.png)

- Schriftliche Antworten auf die fünf Fragen:

    1. Warum konnte der Angreifer den Cookie des Verteidigers erhalten, ohne je dessen Passwort zu kennen oder Zugriff auf dessen Browser zu haben?

    _Der Payload wurde als Stored XSS auf dem Gruyere-Server gespeichert und bei jedem Seitenaufruf an alle Besucher ausgeliefert. Als der Verteidiger die Seite lud, wurde der Code in **seinem eigenen Browser** ausgeführt und sendete `document.cookie` an den Angreifer-Server. Der Angreifer musste also weder das Passwort kennen noch physischen Zugriff auf den fremden Browser haben – der Browser des Opfers hat die Exfiltration selbst durchgeführt._

    2. Welche Rolle spielt der new Image().src-Trick – warum funktioniert diese Technik trotz Same-Origin-Policy?

    _`new Image().src='...'` erzeugt im Hintergrund ein Bild-Objekt und löst dadurch eine GET-Anfrage an die Angreifer-URL aus – mit dem Cookie als Query-Parameter. Die Same-Origin-Policy verbietet nur das **Auslesen** von Antworten fremder Origins, nicht aber das **Absenden** einfacher Anfragen (Bilder, Skripte) an fremde Origins. Da der Angreifer die Antwort gar nicht lesen muss (der Cookie steckt bereits in der abgesendeten URL), umgeht der Trick die SOP vollständig._

    3. Warum war der Serveo-Tunnel notwendig – was wäre passiert, wenn der Payload direkt http://<EC2-IP>:9000 verwendet hätte?    

    _Gruyere läuft über **HTTPS**. Browser blockieren *Mixed Content*: Eine HTTPS-Seite darf keine unverschlüsselten HTTP-Anfragen ins Internet senden. Ein direkter Aufruf von `http://<EC2-IP>:9000` wäre daher vom Browser als Mixed Content blockiert worden – der Cookie hätte den Angreifer-Server nie erreicht. Serveo stellt einen **HTTPS**-Endpunkt bereit, wodurch die Anfrage vom Browser erlaubt wird._

    4. Nennen Sie mindestens zwei technische Massnahmen, mit denen die Webapplikation diesen Angriff verhindert hätte.

    - _**Output Encoding / Escaping:** Würde die Applikation die gespeicherte Eingabe HTML-encodiert ausgeben (`<` → `&lt;`), würde der Payload als Text angezeigt statt ausgeführt – Stored XSS wäre unmöglich._
    - _**`HttpOnly`-Cookie-Flag:** Damit kann JavaScript den Session-Cookie nicht mehr über `document.cookie` auslesen; der Exfiltrations-Payload liefe ins Leere._

    _Ergänzend erhöhen eine **Content-Security-Policy** (verbietet Inline-Skripte und fremde Ziele) sowie das **`Secure`-Flag** den Schutz zusätzlich._

    5. Was bewirkt das Secure-Flag bei einem Cookie, und in welcher Situation schützt es?

    _Das `Secure`-Flag weist den Browser an, den Cookie **ausschliesslich über verschlüsselte HTTPS-Verbindungen** zu senden. Es schützt in Situationen, in denen ein Angreifer den Netzwerkverkehr mitliest (z. B. offenes WLAN, Man-in-the-Middle): Der Cookie wird niemals im Klartext über eine unverschlüsselte HTTP-Verbindung übertragen und kann so nicht durch Sniffing abgegriffen werden._

---
---

## C Reflected XSS in Gruyere

https://google-gruyere.appspot.com/530823917152430333453354905065921721830/login?uid=defender_ronny&pw=%2BA7S22O%27s0%7Ex

- Screenshot von DevTools → Network → Response mit dem Payload im HTML-Quelltext.

    ![](../../img/M183_KN01_8.png)

- Screenshot des ausgeführten Alerts mit dem Payload sichtbar in der URL.
    
    ![](../../img/M183_KN01_9.png)

- Schriftliche Antworten auf die drei Fragen:
    1. Was ist der Hauptunterschied zwischen Stored XSS und Reflected XSS hinsichtlich Persistenz und Reichweite? (Antwort aus Schritt 3 ableiten)

        _**Persistenz:** Stored XSS ist **dauerhaft** – der Payload liegt in der Datenbank des Servers und wird bei jedem Seitenaufruf erneut ausgeliefert. Reflected XSS ist **flüchtig** – der Payload existiert nur in der manipulierten URL/Anfrage und wird einmalig zurückgespiegelt._

        _**Reichweite:** Stored XSS trifft **automatisch alle Besucher** der betroffenen Seite. Reflected XSS trifft **nur die Person, die den präparierten Link öffnet**. Das bestätigt Schritt 3: Beim normalen Aufruf der Startseite (ohne Payload in der URL) wurde beim Verteidiger **kein** Alert ausgelöst – der Angriff ist nicht persistent._

    2. Wie würde ein Angreifer in der Praxis vorgehen, um das Opfer dazu zu bringen, den manipulierten Link zu öffnen? (Social Engineering)

        _Der Angreifer verpackt den manipulierten Link in eine glaubwürdige, dringlich wirkende Nachricht (Phishing) – z. B. per E-Mail, Chat oder Social-Media-Post („Ihr Konto wurde gesperrt, hier bestätigen"). Um den Payload in der URL zu verschleiern, nutzt er URL-Shortener (bit.ly), Link-Text-Maskierung (`<a href="böse-url">harmloser Text</a>`) oder ähnlich aussehende Domains. Klickt das im Zieldienst eingeloggte Opfer auf den Link, wird der Payload in seinem Kontext ausgeführt._

    3. Welcher OWASP Proactive Control schützt am direktesten gegen XSS? Nennen Sie ihn mit Nummer und Titel. (Referenz: owasp.org/www-project-proactive-controls)

        _Am direktesten schützt **C4 – Encode and Escape Data** (Output Encoding). Durch das Encodieren der Ausgabe werden Steuerzeichen wie `<` und `>` in harmlose HTML-Entities umgewandelt, sodass der Browser die Eingabe als Text und nicht als ausführbaren Code interpretiert._

        _Hinweis: In der **aktuellen** Fassung der OWASP Proactive Controls wurde Output Encoding in **C3 – Validate all Input & Handle Exceptions** integriert; ergänzend wirkt **C8 – Leverage Browser Security Features** über eine Content-Security-Policy. Die klassische, in den meisten Kursen erwartete Bezeichnung ist „C4 – Encode and Escape Data"._

---
---

## D Client-State Manipulation in Gruyere

- Screenshot der DevTools mit sichtbarem Cookie-Inhalt (vor der Manipulation).

    ![Cookie-Inhalt vor der Manipulation](../../img/M183_KN01_10.png)

- Screenshot der Applikation nach erfolgreicher Manipulation (erhöhte Rechte sichtbar).
    
    ![Applikation mit erhöhten Rechten nach Manipulation](../../img/M183_KN01_11.png)

- Schriftliche Antworten auf die drei Fragen:
    1. Warum ist es gefährlich, sicherheitsrelevante Daten (wie Rollen oder Berechtigungen) im Client (Cookie/LocalStorage) zu speichern?

        _Client-seitige Speicher (Cookies, LocalStorage) liegen vollständig im Einflussbereich des Benutzers und können mit den DevTools oder JavaScript beliebig ausgelesen und **verändert** werden. Speichert man dort sicherheitsrelevante Daten wie Rollen oder Berechtigungen, kann ein Angreifer sich durch simples Editieren des Werts selbst höhere Rechte (z. B. `admin`) zuweisen, sofern der Server diese Angaben ungeprüft übernimmt. Der Client ist grundsätzlich nicht vertrauenswürdig._

    2. Wo sollten Berechtigungsprüfungen stattfinden – im Client oder auf dem Server? Begründen Sie.

        _Berechtigungsprüfungen müssen **immer auf dem Server** stattfinden. Nur der Server liegt ausserhalb der Kontrolle des Angreifers und kann die Identität (aus der Session bzw. dem Token) verlässlich mit den in der Datenbank hinterlegten Rechten abgleichen. Client-seitige Prüfungen dienen höchstens der Benutzerfreundlichkeit (z. B. Ausblenden von Buttons), bieten aber keinerlei Sicherheit, da sie umgangen werden können._

    3. Welche OWASP Top 10 Kategorie (2025) beschreibt dieses Problem?

        _**[A01:2025 – Broken Access Control](https://owasp.org/Top10/2025/A01_2025-Broken_Access_Control/)** – die serverseitige Zugriffs- bzw. Berechtigungsprüfung fehlt, sodass ein Benutzer durch Manipulation clientseitiger Daten unautorisiert höhere Rechte erlangt._

---
---