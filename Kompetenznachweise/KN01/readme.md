# 183 - KN01 - XSS, CSRF, Client-State Manipulation


Serveo: https://a42379c259cb7d7e-54-165-155-69.serveousercontent.com

Gruyere: https://google-gruyere.appspot.com/530823917152430333453354905065921721830/

## A Gruyere starten und Accounts erstellen

![Gruyere Accounts](../../img/M183_KN01_1.png)

## B Stored XSS in Gruyere

### B1 - DOM-Manipulation als Proof of Concept

1. Warum konnte dieser Payload die Sicherheitsprüfung des Browsers umgehen, obwohl <script> blockiert wird?

2. Was bedeutet es für die Sicherheit, dass der Payload auch im Browser des Verteidigers ausgeführt wird?

3. Welche OWASP Top 10 Kategorie (2025) beschreibt Stored XSS? Nennen Sie Nummer und Bezeichnung.

4. Was hätte die Applikation tun müssen, damit dieser Payload harmlos bleibt? (Stichwort: Output Encoding)

![Gruyere Stored XSS](../../img/M183_KN01_2.png)