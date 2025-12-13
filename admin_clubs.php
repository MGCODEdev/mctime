require_once 'inc/auth.php';
require_once 'inc/logging.php';
require_super_admin();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$error = '';
$success = '';

// Handle Create/Update Club
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
Security::verifyCsrfToken();
if ($_POST['action'] === 'save_club') {
$id = $_POST['id'] ?: uniqid();
$is_new = empty($_POST['id']);
$name = trim($_POST['name']);

// ... params ...
$shortname = trim($_POST['shortname']);
$color = $_POST['color'];
$login_name = trim($_POST['login_name']);
$password = $_POST['password'];
$active = isset($_POST['active']);
$logo_data = $_POST['logo_data'] ?? ''; // Base64 encoded image

// New Fields
$contact_email = trim($_POST['contact_email'] ?? '');
$website = trim($_POST['website'] ?? '');
$president = trim($_POST['president'] ?? '');
$vice_president = trim($_POST['vice_president'] ?? '');
$meeting_place = trim($_POST['meeting_place'] ?? '');
$meeting_time = trim($_POST['meeting_time'] ?? '');
$founded_date = !empty($_POST['founded_date']) ? $_POST['founded_date'] : null;

if ($name && $login_name) {
// Validation
if ($contact_email && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
$error = "Ungültige E-Mail-Adresse.";
}
if ($website && !filter_var($website, FILTER_VALIDATE_URL)) {
$error = "Ungültige Webseiten-URL.";
}

$club_data = [
'id' => $id,
'name' => $name,
'shortname' => $shortname,
'color' => $color,
'login_name' => $login_name,
'active' => $active,
'contact_email' => $contact_email,
'website' => $website,
'president' => $president,
'vice_president' => $vice_president,
'meeting_place' => $meeting_place,
'meeting_time' => $meeting_time,
'founded_date' => $founded_date
];

// Handle Logo Upload
if ($logo_data) {
// Remove data:image/png;base64, prefix
$logo_data = preg_replace('#^data:image/\w+;base64,#i', '', $logo_data);
$decoded = base64_decode($logo_data);

if ($decoded) {
// FIX: Path Traversal
// Sanitize ID to ensure it's safe for filename
$safe_id = Security::sanitizeFilename($id);
$filename = 'logo_' . $safe_id . '.png';
$upload_dir = __DIR__ . '/uploads/logos/';

if (!is_dir($upload_dir)) {
mkdir($upload_dir, 0777, true);
}

$filepath = $upload_dir . $filename;
if (@file_put_contents($filepath, $decoded)) {
$club_data['logo'] = $filename;
} else {
$error = "Fehler beim Speichern des Logos (Rechteproblem?).";
}
}
} else {
// Keep existing logo
$existing = get_club($id);
if ($existing && isset($existing['logo'])) {
$club_data['logo'] = $existing['logo'];
}
}

// Only update password if provided or if it's a new club
if ($password) {
$club_data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
} else {
// Keep existing password if editing
$existing = get_club($id);
if ($existing) {
$club_data['password_hash'] = $existing['password_hash'];
} else {
$error = "Passwort ist für neue Clubs erforderlich.";
}
}

if (!$error) {
if (save_club($club_data)) {
$action_type = $is_new ? 'CLUB_CREATE' : 'CLUB_UPDATE';
system_log($action_type, "Name: $name, Login: $login_name");
$success = "Club gespeichert.";
} else {
$error = "Fehler beim Speichern.";
}
}
} else {
$error = "Name und Login-Name sind Pflichtfelder.";
}
} elseif ($_POST['action'] === 'delete_club') {
$cancel_id = $_POST['id'];
if (delete_club($cancel_id)) {
system_log('CLUB_DELETE', "ID: $cancel_id");
$success = "Club gelöscht.";
}
}
}

