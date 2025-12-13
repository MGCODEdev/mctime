<?php
require_once 'inc/auth.php'; // For navbar logic
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoCalendar - Impressum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css?v=6" rel="stylesheet">
</head>

<body>
    <?php include 'inc/navbar.php'; ?>

    <div class="container">
        <h2 class="mb-4">Impressum</h2>

        <div class="glass-card mb-4">
            <h3 class="h4 mb-3">Angaben gemäß § 5 TMG / Verantwortlich für den Inhalt</h3>
            <p class="mb-1"><strong>Marcel Kraus</strong></p>
            <p class="mb-1">Rechte Siedlungsstraße 25</p>
            <p class="mb-3">8792 Hessenberg, Österreich</p>

            <h4 class="h5 mt-4 mb-3">Kontakt</h4>
            <p class="mb-1">Telefon: <a href="tel:+436604804887" class="text-light text-decoration-none">+43 660 480 48
                    87</a></p>
            <p class="mb-1">E-Mail: <a href="mailto:marcel.kraus@mk-elektroinstallationen.at"
                    class="text-light text-decoration-none">marcel.kraus@mk-elektroinstallationen.at</a></p>
        </div>

        <div class="glass-card">
            <h3 class="h4 mb-3">Open Source Projekt</h3>
            <p>Dieses Projekt <strong>MotoCalendar</strong> wird als Open Source Software bereitgestellt.</p>

            <h4 class="h5 mt-3">Lizenz (MIT License)</h4>
            <div class="p-3 rounded" style="background: rgba(0,0,0,0.3); font-family: monospace; font-size: 0.9em;">
                <p class="mb-2">Copyright (c) <?php echo date('Y'); ?> Marcel Kraus & Contributors</p>
                <p class="mb-2">Permission is hereby granted, free of charge, to any person obtaining a copy of this
                    software and associated documentation files (the "Software"), to deal in the Software without
                    restriction, including without limitation the rights to use, copy, modify, merge, publish,
                    distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the
                    Software is furnished to do so, subject to the following conditions:</p>
                <p class="mb-2">The above copyright notice and this permission notice shall be included in all copies or
                    substantial portions of the Software.</p>
                <p class="mb-0">THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
                    INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
                    NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES
                    OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
                    CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>