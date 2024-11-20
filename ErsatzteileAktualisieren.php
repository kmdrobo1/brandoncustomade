<?php
header('Content-Type: application/json');
$servername = "mb-mysql.fh-muenster.de";
$username = "dbuser11";
$password = "custom11";
$dbname = "dbuser11_bcm";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Verbindung zur Datenbank fehlgeschlagen."]));
}

// JSON-Daten vom Frontend abrufen
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['cart']) || !is_array($data['cart'])) {
    die(json_encode(["success" => false, "message" => "Ungültiges Datumsformat."]));
}

$errors = [];
foreach ($data['cart'] as $item) {
    $product_id = $item['product_id'];
    $quantity = $item['quantity'];
    $category = $item['category']; // Tabelle/Kategorie des Artikels

    // Sicherheitsüberprüfung für die Tabellen (Bremse, Karosserie, Motor)
    $allowed_tables = ['bremse', 'karosserieteile', 'motor'];
    if (!in_array($category, $allowed_tables)) {
        $errors[] = "Invalid category: " . htmlspecialchars($category);
        continue;
    }

    // Bestandsprüfung und -aktualisierung
    $stmt = $conn->prepare("SELECT Menge_Einheiten FROM $category WHERE id = ?");
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['Menge_Einheiten'] >= $quantity) {
            // Bestand aktualisieren
            $new_quantity = $row['Menge_Einheiten'] - $quantity;
            $update_stmt = $conn->prepare("UPDATE $category SET Menge_Einheiten = ? WHERE id = ?");
            $update_stmt->bind_param('ii', $new_quantity, $product_id);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            $errors[] = "Unzureichender Lagerbestand für die Produkt-ID: $product_id";
        }
    }
    $stmt->close();
}

$conn->close();

if (empty($errors)) {
    echo json_encode(["success" => true, "message" => "Inventar erfolgreich aktualisiert."]);
} else {
    echo json_encode(["success" => false, "errors" => $errors]);
}
?>
