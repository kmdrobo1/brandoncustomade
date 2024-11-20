<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['table']) && isset($_POST['article'])) {
    $table = $_POST['table'];
    $article = $_POST['article'];
    $allowedTables = ['bremse', 'karosserieteile', 'motor'];

    if (!in_array($table, $allowedTables)) {
        echo json_encode([ 'status' => 'error', 'message' => 'Ungültige Tabelle' ]);
        exit;
    }

    $host = 'mb-mysql.fh-muenster.de';
    $username = 'dbuser11';
    $password = 'custom11';
    $dbname = 'dbuser11_bcm';

    $conn = new mysqli($host, $username, $password, $dbname);

    if ($conn->connect_error) {
        echo json_encode([ 'status' => 'error', 'message' => "Verbindung fehlgeschlagen: " . $conn->connect_error ]);
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'update_quantity' && isset($_POST['newQuantity'])) {
        $newQuantity = $_POST['newQuantity'];
        $sqlUpdate = "UPDATE $table SET Menge_Einheiten = ? WHERE id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ii", $newQuantity, $article);
        $stmtUpdate->execute();

        if ($stmtUpdate->affected_rows > 0) {
            $sqlSelect = "SELECT Menge_Einheiten FROM $table WHERE id = ?";
            $stmtSelect = $conn->prepare($sqlSelect);
            $stmtSelect->bind_param("i", $article);
            $stmtSelect->execute();
            $result = $stmtSelect->get_result();

            if ($result->num_rows > 0) {
                $data = $result->fetch_assoc();
                echo json_encode([ 
                    'status' => 'success', 
                    'message' => 'Menge erfolgreich aktualisiert',
                    'newQuantity' => $data['Menge_Einheiten']
                ]);
            } else {
                echo json_encode([ 'status' => 'error', 'message' => 'Fehler beim Abrufen der neuen Menge' ]);
            }

            $stmtSelect->close();
        } else {
            echo json_encode([ 'status' => 'error', 'message' => 'Fehler beim Aktualisieren der Menge' ]);
        }

        $stmtUpdate->close();
    } else {
        $sql = "SELECT Art_Beschreibung, Menge_Einheiten FROM $table WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $article);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            echo json_encode([ 
                'status' => 'success', 
                'ArtName' => $data['Art_Beschreibung'],
                'Menge_Einheiten' => $data['Menge_Einheiten']
            ]);
        } else {
            echo json_encode([ 'status' => 'error', 'message' => 'Artikel nicht gefunden' ]);
        }

        $stmt->close();
    }

    $conn->close();
}
?>
