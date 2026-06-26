# 183 - KN03 - Session Handling

## A Sicherheitsgruppe erweitern und App deployen

![AWS Security Group](../../img/M183_KN03_1.png)
_Angepasste Security Group für KN03_
![Meine Webseite](../../img/M183_KN03_2.png)
_Meine Webseite mit angezeigten Session-Daten_

## B Sicherheitslücken in der App analysieren

Aufgabe B: Gehen Sie den Code durch und benennen Sie die fünf Sicherheitslücken mündlich.

1. **Fehlende Passwortüberprüfung (Broken Authentication)**
    - **Das Problem:** Der Code prüft lediglich `if(isset($_POST['login]))` und übernimmt den Benutzernamen unvalididert in die Session. Das übermittelte Passwort wird vom Skript komplett ingoriert. Jeder kann sich mit jedem Benutzernamen (oder sogar ganz ohne Passwort) einloggen.

    - **Schutzziel:** Vertraulichkeit
    
    - **OWASP:** **A07:2025 - Authentication Failures**    

2. **Session Fixation (Fehlende Erneuerung der Session-ID)**
      - **Das Problem:** Nach dem Login wird die Funktion `session_regenerate_id()` nicht aufgerufen. Die Session-ID bleibt vor und nach dem Login identisch. Ein Angreifer kann dem Opfer eine bekannte Session-ID unterschieben und nach dem Login des Opfers diese Session übernehmen.

    - **Schutzziel:** Vertraulichkeit, Integrität

    - **OWASP:** **A07:2025 - Authentication Failures**

3. **Insecure Direct Object Reference/ Broken Acces Control (Feste Rollenvergabe)**
     - **Das Problem:** Die Admin-Rolle wird vergeben, sobald der eingegebene Benutzername exakt `admin` lautet (`if($_SESSION['username'] == 'admin')`). Da keine Passwortprüfung stattfindet, kann sich jeder Nutzer trivial administrative Rechte zuweisen.

    - **Schutzziel:** Vertraulichkeit, Integrität

    - **OWASP:** **A01:2025 - Broken Access Control**

4. **Unsichere Cookie-Konfiguration (Security Misconfiguration)**
     - **Das Problem:** Es werden keine sicheren Cookie-Parameter gesetzt (z.B. fehlen, `HttpOnly`, `Secure`, `SameSite`). Dadurch können Session-Cookies durch Cross-Site Scripting (XSS) per JavaScript gestohlen oder über unverschlüsselte Verbindungen abgefangen werden.

    - **Schutzziel:** Vertraulichkeit

    - **OWASP:** **A02:2025 - Security Misconfiguration**

5. **Fehlender Schutz vor Cross-Site Request Forgery (CSRF)**
     - **Das Problem:** Weder beim Login, noch beim Speichern der Nachricht, noch beim Logout wird ein Anti-CSRF-Token verwendet. Ein Angreifer könnte einen authentifizierten Nutzer dazu bringen, unbemerkt Aktionen auszuführen.

    - **Schutzziel:** Integrität

    - **OWASP:** **A01:2025 - Broken Access Control**

## C Session-Fixation demonstrieren

Aufgabe C: Demonstrieren Sie den Session-Fixation-Angriff in zwei Browsern live und erklären Sie was passiert.

![](../../img/M183_KN03_3.png)

- Was ist passiert? Konnte der zweite Browser auf die Session des ersten zugreifen?
    
    _Die Session-ID wurde manuell aus dem ersten Browser ausgelesen und in den zweiten Browser übertragen. Nach der erfolgreichen Anmeldung im ersten Browser wurde diese spezifische Session-ID serverseitig als authentifiziert markiert. Beim anschliessenden Neuladen des zweiten Browsers wurde dieselbe, nun authentifizierte Session-ID an den Server übermittelt._

- Warum ist das ein Sicherheitsproblem?

    _Dieser Vorgang demonstriert eine Schwachstelle namens Session Fixation (Sitzungsfixierung). Ein Angreifer kann eine gültige Session-ID generieren und diese einem potenziellen Opfer im Voraus zuweisen (beispielsweise über einen präparierten Link). Sobald sich das Opfer mit seinen legitimen Zugangsdaten anmeldet, wird die vom Angreifer kontrollierte Session serverseitig authentifiziert. Da der Angreifer die Session-ID bereits kennt, erlangt er ab diesem Zeitpunkt unautorisierten Vollzugriff auf das Konto des Opfers, ohne dessen Passwort kompromittieren zu müssen._

- Welche eine Massnahme hätte diesen Angriff verhindert?

    _Die Implementierung der Funktion session_regenerate_id(true) unmittelbar nach der erfolgreichen Validierung der Anmeldedaten. Diese Massnahme bewirkt, dass die bisherige (und potenziell bereits bekannte) Session-ID serverseitig gelöscht und durch eine neu generierte ID ersetzt wird. Ein Angreifer verliert dadurch den Zugriff, da seine zuvor fixierte Session-ID ihre Gültigkeit verliert._

## D Sicherheitslücken beheben

- Video der DevTools mit gesetzten Cookie-Flags (HttpOnly und Secure sichtbar) für vorher (ohne Fix) und nachher (mit Fix).
- Video der erfolgreichen Anmeldung nach Fix 2 (falsches Passwort muss abgelehnt werden).
- Schriftliche Antworten auf die drei Fragen:
    - Was bewirkt `HttpOnly` auf einem Cookie? Gegen welchen Angriff schützt dieses Flag?
    - Was bewirkt `SameSite=Strict?` Gegen welchen Angriff schützt dieses Flag?
    - Warum wurde `password_hash()` mit `PASSWORD_ARGON2ID` und nicht mit MD5 oder SHA-1 verwendet?


## E MFA-Faktoren erklären
Aufgabe E: Erklären Sie die MFA-Faktoren mündlich anhand der Tabelle und beantworten Sie die drei Fragen.

Leitfragen / Checkpoints

Ich kann erklären, was Session Fixation ist und wie `session_regenerate_id(true)` dagegen schützt.
Ich kann erklären, warum das Passwort in der ursprünglichen App nicht geprüft wurde und was das bedeutet.
Ich kann erklären, was `password_hash()` mit Argon2ID bewirkt und warum MD5/SHA-1 für Passwörter ungeeignet sind.
Ich kann die Cookie-Flags `HttpOnly`, `Secure` und `SameSite` erklären und je einen Angriff nennen, gegen den sie schützen.
Ich kann die vier MFA-Faktorkategorien mit je einem Beispiel erklären.
Ich kann erklären, warum «Passwort + PIN» kein echtes MFA ist.
Ich kann erklären, was AWS STS ist und wie es mit temporären Berechtigungen zusammenhängt.