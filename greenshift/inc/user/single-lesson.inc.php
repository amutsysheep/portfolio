<form id="trialForm" action="index.php?page=single-lesson-info-form" method="get" class="space-y-6 mt-6">
    <input type="hidden" name="page" value="single-lesson-info-form">
    <section class="relative bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">

            <!-- Booking Card -->
            <div class="relative mx-auto max-w-3xl">
                <div class="pointer-events-none absolute -inset-2 rounded-3xl border-2 border-green-500/60 -z-10"></div>

                <div class="rounded-3xl bg-white shadow-2xl ring-1 ring-green-100 p-6 sm:p-8">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-xl sm:text-2xl font-extrabold text-green-800">RIJLES PLANNEN</h3>
                            <div class="mt-1 h-0.5 w-12 bg-green-600"></div>
                        </div>
                    </div>

                    <p class="mt-3 text-green-700 text-sm">Reserveer snel en easy een dag en tijd <span class="ml-1">🎉</span></p>

                    <!-- Welke dag / Hoe laat -->
                    <div class="mt-6">
                        <label class="block text-xs font-semibold text-green-700 uppercase tracking-wider mb-2">Beschikbare dagen & tijden</label>
                        <div class="flex flex-wrap gap-2">
                            <div class="mt-6 flex flex-col gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-green-700 uppercase tracking-wider mb-2">Welke dag?</label>
                                    <div id="days" class="flex flex-wrap gap-2"></div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-green-700 uppercase tracking-wider mb-2">Hoe laat?</label>
                                    <div id="times" class="flex flex-wrap gap-2"></div>
                                </div>
                            </div>

                            <input type="hidden" name="selected_day" id="selected_day">
                            <input type="hidden" name="selected_time" id="selected_time">
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit"
                                class="inline-flex items-center justify-center px-5 py-2.5 rounded-md bg-green-600 hover:bg-green-700 text-white text-sm font-semibold shadow-md focus:outline-none focus:ring-2 focus:ring-green-400">
                            Plan afspraak
                        </button>
                    </div>

                    <div class="mt-6">
                        <label class="block text-xs font-semibold text-green-700 uppercase tracking-wider mb-2">Gegevens</label>
                        <p class="text-[12px] text-green-700">Sla je gegevens tussentijds op zodra je beschikbaarheid hebt opgeslagen.</p>
                    </div>
                </div>
            </div>
            <!-- ===== Waarom proefrijles (features) ===== -->
            <div class="mt-14">
                <p class="text-green-600 tracking-widest font-extrabold text-sm text-center">DAAROM EEN RIJLES <span class="ml-1">😊</span></p>
                <div class="mt-6 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">

                    <div class="flex gap-4">
                        <div class="flex-shrink-0 text-3xl">⏱️</div>
                        <div>
                            <h4 class="font-bold text-green-800">Rijles van 1 uur</h4>
                            <p class="text-sm text-green-700">Je rijles is een volwaardig rijles van 60 minuten. Gaat alles goed &amp; rijexamen lukt, dan krijg je 45 euro korting.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-shrink-0 text-3xl">🎁</div>
                        <div>
                            <h4 class="font-bold text-green-800">We halen je thuis op</h4>
                            <p class="text-sm text-green-700">Meer dan 250 plaatsen waar wij rijles geven &amp; altijd dichtbij. De instructeur haalt je op en brengt je terug.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-shrink-0 text-3xl">🏠</div>
                        <div>
                            <h4 class="font-bold text-green-800">Geen les te veel</h4>
                            <p class="text-sm text-green-700">In de proefrijles bekijkt de instructeur jouw instapniveau. Zo weet je precies hoeveel lessen je nodig hebt — niet meer dan nodig.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-shrink-0 text-3xl">⚖️</div>
                        <div>
                            <h4 class="font-bold text-green-800">Je verplicht je niet om door te gaan</h4>
                            <p class="text-sm text-green-700">Na de proefrijles bepaal je zelf of je doorgaat. Geen kleine lettertjes; gewoon eerlijk en transparant.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</form>



<script>
    let slots = {};
    let selectedDay = null;
    let selectedTime = null;

    // Fetch beschikbare tijden
    fetch('php/user/available-slots.php')
        .then(res => res.json())
        .then(data => {
            slots = data;
            renderDays();
        });

    // Dagen
    function renderDays() {
        const daysEl = document.getElementById('days');
        daysEl.innerHTML = '';
        Object.keys(slots).forEach(day => {
            const span = document.createElement('span');
            span.className = 'day select-none px-3 py-2 rounded-md border border-green-200 text-green-700 text-sm bg-green-50 cursor-pointer transition-all duration-150';
            span.textContent = day;
            span.dataset.day = day;

            span.addEventListener('click', () => {
                selectedDay = day;
                document.getElementById('selected_day').value = day;

                document.querySelectorAll('.day').forEach(d => {
                    d.classList.remove('bg-green-900', 'text-green-700');
                    d.classList.add('bg-green-50', 'text-green-700');
                });
                span.classList.remove('bg-green-50', 'text-green-700');
                span.classList.add('bg-green-900', 'text-green-700');

                renderTimes(day);
            });

            daysEl.appendChild(span);
        });
    }

    // Tijden
    function renderTimes(day) {
        const timesEl = document.getElementById('times');
        timesEl.innerHTML = '';
        selectedTime = null;
        document.getElementById('selected_time').value = '';

        slots[day].forEach(slot => {
            const start = new Date('1970-01-01T' + slot.hour_start).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            const end = new Date('1970-01-01T' + slot.hour_end).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

            const span = document.createElement('span');
            span.className = 'time select-none px-3 py-2 rounded-md border border-green-200 text-green-700 text-sm bg-green-50 cursor-pointer transition-all duration-150';
            span.textContent = `${start} - ${end}`;
            span.dataset.time = slot.hour_start + ' - ' + slot.hour_end;

            span.addEventListener('click', () => {
                selectedTime = span.dataset.time;
                document.getElementById('selected_time').value = selectedTime;

                document.querySelectorAll('.time').forEach(t => {
                    t.classList.remove('bg-green-900', 'text-green-700');
                    t.classList.add('bg-green-50', 'text-green-700');
                });
                span.classList.remove('bg-green-50', 'text-green-700');
                span.classList.add('bg-green-900', 'text-green-700');
            });

            timesEl.appendChild(span);
        });
    }
</script>
