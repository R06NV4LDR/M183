# 183 - KN02 - SQL Injection, XSS, CSRF, IDOR, JWT

## A WebGoat starten

![](../../img/M183_KN02_2.png)
![](../../img/M183_KN02_1.png)

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

    Gibt ein Angreifer `1 OR 1=1`  ein, sucht die Datenbank buchstäblich nach einem Benutzer, dessen ID exakt der String `1 OR "1 OR 1=1"` ist. Der Payload wird wie harmloser Freitext behandelt und verliert jegliche subversive Wirkung.

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

- Screenshot des ausgelösten Alerts bei C2 (Stored) nach dem Speichern des Kommentars.

- Screenshot der gelösten WebGoat-Aufgabe C2 (grüne Bestätigung).

- Schriftliche Antworten auf die fünf Fragen.

    - Was ist der zentrale Unterschied zwischen Reflected XSS und Stored XSS hinsichtlich Persistenz und Reichweite?

        __
    - Was unterscheidet DOM-based XSS von Reflected XSS – warum ist DOM-based XSS für serverseitige Filter schwieriger zu erkennen?

        __
    - Was bedeutet Output Encoding und warum schützt es gegen XSS? Geben Sie ein konkretes Beispiel, wie `<script>` nach dem Encoding aussieht.

        __
    - Was ist der HTTP-Header Content-Security-Policy (CSP) und wie schränkt er XSS ein? (Recherchieren Sie falls nötig.)

        __
    - Welche OWASP Top 10 Kategorie (2021) beschreibt XSS? Nennen Sie Nummer und Bezeichnung.

        __

## D Cross-Site Request Forgery (CSRF)

## E Broken Access Control (IDOR)

## F Broken Authentication (JWT)