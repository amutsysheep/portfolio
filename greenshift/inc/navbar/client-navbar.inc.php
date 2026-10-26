<?php
// 1) Read role once and derive a boolean you can reuse
$role = $_SESSION['rolename'] ?? 'logout_user';
$logout = ($role === 'logout_user');
$isClient = ($role === 'Client');
?>

<header class="relative z-50">
    <!-- Background overlay -->
    <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-green-600/70 to-green-600/30"></div>

    <nav class="relative container mx-auto px-4 lg:px-8">
        <div class="flex items-center justify-between py-4 lg:py-5">
            <!-- Left: Logo -->
            <a href="<?php echo generateUrl('home'); ?>" class="flex items-center gap-2 text-green-700">
        <span class="text-2xl font-extrabold tracking-tight leading-none">
          <img src="img/greenshift.png" class="h-8 w-auto" alt="Logo">
        </span>
            </a>

            <!-- Mobile menu button -->
            <button id="navToggle"
                    class="lg:hidden inline-flex items-center justify-center rounded-md p-2 text-white/90 hover:text-white hover:bg-white/10 focus:outline-none"
                    aria-expanded="false" aria-controls="primaryMenu">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M4 6h16M4 12h16M4 18h16" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>

            <!-- Center: Menu -->
            <ul id="primaryMenu" class="hidden lg:flex items-center gap-9 font-semibold text-white">
                <li><a class="hover:text-white/80" href="<?php echo generateUrl('pricing'); ?>">Tarieven</a></li>
                <li><a class="hover:text-white/80" href="<?php echo generateUrl('about-us'); ?>">Over ons</a></li>
                <?php if ($logout): ?>
                <li><a class="hover:text-white/80" href="<?php echo generateUrl('trial-lesson'); ?>">Proefrijles</a></li>
                <?php endif; ?>
                <li><a class="hover:text-white/80" href="<?php echo generateUrl('driving-lesson-package'); ?>">rijles pakketten</a></li>
                <?php if ($isClient): ?>
                <li><a class="hover:text-white/80" href="<?php echo generateUrl('single-lesson'); ?>">Rijles</a></li>
                    <li><a class="hover:text-white/80" href="<?php echo generateUrl('pakket-inplannen'); ?>">Pakket inplannen</a></li>
                <?php endif; ?>
                <li><a class="hover:text-white/80" href="<?php echo generateUrl('help'); ?>">Hulp</a></li>
                <li><a class="hover:text-white/80" href=""></a></li>
                <li><a class="hover:text-white/80" href=""></a></li>
            </ul>
            <!-- Right side: Basket + Auth -->
            <div class="hidden lg:flex items-center gap-8 text-white">
                <a href="<?php echo generateUrl('basket'); ?>" class="flex items-center gap-2 hover:text-white/80">
                    <svg class="w-[17px] h-[17px] stroke-2 stroke-current fill-none" viewBox="0 0 24 24">
                        <path
                                d="M3 6L6 2H18L21 6M3 6V20C3 20.5304 3.21071 21.0391 3.58579 21.4142C3.96086 21.7893 4.46957 22 5 22H19C19.5304 22 20.0391 21.7893 20.4142 21.4142C20.7893 21.0391 21 20.5304 21 20V6M3 6H21M16 10C16 11.0609 15.5786 12.0783 14.8284 12.8284C14.0783 13.5786 13.0609 14 12 14C10.9391 14 9.92172 13.5786 9.17157 12.8284C8.42143 12.0783 8 11.0609 8 10" />
                    </svg>
                    <span class="font-extrabold">Basket</span>
                </a>

                <?php if ($isClient): ?>
                    <a href="php/user/logout.php" class="flex items-center gap-2 hover:text-white/80">
                        <div class="font-extrabold">Uitloggen</div>
                    </a>
                <?php else: ?>
                    <a href="<?php echo generateUrl('login'); ?>" class="flex items-center gap-2 hover:text-white/80">
                        <div class="font-extrabold">Login</div>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile dropdown -->
        <nav id="mobilePanel" class="lg:hidden hidden pb-4">
            <ul class="flex flex-col gap-1 rounded-xl bg-white/5 p-2 text-white font-semibold backdrop-blur">
                <li>
                    <a href="<?php echo generateUrl('pricing'); ?>"
                       class="block rounded-lg px-4 py-3 hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/40">
                        Tarieven
                    </a>
                </li>
                <li>
                    <a href="<?php echo generateUrl('about-us'); ?>"
                       class="block rounded-lg px-4 py-3 hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/40">
                        Over ons
                    </a>
                </li>
                <li>
                    <a href="<?php echo generateUrl('trial-lesson'); ?>"
                       class="block rounded-lg px-4 py-3 hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/40">
                        Proefrijles
                    </a>
                </li>
                <li>
                    <a href="<?php echo generateUrl('help'); ?>"
                       class="block rounded-lg px-4 py-3 hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/40">
                        Hulp
                    </a>
                </li>

                <!-- Divider -->
                <li class="my-2 border-t border-white/10"></li>

                <!-- Mobile auth action -->
                <?php if ($isClient): ?>
                    <li>
                        <a href="../../php/user/logout.php"
                           class="block rounded-lg px-4 py-3 text-center bg-white/10 hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/40">
                            Uitloggen
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="<?php echo generateUrl('login'); ?>"
                           class="block rounded-lg px-4 py-3 text-center bg-white/10 hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/40">
                            Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </nav>
</header>

<script>
    // simple mobile toggle
    const btn = document.getElementById('navToggle');
    const panel = document.getElementById('mobilePanel');
    btn?.addEventListener('click', () => {
        const open = panel.classList.toggle('hidden') === false;
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
</script>
