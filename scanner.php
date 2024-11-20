<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: Admin.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner Lagerbestand</title>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-image: url('Bilder/Logo.jpg');
            background-size: cover;
            background-position: center;
            font-family: Poppins, sans-serif;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 60vw;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        #reader {
            width: 500px;
            margin-bottom: 20px;
        }
        #result {
            text-align: center;
            font-size: 1.5rem;
            margin-top: 20px;
        }
        .scan-again, .logout, .update-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            cursor: pointer;
        }
        .update-container {
            margin-top: 20px;
            text-align: center;
        }
        .update-container input {
            width: 100px;
            padding: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div id="reader"></div>
        <div id="result"></div>
        <a class="logout" href="Logout.php">Abmelden</a>
    </div>

    <script>
        const scanner = new Html5QrcodeScanner('reader', { qrbox: { width: 400, height: 400 }, fps: 20 });

        scanner.render(onScanSuccess, onScanError);

        function onScanSuccess(result) {
            const urlParams = new URLSearchParams(new URL(result).search);
            const table = urlParams.get('table');
            const article = urlParams.get('article');

            if (table && article) {
                fetch('backend.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `table=${encodeURIComponent(table)}&article=${encodeURIComponent(article)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('result').innerHTML = `
                            <h3>Scan erfolgreich</h3>
                            <p><strong>Artikelname:</strong> ${data.ArtName}</p>
                            <p><strong>Aktuelle Menge:</strong> <span id="currentQuantity">${data.Menge_Einheiten}</span></p>
                            <div class="update-container">
                                <label for="newQuantity">Neue Menge:</label>
                                <input type="number" id="newQuantity" min="0" value="${data.Menge_Einheiten}">
                                <button class="update-button" onclick="updateQuantity('${table}', '${article}')">Speichern</button>
                            </div>
                            <a class="scan-again" href="scanner.php">Erneut scannen?</a>
                        `;
                    } else {
                        document.getElementById('result').innerHTML = `
                            <h3>Fehler</h3>
                            <p>${data.message}</p>
                            <a class="scan-again" href="scanner.php">Erneut scannen?</a>
                        `;
                    }
                })
                .catch(error => console.error('Fehler beim Abrufen der Daten:', error));

                scanner.clear();
                document.getElementById('reader').remove();
            } else {
                console.error('Ungültiges QR-Code-Format');
                document.getElementById('result').innerHTML = `
                    <h3>Scanfehler</h3>
                    <p>Ungültiges QR-Code-Format.</p>
                    <a class="scan-again" href="scanner.php">Erneut scannen?</a>
                `;
            }
        }

        function onScanError(errorMessage) {
            console.log(`Scanfehler: ${errorMessage}`);
        }

        function updateQuantity(table, article) {
            const newQuantity = document.getElementById('newQuantity').value;

            fetch('backend.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update_quantity&table=${encodeURIComponent(table)}&article=${encodeURIComponent(article)}&newQuantity=${encodeURIComponent(newQuantity)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Aktualisiert die Anzeige der aktuellen Menge als auch das Eingabefeld
                    document.getElementById('currentQuantity').textContent = data.newQuantity;
                    // document.getElementById('newQuantity').value = data.newQuantity;  // Update the input field to the latest quantity
                    document.getElementById('result').innerHTML += `
                        <p>Die Menge wurde erfolgreich aktualisiert.</p>
                    `;
                } else {
                    document.getElementById('result').innerHTML += `
                        <p>Fehler beim Aktualisieren der Menge: ${data.message}</p>
                    `;
                }
            })
            .catch(error => console.error('Fehler beim Aktualisieren der Menge:', error));
        }
    </script>
</body>
</html>
