# StreamCast - Broken Access Control

<toc>



## Intro

    Heute widmen wir uns der Nummer 1 der OWASP Top10: Broken Access Control. Wir werden uns anschauen, was Broken Access Control bedeutet, wie Angreifer diese Schwachstelle ausnutzen können und welche Maßnahmen Entwickler ergreifen können, um ihre Anwendungen sicherer zu machen.

    Was ist Broken Access Control? Es handelt sich dabei um eine Schwachstelle, bei der Angreifer unberechtigten Zugriff auf Ressourcen oder Funktionen einer Anwendung erhalten können. Dies kann passieren, wenn die Anwendung nicht richtig überprüft, ob ein Benutzer autorisiert ist, auf bestimmte Daten oder Funktionen zuzugreifen.

## Arten von Broken Access Control

- [Broken Access Control Examples](https://brightsec.com/blog/broken-access-control-attack-examples-and-4-defensive-measures/)
- URL Manipulation:
- Exploiting Endpoints:
- Privilege Escalation:
- Insecure Direct Object References (IDOR):

    Das konkrete Problem das wir heute betrachten nennt sich Insecure Direct Object References (IDOR). Dabei handelt es sich um eine Art von Broken Access Control, bei der Angreifer durch Manipulation von URLs oder Parametern Zugriff auf Ressourcen erhalten können, die sie eigentlich nicht sehen sollten.

    Dazu werden keine speziellen Tools benötigt, sondern lediglich ein Webbrowser und etwas Kreativität. Angreifer können beispielsweise die URL einer Anwendung manipulieren, um auf Daten anderer Benutzer zuzugreifen, indem sie einfach die ID in der URL hochzählen (ID-Enumeration).
    

- [A01 Broken Access Control @ owasp.org](https://owasp.org/Top10/2025/A01_2025-Broken_Access_Control/) 

## Real World Examples

### [Passar BAZG](https://www.bazg.admin.ch/de/passar-warenverkehrssystem)

    Im Passar-Warenverkehrssystem war es letztes Jahr möglich, die Ausfuhrbescheinigung eines anderen Unternehmens einzusehen. Dazu benötigte man lediglich einen Account und eine eigene Ausfuhrbescheinigung. Anschliessend konnte man die URL manipulieren und fremde Bescheinigungen abrufen. Da die Ausfuhrbescheinigung eine ID im leicht zu erratenden Datumsformat enthielt, liess sich diese einfach abändern, um unberechtigten Zugriff auf die Dokumente anderer Unternehmen zu erhalten. Das Problem wurde behoben, indem die ID der Ausfuhrbescheinigung nicht mehr im Datumsformat, sondern als sichere UUID (Universally Unique Identifier) generiert wird.

## [First American Financial Mega-Leak(2019)](https://krebsonsecurity.com/2019/05/first-american-financial-corp-leaked-hundreds-of-millions-of-title-insurance-records/)

- [DFS Filing](https://krebsonsecurity.com/2020/07/ny-charges-first-american-financial-for-massive-data-leak/)


### [Optus Daten-Breach(2019)](https://www.upguard.com/blog/how-did-the-optus-data-breach-happen)

### [Uber (2016)](https://hackerone.com/reports/194594)

### [XZ Utils Backdoor]()

### [MOVEit Data Breach]()
### [US Department of State Email Breach]()
### [Spring Security Broken Access Control]()
## Massnahmen

- [Massnahmen und Real World Examples](https://www.radware.com/cyberpedia/application-security/broken-access-control-vulnerabilities/)
### 1. Prinzip der geringsten Privilegien
Das Prinzip der geringsten Privilegien besagt, dass Benutzer und Prozesse nur die minimalen Berechtigungen erhalten sollten, die sie benötigen, um ihre Aufgaben auszuführen. Dadurch wird das Risiko von unberechtigtem Zugriff reduziert, da Angreifer nicht auf Ressourcen zugreifen können, für die sie keine Berechtigung haben.

### 2. Rollen und Attribut basierte Zugriffskontrolle

**RBAC** (Role-Based Access Control): Implementieren Sie ein rollenbasiertes Zugriffskontrollsystem, bei dem Benutzer basierend auf ihren Rollen und Berechtigungen Zugriff auf Ressourcen erhalten.

**ABAC** (Attribute-Based Access Control): Verwenden Sie ein attributbasiertes Zugriffskontrollsystem, bei dem Zugriff auf Ressourcen basierend auf verschiedenen Attributen wie Benutzerrollen, Zeit, Standort usw. gewährt wird.

### 3. Secure Session & Token Management
Stellen Sie sicher, dass Sitzungen und Tokens sicher verwaltet werden, um unbefug  


### 4. Reguläre Überprüfung und Zugriffsreviews
Führen Sie regelmäßige Überprüfungen der Zugriffskontrollen durch, um sicherzustellen, dass Benutzer nur Zugriff auf Ressourcen haben, die sie benötigen. Entfernen Sie veraltete oder unnötige Berechtigungen, um das Risiko von unberechtigtem Zugriff zu minimieren.

### 5. MFA (Multi-Factor Authentication)
Implementieren Sie Multi-Faktor-Authentifizierung, um die Sicherheit von Benutzerkonten zu erhöhen. Dadurch wird es Angreifern erschwert, Zugriff auf Konten zu erhalten, selbst wenn sie die Anmeldeinformationen eines Benutzers kompromittieren.