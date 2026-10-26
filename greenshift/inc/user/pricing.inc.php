<!-- ========== TARIEVEN (Hero) ========== -->
<section class="relative bg-white text-green-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div class="grid lg:grid-cols-2 gap-10 items-start">

            <!-- Left copy -->
            <div>
                <h2 class="text-3xl sm:text-4xl font-extrabold">TARIEVEN</h2>
                <div class="mt-2 h-0.5 w-12 bg-green-600"></div>

                <p class="mt-4 text-sm uppercase tracking-widest text-green-600 font-bold">Altijd een vaste lage lesprijs</p>
                <ul class="mt-4 space-y-2 text-green-700">
                    <li class="flex gap-2"><span>✅</span> €7 korting per les (handgeschakeld)</li>
                    <li class="flex gap-2"><span>✅</span> Morgen een proefrijles, dat kan!</li>
                    <li class="flex gap-2"><span>✅</span> Meerdere rijlessen per week</li>
                    <li class="flex gap-2"><span>✅</span> Sneller je rijbewijs met minder lessen</li>
                </ul>
            </div>

            <!-- Right promo card -->
            <div class="lg:justify-self-end">
                <div class="relative">
                    <div class="absolute -top-6 -left-4 text-2xl">🍩</div>
                    <div class="pointer-events-none absolute -inset-2 rounded-3xl border-2 border-green-500/60 -z-10"></div>

                    <div class="rounded-3xl bg-white shadow-2xl ring-1 ring-green-100 p-6 sm:p-8 w-full max-w-sm">
                        <p class="text-green-600 font-bold text-sm">START MET RIJLESSEN</p>
                        <h3 class="mt-1 text-2xl sm:text-3xl font-extrabold">BACK TO SCHOOL ACTIE <span>🎒</span></h3>
                        <p class="text-xs text-green-600">Aanmelden voor 29 september</p>

                        <div class="mt-5 flex items-end gap-3">
                            <div class="line-through text-green-400 text-sm">58,50</div>
                            <div class="text-4xl font-extrabold text-green-700 leading-none">51,50</div>
                            <div class="text-sm text-green-600 mb-1">per les</div>
                        </div>

                        <a href="<?php echo generateUrl('help'); ?>"
                           class="mt-6 inline-flex w-full items-center justify-center rounded-md bg-green-600 hover:bg-green-700 text-white font-semibold py-3 shadow-md focus:outline-none focus:ring-2 focus:ring-green-400">
                            Plan je gratis proefrijles
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Two content blocks -->
        <div class="mt-14 grid lg:grid-cols-2 gap-10">
            <div>
                <p class="text-green-500 tracking-widest font-extrabold text-sm">SNELLER JE RIJBEWIJS</p>
                <h3 class="text-2xl font-extrabold -mt-1">Met minder lessen</h3>
                <p class="mt-4 text-green-700">
                    Hoe langer je rijopleiding duurt, hoe meer lessen je nodig hebt. Bij Rijless.nl kun je meerdere lessen per week
                    afspreken. Daardoor leer je sneller autorijden en heb je minder herhaling nodig.
                </p>
                <p class="mt-4 text-green-700">
                    Plan eenvoudig online je proefrijles. Wil je advies op maat van onze instructeurs? Vraag gerust!
                </p>
            </div>

            <div>
                <p class="text-green-500 tracking-widest font-extrabold text-sm">GEZAKT?</p>
                <h3 class="text-2xl font-extrabold -mt-1">Opnieuw examen doen op onze kosten</h3>
                <p class="mt-4 text-green-700">
                    Stoppen met rijles na een mislukte poging? Niet nodig. Wij betalen je herexamen (onder voorwaarden), zodat je
                    zonder stress snel weer door kunt.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ========== PRIJSVOORBEELDEN ========== -->
