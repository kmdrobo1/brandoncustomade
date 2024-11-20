<?php
session_start();

// Datenbankverbindung herstellen
$servername = "mb-mysql.fh-muenster.de";
$username = "dbuser11";
$password = "custom11";
$dbname = "dbuser11_bcm";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verbindung überprüfen
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Formulardaten verarbeiten
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $benutzername = $_POST['username'];
    $passwort = $_POST['password'];

    // Prepared Statement für sicherere Abfrage
    $stmt = $conn->prepare("SELECT * FROM benutzer WHERE benutzername = ?");
    $stmt->bind_param("s", $benutzername);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $benutzer = $result->fetch_assoc();
        if (password_verify($passwort, $benutzer['passwort'])) {
            $_SESSION['username'] = $benutzername;
            header("Location: scanner.php");
            exit;
        } else {
            echo "Ungültiges Passwort.";
        }
    } else {
        echo "Benutzername nicht gefunden.";
    }
    $stmt->close();
}

$conn->close();
?>
