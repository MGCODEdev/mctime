<?php
require_once 'inc/auth.php';
require_once 'inc/logging.php';

// Only for logged-in users (Club Admins or Super Admins)
require_login();

$clubs = get_clubs();
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Übersicht - MotoCalendar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css?v=6" rel="stylesheet">
    <style>
        .club-card {
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }

        .club-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }

        .club-header {
            height: 80px;
            position: relative;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .club-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(0, 0, 0, 0.5);
            margin-top: -40px;
            background: #000;
            position: relative;
            z-index: 10;
        }
    </style>
</head>

<body>
    <?php include 'inc/navbar.php'; ?>

    <div class="container pb-5">
        <h2 class="mb-4">Club Übersicht</h2>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($clubs as $club): ?>
                <?php if (!$club['active'])
                    continue; ?>
                <?php
                $c1 = $club['color'];
                $c2 = $club['color2'] ?? null;
                if ($c2) {
                    $bg = "linear-gradient(135deg, $c1 50%, $c2 50%)";
                } else {
                    $bg = $c1;
                }
                ?>
                <div class="col">
                    <div class="glass-card club-card d-flex flex-column p-0">
                        <!-- Header Background -->
                        <div class="club-header" style="background: <?php echo $bg; ?>;"></div>

                        <div class="px-4 pb-4 flex-grow-1 text-center">
                            <!-- Logo -->
                            <?php if (!empty($club['logo'])): ?>
                                <img src="uploads/logos/<?php echo htmlspecialchars($club['logo']); ?>"
                                    class="club-logo shadow">
                            <?php else: ?>
                                <div class="club-logo shadow d-flex align-items-center justify-content-center text-white fw-bold fs-3"
                                    style="background: <?php echo $c1; ?>; margin-left: auto; margin-right: auto;">
                                    <?php echo substr($club['shortname'], 0, 2); ?>
                                </div>
                            <?php endif; ?>

                            <h4 class="mt-3 mb-1"><?php echo htmlspecialchars($club['name']); ?></h4>
                            <div class="text-muted small mb-3 text-uppercase fw-bold">
                                <?php echo htmlspecialchars($club['shortname']); ?>
                            </div>

                            <div class="text-start mt-4 px-2">
                                <?php if ($club['president']): ?>
                                    <div class="mb-2"><strong class="text-info">President:</strong>
                                        <?php echo htmlspecialchars($club['president']); ?></div>
                                <?php endif; ?>
                                <?php if ($club['vice_president']): ?>
                                    <div class="mb-2"><strong class="text-info">Vice:</strong>
                                        <?php echo htmlspecialchars($club['vice_president']); ?></div>
                                <?php endif; ?>

                                <hr class="border-secondary my-3">

                                <?php if ($club['contact_email']): ?>
                                    <div class="mb-2">
                                        <span class="me-2">✉️</span>
                                        <a href="mailto:<?php echo htmlspecialchars($club['contact_email']); ?>"
                                            class="text-decoration-none text-light"><?php echo htmlspecialchars($club['contact_email']); ?></a>
                                    </div>
                                <?php endif; ?>

                                <?php if ($club['website']): ?>
                                    <div class="mb-2">
                                        <span class="me-2">🌐</span>
                                        <a href="<?php echo htmlspecialchars($club['website']); ?>" target="_blank"
                                            class="text-decoration-none text-light text-truncate d-inline-block"
                                            style="max-width: 200px; vertical-align: bottom;">
                                            Webseite besuchen
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <?php if ($club['meeting_place']): ?>
                                    <div class="mb-2 d-flex">
                                        <span class="me-2">📍</span>
                                        <span><?php echo htmlspecialchars($club['meeting_place']); ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($club['meeting_time']): ?>
                                    <div class="mb-2 d-flex">
                                        <span class="me-2">🕒</span>
                                        <span
                                            class="text-muted small"><?php echo htmlspecialchars($club['meeting_time']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>