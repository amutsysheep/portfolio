<?php
// Selecteer alle gebruikers met rolid 3 (rij-instructeurs)
$stmt = $pdo->prepare("SELECT lesson_packages.*
                              FROM lesson_packages");
$stmt->execute();
$instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Hero -->
<header class="text-black">
    <div class="mx-auto max-w-6xl px-6 py-14">
        <div class="flex items-center gap-3">
            <div class="rounded-xl bg-gradient-to-r from-green-500 to-emerald-500 p-2 shadow-lg"></div>
            <p class="text-sm tracking-widest text-black">RIJES RIJSCHOOL</p>
        </div>
        <h1 class="mt-4 text-4xl font-bold sm:text-5xl">Kies je rijles pakket</h1>
        <p class="mt-3 max-w-2xl text-black">
            Helder, betaalbaar en flexibel. Start wanneer jij wil – wij regelen de rest.
        </p>
        <div class="mt-6 flex flex-wrap items-center gap-3">
            <span class="bg-white/10 text-black hover:bg-white/20 px-3 py-1 rounded-full text-sm">CBR-ervaren instructeurs</span>
            <span class="bg-white/10 text-black hover:bg-white/20 px-3 py-1 rounded-full text-sm">Betalen in termijnen</span>
            <span class="bg-white/10 text-black hover:bg-white/20 px-3 py-1 rounded-full text-sm">Lessen in automaat of schakel</span>
        </div>
    </div>
</header>

<!-- ✅ All packages in one grid section -->
<section class="mx-auto max-w-6xl px-6 py-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($instructors as $instr): ?>
    <form action="php/user/add-to-cart.php" method="POST">
        <div class="relative overflow-hidden rounded-2xl border border-neutral-200 shadow-sm">
            <input type="hidden" name="id" value="<?= htmlspecialchars($instr['id']) ?>">
            <div class="p-6">
                <p class="text-sm text-neutral-500 flex items-center gap-2"><span>🚗</span> Pakket</p>
                <h2 class="text-2xl font-semibold"><?= htmlspecialchars($instr['name']) ?></h2>
                <div class="mt-1 flex items-baseline gap-1">
                    <span class="text-4xl font-bold">€<?= htmlspecialchars($instr['price'] ?? '') ?></span>
                    <span class="text-sm text-neutral-500">per pakket</span>
                </div>
                <ul class="mt-4 space-y-2 text-sm">
                    <li>✅ <?= htmlspecialchars($instr['number_of_lessons'] ?? '') ?> lessen (60 min)</li>
                    <li>✅ Flexibele lestijden</li>
                </ul>
                <hr/>
                <ul>
                    <a class=" font-semibold"><?= htmlspecialchars($instr['description']) ?></a>
                </ul>
                <button class="mt-6 w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700">Plan proefles</button>
            </div>
        </div>
    </form>
    <?php endforeach; ?>
</section>