<?php
require_once 'inc/auth.php';
require_public_auth();

$month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('m');
$year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');

// Navigation
$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $month + 1;
$next_year = $year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

// Calendar Logic
$first_day_timestamp = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date('t', $first_day_timestamp);
$day_of_week = date('N', $first_day_timestamp); // 1 (Mon) to 7 (Sun)

// Get Events
$start_date = sprintf('%04d-%02d-01', $year, $month);
$end_date = sprintf('%04d-%02d-%02d', $year, $month, $days_in_month);
$events = get_all_events_range($start_date, $end_date);

// Map events to days
$events_by_day = [];
foreach ($events as $event) {
    $day = (int) substr($event['date'], 8, 2);
    $events_by_day[$day][] = $event;
}

$clubs = get_clubs();
$clubs_map = [];
foreach ($clubs as $c)
    $clubs_map[$c['id']] = $c;

$month_names = [
    1 => 'Januar',
    2 => 'Februar',
    3 => 'März',
    4 => 'April',
    5 => 'Mai',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'August',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Dezember'
];

$is_member = is_logged_in(); // True for Club Admin & Super Admin
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoCalendar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css?v=6" rel="stylesheet">
</head>

<body>
    <?php include 'inc/navbar.php'; ?>

    <div class="container-fluid px-4 h-100 d-flex flex-column">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="btn btn-secondary">&laquo;
                Zurück</a>
            <h2 class="text-uppercase fw-bold m-0"><?php echo $month_names[$month] . ' ' . $year; ?></h2>
            <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="btn btn-secondary">Vor
                &raquo;</a>
        </div>

        <ul class="nav nav-pills mb-3 justify-content-center" id="calendarTabs">
            <li class="nav-item">
                <a class="nav-link active" id="tab-open-road" href="#"
                    onclick="switchCalendar('public')">Veranstaltungen
                    (Öffentlich)</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-iron-circle" href="#" onclick="switchCalendar('internal')">Clubabende
                    (Intern)</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-all" href="#" onclick="switchCalendar('all')">Alle Termine</a>
            </li>
        </ul>

        <div class="calendar-container">
            <div class="calendar-header">
                <div>Mo</div>
                <div>Di</div>
                <div>Mi</div>
                <div>Do</div>
                <div>Fr</div>
                <div>Sa</div>
                <div>So</div>
            </div>

            <div class="calendar-grid">
                <?php
                // Empty cells for previous month
                for ($i = 1; $i < $day_of_week; $i++) {
                    echo '<div class="calendar-day empty"></div>';
                }

                // Days
                for ($day = 1; $day <= $days_in_month; $day++) {
                    $date_str = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $is_today = $date_str === date('Y-m-d');
                    $day_num = date('N', strtotime($date_str));
                    $is_weekend = ($day_num == 6 || $day_num == 7);
                    $day_events = $events_by_day[$day] ?? [];

                    echo '<div class="calendar-day ' . ($is_today ? 'today' : '') . ($is_weekend ? ' weekend' : '') . '" onclick="showDayDetails(' . $day . ')">';
                    echo '<div class="day-number">' . $day . '</div>';

                    $max_display = 6; // Show more events on large screens
                    $count = 0;

                    foreach ($day_events as $event) {
                        if ($count < $max_display) {
                            $club = $clubs_map[$event['club_id']] ?? ['color' => '#666', 'shortname' => '?'];
                            $c1 = $club['color'] ?: '#666';
                            $c2 = $club['color2'] ?? null;

                            // Background Style (Gradient if 2 colors)
                            if ($c2) {
                                $bg_style = "background: linear-gradient(135deg, $c1 50%, $c2 50%);";
                            } else {
                                $bg_style = "background-color: " . htmlspecialchars($c1) . ";";
                            }

                            $vis = $event['visibility'] ?? 'public';

                            // Add 'internal-event' class if internal
                            $vis_class = $vis === 'internal' ? 'internal-event' : 'public-event';

                            echo '<div class="event-badge ' . $vis_class . '" style="' . $bg_style . '" title="' . htmlspecialchars($event['title']) . '">';

                            // Logo
                            if (isset($club['logo']) && file_exists('uploads/logos/' . $club['logo'])) {
                                echo '<img src="uploads/logos/' . htmlspecialchars($club['logo']) . '" style="width: 16px; height: 16px; border-radius: 50%; margin-right: 4px; vertical-align: middle;">';
                            }

                            echo htmlspecialchars($club['shortname'] . ': ' . $event['title']);
                            echo '</div>';
                        }
                        $count++;
                    }

                    if ($count > $max_display) {
                        echo '<div class="more-events">+' . ($count - $max_display) . ' weitere</div>';
                    }

                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade" id="dayDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content text-light">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="detailsDate">Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsBody">
                    <!-- Content via JS -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const eventsByDay = <?php echo json_encode($events_by_day); ?>;
        // SECURITY FIX: Filter club data before exposing to JS
        <?php
        $safe_clubs_map = [];
        foreach ($clubs_map as $id => $c) {
            $safe_clubs_map[$id] = Security::filterClubPublic($c);
        }
        ?>
        const clubsMap = <?php echo json_encode($safe_clubs_map); ?>;
        const monthNames = <?php echo json_encode($month_names); ?>;
        const currentYear = <?php echo $year; ?>;
        const currentMonth = <?php echo $month; ?>;
        const isMember = <?php echo $is_member ? 'true' : 'false'; ?>;

        // Initial State
        let currentMode = 'public'; // 'public' or 'internal'

        function switchCalendar(mode) {
            currentMode = mode;

            // Update Tabs
            document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
            if (mode === 'public') document.getElementById('tab-open-road').classList.add('active');
            else if (mode === 'internal') document.getElementById('tab-iron-circle').classList.add('active');
            else document.getElementById('tab-all').classList.add('active');

            // Toggle Events
            document.querySelectorAll('.internal-event').forEach(el => {
                el.style.display = (mode === 'internal' || mode === 'all') ? 'block' : 'none';
            });

            document.querySelectorAll('.public-event').forEach(el => {
                el.style.display = (mode === 'public' || mode === 'all') ? 'block' : 'none';
            });
        }

        // Run once on load to hide internal if default is public
        document.addEventListener("DOMContentLoaded", () => {
            switchCalendar('public');
        });

        function showDayDetails(day) {
            const dateStr = `${day}. ${monthNames[currentMonth]} ${currentYear}`;
            document.getElementById('detailsDate').innerText = dateStr;

            const events = eventsByDay[day] || [];
            let html = '';

            if (events.length === 0) {
                html = '<p class="text-muted">Keine Termine an diesem Tag.</p>';
            } else {
                events.forEach(event => {
                    // Filter in Modal
                    const vis = event.visibility || 'public';
                    if (currentMode === 'public' && vis === 'internal') return;
                    if (currentMode === 'internal' && vis === 'public') return;
                    // if currentMode === 'all', show everything

                    const club = clubsMap[event.club_id] || { name: 'Unbekannt', color: '#666' };

                    // Dual Color Logic
                    const c1 = club.color;
                    const c2 = club.color2;
                    let borderStyle = `border-color: ${c1} !important;`;
                    let logoBorder = `border: 2px solid ${c1};`;
                    let gradientBg = ''; // For something else?

                    if (c2) {
                        // For border, we can't easily do gradient border in pure CSS border-color.
                        // Instead, we can use border-image or just stick to Primary color for border.
                        // Or use a background gradient on the wrapper.
                        // Let's use border-image for valid modern browsers or fallback.
                        // borderStyle = `border-image: linear-gradient(to bottom, ${c1}, ${c2}) 1; border-width: 4px; border-style: solid;`;
                        // But border-radius and border-image conflict.
                        // Simple approach: Use C1. Or split?
                        // Let's stick to C1 for border to keep it clean, OR use a small indicator.
                        // User requirement: "Flaggenartig (nebeneinander ... konsistent UI)".
                        // Maybe the LOGO border should be split?

                        logoBorder = `background: linear-gradient(135deg, ${c1} 50%, ${c2} 50%); padding: 3px; border-radius: 50%;`;
                        // Note: To show gradient ring, we put image inside a div with gradient padding.
                    }

                    // Logo Logic
                    let logoHtml = '';
                    if (club.logo) {
                        if (c2) {
                            logoHtml = `<div style="display: inline-block; ${logoBorder} margin-right: 12px; vertical-align: middle; width: 46px; height: 46px;">
                                <img src="uploads/logos/${club.logo}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; display: block; background: #000;">
                            </div>`;
                        } else {
                            logoHtml = `<img src="uploads/logos/${club.logo}" style="width: 40px; height: 40px; border-radius: 50%; margin-right: 12px; object-fit: cover; border: 2px solid ${club.color};">`;
                        }
                    } else {
                        // Fallback avatar
                        logoHtml = `<div style="width: 40px; height: 40px; border-radius: 50%; margin-right: 12px; background-color: ${club.color}; display: flex; align-items: center; justify-content: center; font-weight: bold; color: white;">${club.shortname.substring(0, 2)}</div>`;
                    }

                    html += `
                        <div class="card mb-3 border-0" style="background-color: rgba(15, 23, 42, 0.8); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5);">
                            <div class="card-body border-start border-4" style="${borderStyle} padding: 1.5rem;">
                                <div class="d-flex align-items-center mb-3">
                                    ${logoHtml}
                                    <div>
                                        <h4 class="card-title text-white mb-0 fw-bold" style="font-size: 1.25rem;">${event.title}</h4>
                                        <div class="text-muted small text-uppercase fw-bold" style="letter-spacing: 0.05em;">${club.name}</div>
                                    </div>
                                </div>
                                
                                <div class="mb-4" style="color: #e2e8f0;">
                                    <div class="d-flex align-items-center mb-2">
                                        <span style="width: 24px; text-align: center; margin-right: 10px; color: var(--accent-color);">🕒</span>
                                        <span class="fw-semibold">${event.time_from || '?'} - ${event.time_to || '?'} Uhr</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span style="width: 24px; text-align: center; margin-right: 10px; color: var(--accent-color);">📍</span>
                                        <span>${event.location || 'Nicht angegeben'}</span>
                                    </div>
                                </div>
                                
                                ${event.description ? `
                                <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                                    <h6 class="text-info text-uppercase small fw-bold mb-2">Beschreibung</h6>
                                    <p class="card-text text-light mb-0" style="white-space: pre-wrap; line-height: 1.6;">${event.description}</p>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                });
            }

            document.getElementById('detailsBody').innerHTML = html;
            new bootstrap.Modal(document.getElementById('dayDetailsModal')).show();
        }
    </script>
</body>

</html>