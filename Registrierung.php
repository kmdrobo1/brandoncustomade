<?php
// Datenbankverbindung herstellen
$servername = "mb-mysql.fh-muenster.de"; //Server
$username = "dbuser11"; // Nutzername
$password = "custom11"; // Passwort
$dbname = "dbuser11_bcm"; // Name der Datenbank

$conn = new mysqli($servername, $username, $password, $dbname);

// Verbindung überprüfen
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Formulardaten verarbeiten
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $benutzername = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $passwort = $_POST['password'];
    $passwort_bestaetigung = $_POST['confirm_password'];

    // Überprüfen, ob das Passwort und die Bestätigung übereinstimmen
    if ($passwort === $passwort_bestaetigung) {
        // Passwort hashen
        $passwort_gehasht = password_hash($passwort, PASSWORD_DEFAULT);

        // Überprüfen, ob der Benutzername bereits existiert
        $sql = "SELECT id FROM benutzer WHERE benutzername = '$benutzername'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "Benutzername ist bereits vergeben. Bitte wählen Sie einen anderen.";
        } else {
            // Benutzer in die Datenbank einfügen
            $sql = "INSERT INTO benutzer (benutzername, email, passwort) VALUES ('$benutzername', '$email', '$passwort_gehasht')";

            if ($conn->query($sql) === TRUE) {
                echo "Registrierung erfolgreich!";
                header("Location: login.php");
            } else {
                echo "Fehler: " . $sql . "<br>" . $conn->error;
            }
        }
    } else {
        echo "Passwörter stimmen nicht überein.";
    }
}

$conn->close();

