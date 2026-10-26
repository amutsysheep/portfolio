<!-- Navbar (Visible on mobile and acts as toggle on desktop) -->
<nav class="fixed top-0 left-0 h-16 flex items-center px-6">
    <!-- Menu Button (Opens Sidebar) -->
    <button id="menu-toggle" class="text-gray-500 text-2xl">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="">
            <path d="M3 12H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <path d="M3 6H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <path d="M3 18H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
    </button>
</nav>

<!-- Sidebar -->
<div id="sidebar" class="fixed top-0 left-0 h-screen w-64 bg-white shadow-md transform -translate-x-full transition-transform duration-300 z-50">
    <div class="flex items-center justify-between h-16 px-6 shadow-md">
        <!-- Close Button (Now always visible) -->
        <button id="close-sidebar" class="text-gray-500 text-2xl">
            <i class="bx bx-x"></i>
        </button>
    </div>
    <ul class="flex flex-col py-4">
        <?php if (isset($_SESSION['rolename']) && $_SESSION['rolename'] !== 'logout_user') : ?>
            <?php if ($_SESSION['rolename'] == 'Admin') : ?>
                <li>
                    <a href="<?php echo $base_url; ?>/index.php?page=home&panel=Admin" class="flex flex-row items-center h-12 px-6 text-gray-500 hover:text-gray-800">
                        <i class="bx bx-home text-lg mr-2"></i>
                        <span class="text-sm font-medium">Home</span>
                    </a>
                </li>

                <li>
                    <a href="<?php echo $base_url; ?>/index.php?page=instructormangement&panel=Admin" class="flex flex-row items-center h-12 px-6 text-gray-500 hover:text-gray-800">
                        <i class="bx bx-home text-lg mr-2"></i>
                        <span class="text-sm font-medium">Rij-instructeur beheer</span>
                    </a>
                </li>

                <li>
                    <a href="<?php echo $base_url; ?>/index.php?page=lessonpackage&panel=Admin" class="flex flex-row items-center h-12 px-6 text-gray-500 hover:text-gray-800">
                        <i class="bx bx-home text-lg mr-2"></i>
                        <span class="text-sm font-medium">Lespakket beheer</span>
                    </a>
                </li>

                <li>
                    <a href="<?php echo $base_url; ?>/index.php?page=studentoverview&panel=Admin" class="flex flex-row items-center h-12 px-6 text-gray-500 hover:text-gray-800">
                        <i class="bx bx-box text-lg mr-2"></i>
                        <span class="text-sm font-medium">Leerling overzicht</span>
                    </a>
                </li>

            <?php elseif ($_SESSION['rolename'] == 'Driving_Instructor') : ?>
                <li>
                    <a href="index.php?page=home&panel=Driving_Instructor" class="flex flex-row items-center h-12 px-6 text-gray-500 hover:text-gray-800">
                        <i class="bx bx-home text-lg mr-2"></i>
                        <span class="text-sm font-medium">Home</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=editinstructor&panel=Driving_Instructor" class="flex flex-row items-center h-12 px-6 text-gray-500 hover:text-gray-800">
                        <i class="bx bx-home text-lg mr-2"></i>
                        <span class="text-sm font-medium">Proiel aanpassen</span>
                    </a>
                </li>
              
            <?php endif; ?>
            <li>
                <a href="php/driving_instructor/logout.php" class="flex flex-row items-center h-12 px-6 text-gray-500 hover:text-gray-800">
                    <i class="bx bx-log-out text-lg mr-2"></i>
                    <span class="text-sm font-medium">Uitloggen</span>
                </a>
            </li>
        <?php else : ?>
        <?php endif; ?>
    </ul>
</div>

<!-- JavaScript for Sidebar Toggle -->
<script>
    const menuToggle = document.getElementById('menu-toggle');
    const closeSidebar = document.getElementById('close-sidebar');
    const sidebar = document.getElementById('sidebar');

    menuToggle.addEventListener('click', function () {
        sidebar.classList.toggle('-translate-x-full');
    });

    closeSidebar.addEventListener('click', function () {
        sidebar.classList.add('-translate-x-full');
    });

    // Close sidebar if clicking outside
    document.addEventListener('click', function (event) {
        if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
            sidebar.classList.add('-translate-x-full');
        }
    });
</script>