$clubs = get_clubs();
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoCalendar - Clubs verwalten</title>
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
            <h2>Clubs verwalten</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editClubModal"
                onclick="clearModal()">Neuer Club</button>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger custom-alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success custom-alert"
                style="color: #86efac; background: rgba(34, 197, 94, 0.1); border-color: rgba(34, 197, 94, 0.2);">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="glass-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Logo</th>
                            <th>Name</th>
                            <th>Kürzel</th>
                            <th>Login</th>
                            <th>Farbe</th>
                            <th>Status</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clubs as $club): ?>
                            <tr>
                                <td>
                                    <?php if (isset($club['logo']) && file_exists('uploads/logos/' . $club['logo'])): ?>
                                        <img src="uploads/logos/<?php echo htmlspecialchars($club['logo']); ?>" alt="Logo"
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    <?php else: ?>
                                        <div
                                            style="width: 40px; height: 40px; border-radius: 50%; background: <?php echo htmlspecialchars($club['color']); ?>; display: flex; align-items: center; justify-content: center; font-weight: bold; color: white;">
                                            <?php echo substr($club['shortname'], 0, 2); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($club['name']); ?></td>
                                <td><?php echo htmlspecialchars($club['shortname']); ?></td>
                                <td><?php echo htmlspecialchars($club['login_name']); ?></td>
                                <td><span class="badge rounded-pill"
                                        style="background-color: <?php echo htmlspecialchars($club['color']); ?>; width: 24px; height: 24px;">&nbsp;</span>
                                </td>
                                <td><?php echo $club['active'] ? '<span class="badge bg-success bg-opacity-25 text-success border border-success">Aktiv</span>' : '<span class="badge bg-danger bg-opacity-25 text-danger border border-danger">Inaktiv</span>'; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-secondary"
                                        onclick='editClub(<?php echo json_encode($club); ?>)'>Bearbeiten</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editClubModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content text-light">
                <form method="post" id="clubForm">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">Club bearbeiten</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php echo Security::csrfField(); ?>
                        <input type="hidden" name="action" value="save_club">
                        <input type="hidden" name="id" id="club_id">
                        <input type="hidden" name="logo_data" id="logo_data">

                        <ul class="nav nav-tabs mb-3" id="clubTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="general-tab" data-bs-toggle="tab"
                                    data-bs-target="#general" type="button" role="tab"
                                    aria-selected="true">Allgemein</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#details"
                                    type="button" role="tab" aria-selected="false">Details</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="clubTabsContent">
                            <!-- General Tab -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="form-label">Name</label>
                                            <input type="text" class="form-control" name="name" id="club_name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Kürzel</label>
                                            <input type="text" class="form-control" name="shortname"
                                                id="club_shortname">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Farbe</label>
                                            <input type="color" class="form-control form-control-color w-100"
                                                name="color" id="club_color" value="#d32f2f">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Login Name</label>
                                            <input type="text" class="form-control" name="login_name"
                                                id="club_login_name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Passwort <small class="text-muted">(leer lassen
                                                    zum Beibehalten)</small></label>
                                            <input type="password" class="form-control" name="password"
                                                id="club_password">
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="active"
                                                id="club_active" checked>
                                            <label class="form-check-label" for="club_active">Aktiv</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <label class="form-label">Logo</label>
                                        <div class="mb-3">
                                            <input type="file" class="form-control" id="logo_input" accept="image/*">
                                        </div>
                                        <div class="img-container mb-2">
                                            <img id="image_preview" src="" style="max-width: 100%; display: none;">
                                        </div>
                                        <button type="button" class="btn btn-sm btn-secondary" id="crop_btn"
                                            style="display: none;">Zuschneiden</button>
                                        <div id="result_preview" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Details Tab -->
                            <div class="tab-pane fade" id="details" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kontakt E-Mail</label>
                                        <input type="email" class="form-control" name="contact_email"
                                            id="club_contact_email">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Webseite</label>
                                        <input type="url" class="form-control" name="website" id="club_website"
                                            placeholder="https://...">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Präsident</label>
                                        <input type="text" class="form-control" name="president" id="club_president">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Vize-Präsident</label>
                                        <input type="text" class="form-control" name="vice_president"
                                            id="club_vice_president">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Stammlokal / Treffpunkt</label>
                                        <input type="text" class="form-control" name="meeting_place"
                                            id="club_meeting_place">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Treffzeit</label>
                                        <input type="text" class="form-control" name="meeting_time"
                                            id="club_meeting_time" placeholder="z.B. Jeden 1. Freitag">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Gründungsdatum</label>
                                        <input type="date" class="form-control" name="founded_date"
                                            id="club_founded_date">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                        <button type="submit" class="btn btn-primary">Speichern</button>
                    </div>
                </form>
            </div>
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
                    if (cropper) {
                        cropper.destroy();
                    }
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
                const canvas = cropper.getCroppedCanvas({
                    width: 200,
                    height: 200,
                });
                const base64 = canvas.toDataURL('image/png');
                logoData.value = base64;

                resultPreview.innerHTML = `<img src="${base64}" style="width: 100px; height: 100px; border-radius: 50%; border: 2px solid white;">`;
                cropBtn.innerText = "Aktualisiert";
            }
        });

        function editClub(club) {
            document.getElementById('club_id').value = club.id;
            document.getElementById('club_name').value = club.name;
            document.getElementById('club_shortname').value = club.shortname;
            document.getElementById('club_color').value = club.color;
            document.getElementById('club_login_name').value = club.login_name;
            document.getElementById('club_active').checked = club.active;
            document.getElementById('club_password').value = '';

            // New Fields
            document.getElementById('club_contact_email').value = club.contact_email || '';
            document.getElementById('club_website').value = club.website || '';
            document.getElementById('club_president').value = club.president || '';
            document.getElementById('club_vice_president').value = club.vice_president || '';
            document.getElementById('club_meeting_place').value = club.meeting_place || '';
            document.getElementById('club_meeting_time').value = club.meeting_time || '';
            document.getElementById('club_founded_date').value = club.founded_date || '';

            // Reset Cropper
            if (cropper) {
                cropper.destroy();
                image.style.display = 'none';
                cropBtn.style.display = 'none';
            }
            input.value = '';
            logoData.value = '';

            if (club.logo) {
                resultPreview.innerHTML = `<img src="uploads/logos/${club.logo}" style="width: 100px; height: 100px; border-radius: 50%; border: 2px solid white;">`;
            } else {
                resultPreview.innerHTML = '';
            }

            // Switch to first tab
            const firstTabEl = document.querySelector('#clubTabs button[data-bs-target="#general"]');
            const tab = new bootstrap.Tab(firstTabEl);
            tab.show();

            new bootstrap.Modal(document.getElementById('editClubModal')).show();
        }

        function clearModal() {
            document.getElementById('club_id').value = '';
            document.getElementById('club_name').value = '';
            document.getElementById('club_shortname').value = '';
            document.getElementById('club_color').value = '#d32f2f';
            document.getElementById('club_login_name').value = '';
            document.getElementById('club_active').checked = true;
            document.getElementById('club_password').value = '';

            // Clear New Fields
            document.getElementById('club_contact_email').value = '';
            document.getElementById('club_website').value = '';
            document.getElementById('club_president').value = '';
            document.getElementById('club_vice_president').value = '';
            document.getElementById('club_meeting_place').value = '';
            document.getElementById('club_meeting_time').value = '';
            document.getElementById('club_founded_date').value = '';

            if (cropper) {
                cropper.destroy();
                image.style.display = 'none';
                cropBtn.style.display = 'none';
            }
            input.value = '';
            logoData.value = '';
            resultPreview.innerHTML = '';

            // Switch to first tab
            const firstTabEl = document.querySelector('#clubTabs button[data-bs-target="#general"]');
            const tab = new bootstrap.Tab(firstTabEl);
            tab.show();
        }
    </script>
</body>

</html>