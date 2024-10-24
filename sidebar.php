<!-- sidebar.php -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-cog"></i> Admin Panel
    </div>

    <!-- Members Dropdown -->
    <a href="#membersSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fas fa-users"></i> Members</a>
    <ul class="collapse list-unstyled" id="membersSubmenu">
        <li>
            <a href="members_women.php"><i class="fas fa-female"></i> Women</a>
        </li>
        <li>
            <a href="members_men.php"><i class="fas fa-male"></i> Men</a>
        </li>
    </ul>

    <!-- Memberships Dropdown -->
    <a href="#membershipsSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fas fa-id-badge"></i> Memberships</a>
    <ul class="collapse list-unstyled" id="membershipsSubmenu">
        <li>
            <a href="memberships_women.php"><i class="fas fa-female"></i> Women</a>
        </li>
        <li>
            <a href="memberships_men.php"><i class="fas fa-male"></i> Men</a>
        </li>
    </ul>

    <!-- Payments -->
    <a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a>

    <!-- Notifications -->
    <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>

    <!-- Insurance -->
    <a href="insurance.php"><i class="fas fa-shield-alt"></i> Insurance</a>

    <!-- Reports -->
    <a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a>

    <!-- Profile -->
    <a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a>

    <!-- Logout -->
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<style>
    .sidebar {
        width: 250px;
        background-color: #343a40;
        padding: 15px;
        height: 100vh;
        position: fixed;
        transition: all 0.3s ease;
        left: 0;
        color: white;
    }

    .sidebar-header {
        font-size: 22px;
        color: white;
        margin-bottom: 20px;
        text-align: center;
    }

    .sidebar a {
        color: white;
        padding: 10px;
        text-decoration: none;
        display: block;
        transition: 0.3s;
    }

    .sidebar a:hover {
        background-color: #495057;
    }

    .sidebar .dropdown-toggle::after {
        float: right;
        margin-top: 6px;
    }

    .list-unstyled {
        padding-left: 0;
        list-style-type: none;
    }

    .list-unstyled li {
        padding-left: 10px;
    }

    .list-unstyled li a {
        display: flex;
        align-items: center;
        padding-left: 10px;
    }

    .list-unstyled li i {
        margin-right: 10px;
    }

    .collapse .list-unstyled {
        margin-left: 10px;
    }
</style>