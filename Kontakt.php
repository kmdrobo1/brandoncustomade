<?php
// Kontakt.php

// Ziel-E-Mail-Adresse für Testzwecke
$ziel_email = "zieladresse@example.com"; // Hier beliebige Adresse sein, da MailHog genutzt wir um die NAchricht abzufangen

// Überprüfen, ob das Formular mit der POST-Methode abgesendet wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Formularfelder abrufen und trimmen
    $vorname = trim($_POST["firstname"]);
    $nachname = trim($_POST["lastname"]);
    $land = trim($_POST["country"]);
    $anliegen = trim($_POST["subject"]);

    // Überprüfen, ob alle Felder ausgefüllt sind
    if (empty($vorname) || empty($nachname) || empty($land) || empty($anliegen)) {
        echo "<p>Bitte füllen Sie alle Felder aus.</p>";
    } else {
        // Betreff und Nachricht für die E-Mail vorbereiten
        $betreff = "Neue Kontaktanfrage von $vorname $nachname";
        $nachricht = "Name: $vorname $nachname\n";
        $nachricht .= "Wohnsitz: $land\n";
        $nachricht .= "Anliegen:\n$anliegen";

        // Zusätzliche Header (optional)
        $header = "From: no-reply@example.com\r\n";
        $header .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // E-Mail senden
        $email_gesendet = mail($ziel_email, $betreff, $nachricht, $header);

        if ($email_gesendet) {
            echo "<h2>Vielen Dank für Ihre Nachricht, $vorname $nachname!</h2>";
            echo "<p>Ihre Nachricht wurde erfolgreich übermittelt und kann in MailHog überprüft werden.</p>";
        } else {
            echo "<p>Es gab ein Problem beim Versenden Ihrer Nachricht. Bitte versuchen Sie es später erneut.</p>";
        }
    }
} else {
    echo "<p>Ungültige Anfrage.</p>";
}
?>
