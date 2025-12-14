<header class="dashboard-header">
    <div class="header-left">
        <h1 class="header-logo">
            <?php 
            $page = basename($_SERVER['PHP_SELF']);
            if ($page == 'index.php') echo 'Dashboard';
            elseif ($page == 'users.php') echo 'Users Management';
            elseif ($page == 'courses.php') echo 'Courses Management';
            elseif ($page == 'purchased-courses.php') echo 'Inscriptions & Sales';
            elseif ($page == 'reports.php') echo 'Reports & Analytics';
            elseif ($page == 'edit_users.php') echo 'Edit User';
            else echo 'Admin Panel';
            ?>
        </h1>
    </div>
    <div class="header-center">
        <!-- Search could go here if implemented -->
    </div>
    <div class="header-right">
        <div class="user-profile">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name'] ?? 'Admin'); ?>&background=random" class="admin-avatar">
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
        </div>
    </div>
</header>
