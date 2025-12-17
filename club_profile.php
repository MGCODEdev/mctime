<?php
require_once 'inc/auth.php';
require_once 'inc/logging.php';

// Ensure user is logged in and is a Club Admin
require_login();
if (!is_club_admin()) {
    // If Super Admin tries to access, maybe redirect to admin_clubs?
    // But requirement says "Clubs ... manage self".
    if (is_super_admin()) {
        header('Location: admin_clubs.php');
        exit;
    }
    die("Zugriff verweigert.");
}

$club_id = $_SESSION['user_id']; // For Club Admin, user_id IS club_id
$club = get_club($club_id);

if (!$club) {
    die("Club nicht gefunden.");
}

$error = '';
$success = '';

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::verifyCsrfToken();

    // We only allow editing specific fields
    // Login Name and Active status are NOT editable by Club Admin for security/system integrity
    // unless specified otherwise. Usually Login Name is fixed or Admin-only.
    // Requirement: "manage own data (Profile, Events, Contact)".

    $contact_email = trim($_POST['contact_email'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $president = trim($_POST['president'] ?? '');
    $vice_president = trim($_POST['vice_president'] ?? '');
    $meeting_place = trim($_POST['meeting_place'] ?? '');
    $meeting_time = trim($_POST['meeting_time'] ?? '');
    $founded_date = !empty($_POST['founded_date']) ? $_POST['founded_date'] : null;

    // Club Color & Logo
    $color = $_POST['color'] ?? $club['color'];
    $color2 = $_POST['color2'] ?? $club['color2'];
    $logo_data = $_POST['logo_data'] ?? '';

    // Password Update
    $password = $_POST['password'] ?? '';

    // Validation
    if ($contact_email && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Ungültige E-Mail-Adresse.";
    }
    if ($website && !filter_var($website, FILTER_VALIDATE_URL)) {
        $error = "Ungültige Webseiten-URL.";
    }

    if (!$error) {
        $club_data = $club; // Start with existing

        // Update permitted fields
        $club_data['contact_email'] = $contact_email;
        $club_data['website'] = $website;
        $club_data['president'] = $president;
        $club_data['vice_president'] = $vice_president;
        $club_data['meeting_place'] = $meeting_place;
        $club_data['meeting_time'] = $meeting_time;
        $club_data['founded_date'] = $founded_date;
        $club_data['color'] = $color;
        $club_data['color2'] = $color2;

        // Password
        if (!empty($password)) {
            $club_data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        } else {
            // Keep existing hash (handled by save_club logic if we pass empty hash, logic might vary)
            // Actually save_club only updates if has is present.
            // But we must be careful not to OVERWRITE with empty if logic expects full object.
            // Let's rely on save_club logic: "if !empty(password_hash) ... UPDATE ... password_hash = ..."
            // So if we don't set it in $club_data, or set it to empty?
            // save_club binds :password_hash from $club_data.
            // If we want to keep it, we should pass the EXISTING hash OR handle logic.
            // Our save_club has: if (!empty($club_data['password_hash'])) ...
            // So if we don't change it, we should UNSET it or pass empty string?
            // If we pass empty string, save_club uses the FIRST query (without password column). Correct.
            $club_data['password_hash'] = '';
        }

        // Logo Processing
        if ($logo_data) {
            $logo_data = preg_replace('#^data:image/\w+;base64,#i', '', $logo_data);
            $decoded = base64_decode($logo_data);
            if ($decoded) {
                $safe_id = Security::sanitizeFilename($club_id);
                $filename = 'logo_' . $safe_id . '.png';
                $upload_dir = __DIR__ . '/uploads/logos/';
                if (!is_dir($upload_dir))
                    mkdir($upload_dir, 0777, true);

                $filepath = $upload_dir . $filename;
                if (@file_put_contents($filepath, $decoded)) {
                    $club_data['logo'] = $filename;
                }
            }
        }

        if (save_club($club_data)) {
            system_log('CLUB_UPDATE_SELF', "ID: $club_id");
            $success = "Profil aktualisiert.";
            // Refresh data
            $club = get_club($club_id);
            // Update Session Color if changed
            $_SESSION['club_color'] = $club['color'];
        } else {
            $error = "Fehler beim Speichern.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mein Club - MotoCalendar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    <link href="assets/css/style.css?v=6" rel="stylesheet">
    <style>
        .img-container {
            max-width: 100%;
            max-height: 400px;
        }

        .preview-container {
            overflow: hidden;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 2px solid var(--glass-border);
            margin: 10px auto;
        }
    </style>
</head>

<body>
    <?php include 'inc/navbar.php'; ?>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Mein Club Profil</h2>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger custom-alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success custom-alert"
                style="border-color: rgba(34, 197, 94, 0.2); color: #86efac; background: rgba(34, 197, 94, 0.1);">
                <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="glass-card p-4">
            <form method="post" id="clubForm">
                <?php echo Security::csrfField(); ?>
                <input type="hidden" name="logo_data" id="logo_data">

                <div class="row">
                    <!-- Left Column: Identity & Logo -->
                    <div class="col-md-4 text-center mb-4">
                        <h4 class="mb-3">Logo & Identität</h4>
                        <div class="mb-3">
                            <label class="form-label">Aktuelles Logo</label>
                            <div class="d-flex justify-content-center">
                                <?php if (!empty($club['logo'])): ?>
                                    <img src="uploads/logos/<?php echo htmlspecialchars($club['logo']); ?>?v=<?php echo time(); ?>"
                                        style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid <?php echo $club['color']; ?>">
                                <?php else: ?>
                                    <div
                                        style="width: 120px; height: 120px; border-radius: 50%; background: <?php echo $club['color']; ?>; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; color: white;">
                                        <?php echo substr($club['shortname'], 0, 2); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3 text-start">
                            <label class="form-label">Neues Logo hochladen</label>
                            <input type="file" class="form-control" id="logo_input" accept="image/*">
                        </div>
                        <div class="img-container mb-2">
                            <img id="image_preview" src="" style="max-width: 100%; display: none;">
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary mb-3" id="crop_btn"
                            style="display: none;">Zuschneiden</button>
                        <div id="result_preview"></div>

                        <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">

                        <div class="row text-start">
                            <div class="col-6 mb-3">
                                <label class="form-label">Club Farbe 1</label>
                                <input type="color" class="form-control form-control-color w-100" name="color"
                                    value="<?php echo htmlspecialchars($club['color']); ?>">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Farbe 2</label>
                                <input type="color" class="form-control form-control-color w-100" name="color2"
                                    value="<?php echo htmlspecialchars($club['color2'] ?? '#ffffff'); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Details -->
                    <div class="col-md-8">
                        <h4 class="mb-3">Club Details</h4>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Club Name (Kürzel:
                                    <?php echo htmlspecialchars($club['shortname']); ?>)</label>
                                <input type="text" class="form-control"
                                    value="<?php echo htmlspecialchars($club['name']); ?>" disabled readonly>
                                <div class="form-text text-muted">Name kann nur vom Admin geändert werden.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Passwort ändern</label>
                                <input type="password" class="form-control" name="password"
                                    placeholder="Neues Passwort (optional)">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kontakt E-Mail</label>
                                <input type="email" class="form-control" name="contact_email"
                                    value="<?php echo htmlspecialchars($club['contact_email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Webseite</label>
                                <input type="url" class="form-control" name="website"
                                    value="<?php echo htmlspecialchars($club['website'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Präsident</label>
                                <input type="text" class="form-control" name="president"
                                    value="<?php echo htmlspecialchars($club['president'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vize-Präsident</label>
                                <input type="text" class="form-control" name="vice_president"
                                    value="<?php echo htmlspecialchars($club['vice_president'] ?? ''); ?>">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Stammlokal / Treffpunkt</label>
                                <input type="text" class="form-control" name="meeting_place"
                                    value="<?php echo htmlspecialchars($club['meeting_place'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Treffzeit</label>
                                <input type="text" class="form-control" name="meeting_time"
                                    value="<?php echo htmlspecialchars($club['meeting_time'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gründungsdatum</label>
                                <input type="date" class="form-control" name="founded_date"
                                    value="<?php echo htmlspecialchars($club['founded_date'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4">Speichern</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script>
        let cropper;
        const image = document.getElementById('image_preview');
        const input = document.getElementById('logo_input');
        const cropBtn = document.getElementById('crop_btn');
        const logoData = document.getElementById('logo_data');
        const resultPreview = document.getElementById('result_preview');

        input.addEventListener('change', function (e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                const file = files[0];
                const reader = new FileReader();
                reader.onload = function (e) {
                    image.src = e.target.result;
                    image.style.display = 'block';
                    if (cropper) { cropper.destroy(); }
                    cropper = new Cropper(image, {
                        aspectRatio: 1,
                        viewMode: 1,
                        preview: '.preview-container'
                    });
                    cropBtn.style.display = 'inline-block';
                };
                reader.readAsDataURL(file);
            }
        });

        cropBtn.addEventListener('click', function () {
            if (cropper) {
                const canvas = cropper.getCroppedCanvas({ width: 200, height: 200 });
                const base64 = canvas.toDataURL('image/png');
                logoData.value = base64;
                resultPreview.innerHTML = `<img src="${base64}" style="width: 100px; height: 100px; border-radius: 50%; border: 2px solid white;">`;
                cropBtn.innerText = "Aktualisiert";
            }
        });
    </script>
</body>

</html>