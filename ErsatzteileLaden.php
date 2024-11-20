<?php
header('Content-Type: application/json');
$servername = "mb-mysql.fh-muenster.de"; // Server
$username = "dbuser11"; // Nutzername
$password = "custom11"; // Passwort
$dbname = "dbuser11_bcm"; // Name der Datenbank

// Verbindung aufbauen
$conn = new mysqli($servername, $username, $password, $dbname);

// Verbindung prüfen
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Filterparameter abrufen
$search = isset($_GET['search']) ? $_GET['search'] : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 999999;
$category = isset($_GET['category']) ? $_GET['category'] : 'bremse'; // Standard-Kategorie

// Erlaubten Tabellen, um SQL-Injection zu verhindern
$allowed_tables = ['bremse', 'karosserieteile', 'motor'];
if (!in_array($category, $allowed_tables)) {
    die(json_encode([])); // Leere Antwort, wenn eine ungültige Tabelle gewählt wird
}

// SQL-Abfrage vorbereiten und Bestand (Menge_Einheiten) hinzufügen
$sql = "SELECT id, Art_Beschreibung, Preis, Grafik, Menge_Einheiten FROM $category 
        WHERE Art_Beschreibung LIKE ? AND Preis BETWEEN ? AND ?";

$stmt = $conn->prepare($sql);
$searchTerm = "%" . $search . "%";
$stmt->bind_param('sdd', $searchTerm, $min_price, $max_price);

$stmt->execute();
$result = $stmt->get_result();

$products = array();
while ($row = $result->fetch_assoc()) {
    // Bilddaten konvertieren und den Bestand zur JSON-Ausgabe hinzufügen
    $row['Grafik'] = base64_encode($row['Grafik']);
    $products[] = $row;
}

// JSON-Ausgabe für das Frontend
echo json_encode($products);

$stmt->close();
$conn->close();
?>
