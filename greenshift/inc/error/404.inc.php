<div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
    <div class="mx-auto max-w-screen-sm text-center">
        <h1 class="mb-4 text-7xl tracking-tight font-extrabold lg:text-9xl text-primary-600 dark:text-green-600">404</h1>
        <p class="mb-4 text-3xl tracking-tight font-bold text-gray-900 md:text-4xl dark:text-green-600">Er ontbreekt iets.</p>
        <p class="mb-4 text-lg font-light text-gray-500 dark:text-gray-400">Sorry, we kunnen die pagina niet vinden. Op de startpagina vindt u veel om te ontdekken.</p>

        <?php if (isset($_SESSION['rolename']) && $_SESSION['rolename'] !== 'logout_user') : ?>
            <?php if ($_SESSION['rolename'] == 'admin') : ?>
                <a href="index.php?page=home" class="inline-flex text-green-600 bg-primary-600 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:focus:ring-primary-900 my-4">Terug naar startpagina</a>
            <?php elseif ($_SESSION['rolename'] == 'Colleague') : ?>
                <a href="index.php?page=home" class="inline-flex text-green-600 bg-primary-600 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:focus:ring-primary-900 my-4">Terug naar startpagina</a>
            <?php endif; ?>
        <?php else : ?>
            <a href="index.php?page=home" class="inline-flex text-green-600 bg-primary-600 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:focus:ring-primary-900 my-4">Terug naar startpagina</a>
        <?php endif; ?>
    </div>
</div>
