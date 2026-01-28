<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <img src="../../LogoEdu.png" alt="3edu+ Logo" style="max-height: 50px;">
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <p class="nav-section-title">MENU</p>
            <ul class="nav-menu">
                <?php 
                $role = $_SESSION['user_role'] ?? 'Admin';
                $dashboard_url = ($role === 'Marketing') ? '../marketing/index.php' : 'index.php';
                ?>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <a href="<?php echo $dashboard_url; ?>" class="nav-link">
                        <i class="fas fa-chart-line nav-icon"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' || basename($_SERVER['PHP_SELF']) == 'edit_users.php' ? 'active' : ''; ?>">
                    <a href="users.php" class="nav-link">
                        <i class="fas fa-users nav-icon"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'courses.php' ? 'active' : ''; ?>">
                    <a href="courses.php" class="nav-link">
                        <i class="fas fa-book-open nav-icon"></i>
                        <span>Courses</span>
                    </a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'purchased-courses.php' ? 'active' : ''; ?>">
                    <a href="purchased-courses.php" class="nav-link">
                        <i class="fas fa-briefcase nav-icon"></i>
                        <span>Inscriptions</span>
                    </a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage_events.php' ? 'active' : ''; ?>">
                    <a href="manage_events.php" class="nav-link">
                        <i class="fas fa-calendar nav-icon"></i>
                        <span>Events</span>
                    </a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
                    <a href="payments.php" class="nav-link">
                        <i class="fas fa-credit-card nav-icon"></i>
                        <span>Payments</span>
                    </a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage_quizzes.php' ? 'active' : ''; ?>">
                    <a href="manage_quizzes.php" class="nav-link">
                        <i class="fas fa-question-circle nav-icon"></i>
                        <span>Quizzes</span>
                    </a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage_sessions.php' ? 'active' : ''; ?>">
                    <a href="manage_sessions.php" class="nav-link">
                        <i class="fas fa-clock nav-icon"></i>
                        <span>Sessions</span>
                    </a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                    <a href="reports.php" class="nav-link">
                        <i class="fas fa-file-alt nav-icon"></i>
                        <span>Reports</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="nav-section">
            <p class="nav-section-title">OTHERS</p>
            <ul class="nav-menu">
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                    <a href="profile.php" class="nav-link">
                        <i class="fas fa-user nav-icon"></i>
                        <span>Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../../logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt nav-icon"></i>
                        <span>Logout</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../../index.php" class="nav-link">
                        <i class="fas fa-home nav-icon"></i>
                        <span>Home</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</aside>
