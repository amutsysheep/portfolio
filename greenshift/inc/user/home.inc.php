<section class="relative overflow-hidden bg-white">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-16">
        <div class="grid lg:grid-cols-2 gap-8 items-center">

            <!-- Left: Hero copy -->
            <div class="text-green-800">
                <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight">
                    SNEL JE<br/> RIJBEWIJS!
                    <span class="inline-block align-middle text-3xl sm:text-4xl">👋</span>
                </h1>

                <p class="mt-6 text-lg text-green-700">
                    Start morgen, <br class="hidden sm:block">in heel Nederland
                </p>

                <a href="<?php echo generateUrl('trial-lesson'); ?>"
                   class="mt-6 inline-flex items-center justify-center px-6 py-3 rounded-md bg-green-600 hover:bg-green-700 text-white font-semibold shadow-lg focus:outline-none focus:ring-2 focus:ring-green-400">
                    Plan je gratis proefrijles
                </a>

                <!-- Benefits block -->
                <div class="mt-10">
                    <p class="text-green-500 tracking-widest font-extrabold text-sm">VASTE LAGE</p>
                    <h2 class="text-2xl sm:text-3xl font-extrabold -mt-1">LESPRIJS</h2>

                    <div class="mt-6 space-y-5 text-sm sm:text-base">
                        <div>
                            <h3 class="font-semibold">Sneller slagen met minder lessen</h3>
                            <p class="text-green-700">Meerdere lessen per week mogelijk. Daardoor leer je sneller autorijden.</p>
                        </div>
                        <div>
                            <h3 class="font-semibold">Start direct</h3>
                            <p class="text-green-700">Geen wachtlijsten — in de meeste plaatsen kun je meteen beginnen.</p>
                        </div>
                        <div>
                            <h3 class="font-semibold">Betaalgemak</h3>
                            <p class="text-green-700">Gespreid per maand of per les; kies wat voor jou het beste werkt.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Promo card -->
            <div class="lg:justify-self-end">
                <div class="relative">
                    <!-- sticker / badge -->
                    <div class="absolute -top-6 -left-4 z-10 text-2xl">🍩</div>

                    <div class="rounded-2xl bg-green-50 shadow-2xl ring-1 ring-green-100 p-6 sm:p-8 max-w-md">
                        <p class="text-green-600 font-bold text-sm">START MET RIJLESSEN</p>
                        <h3 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight text-green-800">
                            BACK TO SCHOOL<br/>ACTIE <span class="inline-block">🎒</span>
                        </h3>
                        <p class="mt-1 text-xs text-green-600">Aanmelden voor 29 september</p>

                        <div class="mt-5 flex items-end gap-3">
                            <div class="text-green-400 line-through text-sm">58,50</div>
                            <div class="text-4xl font-extrabold text-green-700 leading-none">51,50</div>
                            <div class="text-green-600 text-sm mb-1">per les</div>
                        </div>

                        <ul class="mt-5 space-y-2 text-sm text-green-700">
                            <li class="flex gap-2"><span>✅</span> €7 korting per les (handgeschakeld)</li>
                            <li class="flex gap-2"><span>✅</span> incl. gratis herexamens</li>
                            <li class="flex gap-2"><span>✅</span> gespreid betalen mogelijk</li>
                            <li class="flex gap-2"><span>✅</span> 100% flexibel, geen pakketkosten</li>
                        </ul>

                        <!-- Form -->
                        <form class="mt-6 space-y-3" action="<?php echo generateUrl('trial-lesson'); ?>" method="get">
                            <div class="grid grid-cols-3 gap-2">
                                <input type="text" name="postcode" placeholder="Postcode"
                                       class="col-span-1 w-full rounded-md border border-green-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400" required>
                                <input type="text" name="huisnr" placeholder="Huisnr."
                                       class="col-span-1 w-full rounded-md border border-green-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                                <input type="text" name="toevoeging" placeholder="Toevoeg."
                                       class="col-span-1 w-full rounded-md border border-green-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                            </div>

                            <button type="submit"
                                    class="w-full rounded-md bg-green-600 hover:bg-green-700 text-white font-semibold py-3 shadow-lg focus:outline-none focus:ring-2 focus:ring-green-400">
                                Toon beschikbaarheid
                            </button>

                            <p class="text-[11px] text-center text-green-600">
                                We hebben je postcode nodig om de agenda te laten zien.
                            </p>
                        </form>
                    </div>

                    <!-- card border accent -->
                    <div class="pointer-events-none absolute -inset-2 rounded-3xl border-2 border-green-500/60 -z-10"></div>
                </div>
            </div>

        </div>
    </div>
    <!-- ===== Info + Zigzag + Welcome + Free Trial ===== -->
    <section class="relative bg-white text-green-800">
        <!-- Decorative zig-zag / chevron -->
        <div class="relative hidden lg:block">
            <div class="absolute left-0 top-8 h-80 w-24 bg-gradient-to-b from-green-500 to-emerald-400 rounded-md transform -skew-x-12"></div>
            <div class="absolute left-16 top-24 h-80 w-24 bg-gradient-to-b from-emerald-400 to-green-500 rounded-md transform -skew-x-12 opacity-80"></div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="grid lg:grid-cols-2 gap-10 items-center">


                <!-- Right: small feature with checklist + CTA -->
                <div class="lg:col-start-2">
                    <div class="max-w-md ml-auto">
                        <div class="flex items-start gap-4">
                            <!-- phone/image placeholder -->
                            <img src="/img/app-mock.png" alt="" class="w-16 h-16 object-cover rounded-md shadow-sm border border-green-100">
                            <div>
                                <p class="text-xs uppercase tracking-widest text-green-500 font-bold">Superhandig,</p>
                                <h3 class="text-2xl font-extrabold -mt-1">en supersnel geregeld</h3>
                            </div>
                        </div>

                        <ul class="mt-4 space-y-2 text-sm text-green-700">
                            <li class="flex gap-2"><span>✅</span> Plan gemakkelijk zelf online</li>
                            <li class="flex gap-2"><span>✅</span> Je verplicht je niet om door te gaan</li>
                            <li class="flex gap-2"><span>✅</span> We halen je thuis op</li>
                            <li class="flex gap-2"><span>✅</span> Advies op maat, laten weten wat je kan</li>
                            <li class="flex gap-2"><span>✅</span> Gratis als je verder gaat en anders €0</li>
                        </ul>

                        <p class="mt-4 text-sm text-green-700">Vanaf morgen? Dat kan!</p>

                        <a href="<?php echo generateUrl('trial-lesson'); ?>"
                           class="mt-4 inline-flex items-center justify-center px-6 py-3 rounded-md bg-green-600 hover:bg-green-700 text-white font-semibold shadow-md focus:outline-none focus:ring-2 focus:ring-green-400">
                            Plan je gratis proefrijles
                        </a>
                    </div>
                </div>
            </div>

            <!-- Welcome block -->
            <div class="mt-16 max-w-3xl">
                <p class="text-green-500 tracking-widest font-extrabold text-sm">WELKOM BIJ ONZE RIJSCHOOL</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold -mt-1">RIJLES.NL</h2>

                <div class="mt-6 space-y-5 text-green-700 leading-relaxed">
                    <p>De klantvriendelijkste & grootste rijschool van Nederland!</p>
                    <p>Het behalen van je rijbewijs is een belangrijk moment in je leven. Bij Rijles.nl profiteer je van onze jarenlange ervaring.
                        Rijles.nl was de eerste die een proefrijles-aanbod bood bij de start van rijles. En daarnaast zijn we transparant als rijschool over onze gratis herexamens.
                        Als dat nodig is, bieden wij gratis herexamens aan en betaal jij alleen nog de rijles.</p>
                    <p>We hebben het halen van je rijbewijs altijd makkelijk gemaakt. Bij ons kun je je rijlessen in maximaal zeven maandelijkse termijnen betalen of nog flexibeler: per les.</p>
                    <p>Onze klantenservice zorgt ervoor dat we je helpen bij al je vragen. Bij Rijles.nl krijg je gegarandeerd goede rijlessen en een fijne leerervaring.</p>
                    <p>En wees gerust, wil jij je rijbewijs snel halen, misschien zelfs wel met spoed. Ook dat doen we graag goed. Na je proefrijles regelen wij meteen je rijexamen, zodat je sneller op examen kunt.
                        Begin vandaag nog en zet de eerste stap naar het behalen van je rijbewijs!</p>
                    <a href="<?php echo generateUrl('trial-lesson'); ?>" class="inline-block text-green-700 underline underline-offset-2 hover:text-green-800">Lees meer</a>
                </div>
            </div>

            <!-- Free trial box -->
            <div class="mt-16 grid lg:grid-cols-2 gap-10 items-start">
                <div class="max-w-md">
                    <p class="text-green-500 tracking-widest font-extrabold text-sm">DE PROEFRIJLES 💚</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold -mt-1">KRIJG JE VAN ONS</h3>

                    <ul class="mt-4 space-y-2 text-sm text-green-700">
                        <li class="flex gap-2"><span>✅</span> Plan gemakkelijk zelf online</li>
                        <li class="flex gap-2"><span>✅</span> Je verplicht je niet om door te gaan</li>
                        <li class="flex gap-2"><span>✅</span> We halen je thuis op</li>
                        <li class="flex gap-2"><span>✅</span> Advies op maat, laten weten wat je kan</li>
                        <li class="flex gap-2"><span>✅</span> Gratis als je verder gaat en anders €0</li>
                    </ul>

                    <p class="mt-4 text-sm text-green-700">Vanaf morgen? Dat kan!</p>

                    <a href="<?php echo generateUrl('trial-lesson'); ?>"
                       class="mt-4 inline-flex items-center justify-center px-6 py-3 rounded-md bg-green-600 hover:bg-green-700 text-white font-semibold shadow-md focus:outline-none focus:ring-2 focus:ring-green-400">
                        Plan je gratis proefrijles
                    </a>
                </div>
            </div>
        </div>
    </section>

</section>

