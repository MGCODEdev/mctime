<?php
$pageTitle = "Über uns";
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mein Portal - <?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <nav>
            <a href="index.php" class="logo">PortalApp</a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="about.php" class="active">Über uns</a>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h1>Über dieses Projekt</h1>
            <p class="lead">Eine Demonstration einer einfachen PHP-Webanwendung.</p>
        </section>

        <div class="card-grid">
            <div class="card" style="grid-column: 1 / -1;">
                <h2>Unsere Mission</h2>
                <p>Wir erstellen sauberen, wartbaren Code mit modernen Web-Technologien. Diese Seite dient als Beispiel
                    für Routing und Layout in einer einfachen PHP-Umgebung ohne Frameworks.</p>
                <br>
                <a href="index.php" class="btn">Zurück zur Startseite</a>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Mein Portal. Alle Rechte vorbehalten.</p>
    </footer>
</body>

</html>