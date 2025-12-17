<?php
// Determine current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
$is_logged_in = is_logged_in(); // Assumes inc/auth.php is included
$is_super_admin = is_super_admin();
?>
<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="index.php">MotoCalendar</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav ms-auto">
                <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>"
                    href="index.php">Kalender</a>

                <?php if ($is_logged_in): ?>
                    <?php if ($is_super_admin): ?>
                        <a class="nav-link <?php echo $current_page == 'admin_clubs.php' ? 'active' : ''; ?>"
                            href="admin_clubs.php">Verwaltung</a>
                        <a class="nav-link <?php echo $current_page == 'clubs.php' ? 'active' : ''; ?>" href="clubs.php">Club
                            Übersicht</a>
                        <a class="nav-link <?php echo $current_page == 'admin_users.php' ? 'active' : ''; ?>"
                            href="admin_users.php">Admins</a>
                        <a class="nav-link <?php echo $current_page == 'admin_logs.php' ? 'active' : ''; ?>"
                            href="admin_logs.php">Logs</a>
                        <a class="nav-link <?php echo $current_page == 'admin_events.php' ? 'active' : ''; ?>"
                            href="admin_events.php">Termine</a>
                        <a class="nav-link <?php echo $current_page == 'admin_backup.php' ? 'active' : ''; ?>"
                            href="admin_backup.php">Backup</a>
                    <?php else: ?>
                        <!-- Club Admin Links -->
                        <a class="nav-link <?php echo $current_page == 'club_profile.php' ? 'active' : ''; ?>"
                            href="club_profile.php">Mein Club</a>
                        <a class="nav-link <?php echo $current_page == 'clubs.php' ? 'active' : ''; ?>" href="clubs.php">Club
                            Übersicht</a>
                        <a class="nav-link <?php echo $current_page == 'admin_events.php' ? 'active' : ''; ?>"
                            href="admin_events.php">Termine</a>
                    <?php endif; ?>
                <?php endif; ?>

                <a class="nav-link <?php echo $current_page == 'info.php' ? 'active' : ''; ?>" href="info.php">Info</a>
                <a class="nav-link <?php echo $current_page == 'impressum.php' ? 'active' : ''; ?>"
                    href="impressum.php">Impressum</a>

                <?php if ($is_logged_in): ?>
                    <a class="nav-link" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="nav-link <?php echo $current_page == 'login.php' ? 'active' : ''; ?>"
                        href="login.php">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>