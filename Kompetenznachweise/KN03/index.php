  GNU nano 7.2                                                                                                                                                                                                                                                                                                                                                           /home/ubuntu/m183/2 Sessionhandling, Authentifizierung und Autorisierung/Sessionhandling/AufgabeSource/index.php
<?php

// --- AUFGABE D: Fix 2 (Passwort-Hashing mit Argon2ID) ---
// Gehashte Passwörter (in einer echten App in der Datenbank
$users = [
        'admin' => password_hash('geheim123', PASSWORD_ARGON2ID),
        'alice' => password_hash('passwort', PASSWORD_ARGON2ID),
];

// --- AUFGABE D: Fix 3 (Cookie-Flags setzen) ---
// Cookie-Parameter MÜSSEN vor session_start() gesetzt werden
session_set_cookie_params([
        'lifetime'      => 900,         // 15 Minuten Timeout
        'secure'        => true,        // Nur über HTTPS senden
        'httponly'      => true,        // Kein Zugriff via JavaScript
        'samesite'      => 'Strict'     // Kein Cross-Site-Senden
]);

session_start();

if(isset($_POST['logout'])) {
    session_destroy();
    header("location:./index.php");
    exit();
}

// --- AUFGABE D: Fix 2 (Passwort-Hashing mit Argon2ID - Login-Block ersetzen) ---
if(isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if(isset($users[$username]) && password_verify($password, $users[$username])) {
        // --- AUFGABE D: Fix 1 (Session-ID nach Login erneuern) ---
        session_regenerate_id(true);
        $_SESSION['username'] = $username;
        $_SESSION['role'] = ($username === 'admin') ? 'admin' : 'user';
    }
}

if(isset($_POST['speichern'])) {
    $_SESSION['secret_message'] = $_POST['message'];
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meine Webseite</title>
    <!-- Füge hier deine CSS-Dateien oder Styles ein -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Willkommen auf meiner Webseite</h1>
    </header>

    <main>
        <section>
            <h2>Session-Daten:</h2>
            <pre><?php var_dump($_SESSION); ?></pre>
        </section>

        <?php if(!isset($_SESSION['username'])) { ?>
        <section>
            <h2>Login</h2>
            <form action="./index.php" method="post">
                <p>Benutzername: <input type="text" name="username" /></p>
                <p>Passwort: <input type="password" name="password" /></p>
                <p><input type="submit" name="login" value="login" /></p>
            </form>
            <p><b>Hinweis:</b> Dieses Login-Formular ist ein "Fake". Oben bei den Session-Daten sehen Sie, was in der Session gespeichert wird, wenn Sie das Login-Formular verwenden. Verwenden Sie für das Login einen beliebigen Benutzernamen und ein beliebiges Passwort. Beobachten Sie was mit den Session-Daten geschieht. Wenn Sie den Benutzernamen admin verwenden (mit beliebigem Passwort), dann erhalten Sie zusätzliche Rechte.</p>
        </section>
        <?php } else { ?>
        <section>
            <h2>Geheime Daten</h2>
            <form action="./index.php" method="post">
                <p>Geheimer Inhalt in Session: <input type="text" name="message" /></p>
                <p><input type="submit" name="speichern" value="speichern" /></p>
            </form>
            <p><b>Hinweis:</b> Sie können hier geheime Inhalte in der Session speichern. Ihr Kollege / Ihre Kollegin kann anschliessens schauen, ob er diese auch in seinem Browser angezeigt kriegt, wenn Sie Ihm / Ihr die Session-ID bekannt geben.</p>
        </section>
        <section>
            <h2>Logout</h2>
            <form action="./index.php" method="post">
                <p><input type="submit" name="logout" value="logout" /></p>
            </form>
        </section>
        <?php } ?>
    </main>

    <footer>
    </footer>

    <!-- Füge hier deine JavaScript-Dateien oder Skripte ein -->
    <script src="scripts.js"></script>
</body>
</html>