<section class="relative bg-white text-green-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <p class="text-center text-green-600 tracking-widest font-extrabold text-sm">PRIJSVOORBEELDEN</p>

        <div class="mt-8 grid lg:grid-cols-3 gap-10">
            <!-- Pricing table card -->
            <div class="lg:col-span-2">
                <div class="relative">
                    <div class="absolute -top-3 -right-3 bg-green-600 text-white text-xs px-2 py-1 rounded-full shadow">12% korting</div>
                    <div class="pointer-events-none absolute -inset-2 rounded-3xl border-2 border-green-500/60 -z-10"></div>

                    <div class="rounded-3xl bg-white shadow-2xl ring-1 ring-green-100 p-6 sm:p-8">
                        <p class="text-green-600 font-bold text-sm">START MET RIJLESSEN</p>
                        <h3 class="text-2xl sm:text-3xl font-extrabold">BACK TO SCHOOL ACTIE</h3>
                        <p class="text-xs text-green-600">Aanmelden voor 29 september</p>

                        <div class="mt-6 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                <tr class="text-left text-green-600 border-b border-green-200">
                                    <th class="py-2 pr-4">Lessen</th>
                                    <th class="py-2 pr-4">Prijs</th>
                                    <th class="py-2 pr-4">Actie</th>
                                    <th class="py-2 pr-4">Bespaar</th>
                                </tr>
                                </thead>
                                <tbody class="text-green-800">
                                <?php
                                $rows = [
                                    ['10', '515,-', '—', '70,-'],
                                    ['15', '772,-', '—', '105,-'],
                                    ['20', '1.030,-', '—', '140,-'],
                                    ['25', '1.287,-', '—', '175,-'],
                                    ['30', '1.545,-', '—', '210,-'],
                                ];
                                foreach ($rows as $r) {
                                    echo '<tr class="border-b border-green-100">';
                                    echo '<td class="py-2 pr-4">'.$r[0].'</td>';
                                    echo '<td class="py-2 pr-4">'.$r[1].'</td>';
                                    echo '<td class="py-2 pr-4">'.$r[2].'</td>';
                                    echo '<td class="py-2 pr-4">'.$r[3].'</td>';
                                    echo '</tr>';
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>

                        <p class="mt-3 text-[12px] text-green-600">Deze korting geldt niet in combinatie met een abonnement.</p>
                    </div>
                </div>
            </div>

            <!-- Side list: overige kosten -->
            <div>
                <h4 class="font-extrabold text-lg">Overige kosten</h4>

                <div class="mt-4">
                    <p class="text-sm font-semibold text-green-700">CBR</p>
                    <ul class="mt-2 space-y-1 text-sm text-green-800">
                        <li>- Rijexamen CBR: 130,-</li>
                        <li>- Tussentijdse toets: 120,-</li>
                        <li>- Gezondheidsverklaring: 44,50</li>
                    </ul>
                </div>

                <div class="mt-6">
                    <p class="text-sm font-semibold text-green-700">Rijless</p>
                    <ul class="mt-2 space-y-1 text-sm text-green-800">
                        <li>- Praktijkexamen: 250,-</li>
                        <li>- TTT begeleiding: 210,-</li>
                        <li>- Herexamen: 250,- (onder voorwaarden)</li>
                    </ul>
                </div>

                <div class="mt-6">
                    <p class="text-sm font-semibold text-green-700">Lespakket / lesauto</p>
                    <ul class="mt-2 space-y-1 text-sm text-green-800">
                        <li>- 60 min / 1 les</li>
                        <li>- Instructeur blijft dezelfde</li>
                        <li>- Moderne lesauto’s, batterijen inbegrepen</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== BETAALMOGELIJKHEDEN + PROEFRIJLES ========== -->
<section class="relative bg-white text-green-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <p class="text-center text-green-600 tracking-widest font-extrabold text-sm">BETAALMOGELIJKHEDEN</p>

        <div class="mt-8 grid lg:grid-cols-2 gap-10 items-start">
            <!-- Left copy -->
            <div>
                <h3 class="text-2xl font-extrabold">Stel je rijbewijs halen niet uit</h3>
                <p class="mt-4 text-green-700">
                    Bij andere rijscholen moet je alles in één keer betalen. Daarom kiezen veel leerlingen hun
                    rijopleiding uit te stellen. Bij ons kun je per les of per maand betalen.
                </p>
                <p class="mt-4 text-green-700">Waarom zou je wachten? Meld je nu aan; bij Rijless betaal je per les of per maand.</p>
            </div>

            <!-- Right: payment card -->
            <div class="relative max-w-md">
                <div class="absolute -top-3 -right-3 text-yellow-400 text-2xl">🔖</div>
                <div class="pointer-events-none absolute -inset-2 rounded-3xl border-2 border-green-500/60 -z-10"></div>

                <div class="rounded-3xl bg-white shadow-2xl ring-1 ring-green-100 p-6 sm:p-8">
                    <div class="flex items-center justify-between">
                        <h4 class="font-extrabold">Betaalwijze</h4>
                        <span class="text-sm text-green-600 font-semibold">Kosten</span>
                    </div>
                    <ul class="mt-4 divide-y divide-green-100">
                        <li class="py-3 flex items-center justify-between text-sm">
                            <span>Betaal per les</span><span class="text-green-600 font-semibold">Gratis</span>
                        </li>
                        <li class="py-3 flex items-center justify-between text-sm">
                            <span>iDeal</span><span class="text-green-600 font-semibold">Gratis</span>
                        </li>
                        <li class="py-3 flex items-center justify-between text-sm">
                            <span>Bankoverschrijving</span><span class="text-green-600 font-semibold">Gratis</span>
                        </li>
                        <li class="py-3 flex items-center justify-between text-sm">
                            <span>Gespreid per maand</span><span class="text-green-600 font-semibold">Gratis</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Free trial block -->
        <div class="mt-16 grid lg:grid-cols-2 gap-10">
            <div>
                <p class="text-green-500 tracking-widest font-extrabold text-sm">DE PROEFRIJLES 💚</p>
                <h3 class="text-2xl font-extrabold -mt-1">KRIJG JE VAN ONS</h3>

                <ul class="mt-4 space-y-2 text-green-700">
                    <li class="flex gap-2"><span>✅</span> Plan gemakkelijk zelf online</li>
                    <li class="flex gap-2"><span>✅</span> Je verplicht je niet om door te gaan</li>
                    <li class="flex gap-2"><span>✅</span> We halen je thuis op</li>
                    <li class="flex gap-2"><span>✅</span> Advies op maat, laten weten wat je kan</li>
                    <li class="flex gap-2"><span>✅</span> Gratis als je verder gaat en anders €0</li>
                </ul>

                <p class="mt-3 text-sm text-green-700">Vanaf morgen? Dat kan!</p>

                <a href="<?php echo generateUrl('help'); ?>"
                   class="mt-4 inline-flex items-center justify-center px-6 py-3 rounded-md bg-green-600 hover:bg-green-700 text-white font-semibold shadow-md focus:outline-none focus:ring-2 focus:ring-green-400">
                    Plan je gratis proefrijles
                </a>
            </div>
        </div>
    </div>
</section>
