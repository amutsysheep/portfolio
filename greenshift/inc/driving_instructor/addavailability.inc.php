
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'groen': '#10b981',
                        'groen-donker': '#059669',
                        'groen-licht': '#a7f3d0',
                    }
                }
            }
        }
    </script>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <header class="text-center mb-10">
            <h1 class="text-3xl md:text-4xl font-bold text-groen-donker mb-2">Beschikbaarheid Planner</h1>
            <p class="text-gray-600">Selecteer een datum en kies uw beschikbare tijdblokken</p>
        </header>

        <!-- Hoofdcontainer -->
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md overflow-hidden">
            <div class="md:flex">
                <!-- Datum selectie sectie -->
                <div class="md:w-1/3 bg-groen p-6 text-white">
                    <h2 class="text-xl font-bold mb-4">Selecteer Datum</h2>
                    
                    <div class="mb-6">
                        <label for="datePicker" class="block mb-2 font-medium">Kies een datum:</label>
                        <input type="date" id="datePicker" class="w-full p-2 rounded text-gray-800">
                    </div>
                    
                    <div class="mt-6">
                        <h3 class="text-lg font-bold mb-2">Geselecteerde datum:</h3>
                        <div id="selectedDate" class="text-xl font-bold bg-white text-groen-donker p-3 rounded-lg text-center"></div>
                    </div>
                    
                    <div class="mt-6">
                        <button id="resetBtn" class="w-full bg-white text-groen-donker font-bold py-2 px-4 rounded-lg hover:bg-gray-100 transition">
                            <i class="fas fa-redo mr-2"></i>Reset Selectie
                        </button>
                    </div>
                </div>
                
                <!-- Tijdblokken sectie -->
                <div class="md:w-2/3 p-6">
                    <h2 class="text-xl font-bold text-groen-donker mb-4">Beschikbare Tijdblokken</h2>
                    <p class="text-gray-600 mb-6">Klik op de tijdblokken om ze te selecteren/deselecteren</p>
                    
                    <div id="timeSlots" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <!-- Tijdblokken worden hier gegenereerd -->
                    </div>
                    
                    <div class="mt-8 p-4 bg-groen-licht rounded-lg">
                        <h3 class="font-bold text-groen-donker mb-2">Uw selectie:</h3>
                        <p id="selectionSummary" class="text-gray-700">Nog geen tijdblokken geselecteerd</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Instructies -->
        <div class="max-w-4xl mx-auto mt-8 bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-groen-donker mb-4">Hoe werkt het?</h2>
            <ul class="list-disc pl-5 text-gray-700 space-y-2">
                <li>Selecteer eerst een datum in de linker sectie</li>
                <li>Klik vervolgens op de gewenste tijdblokken om ze te selecteren</li>
                <li>Geselecteerde tijdblokken worden groen gemarkeerd</li>
                <li>Klik nogmaals op een tijdblok om de selectie ongedaan te maken</li>
                <li>Gebruik de reset knop om alle selecties te wissen</li>
            </ul>
        </div>
    </div>

    <script>
        // Tijdblokken array
        const timeSlots = [
            '08:00-09:00',
            '09:00-10:00',
            '10:00-11:00',
            '11:00-12:00',
            '12:00-13:00',
            '13:00-14:00',
            '14:00-15:00',
            '15:00-16:00',
            '16:00-17:00',
            '17:00-18:00',
            '18:00-19:00',
            '19:00-20:00'
        ];

        // Elementen
        const datePicker = document.getElementById('datePicker');
        const selectedDate = document.getElementById('selectedDate');
        const timeSlotsContainer = document.getElementById('timeSlots');
        const selectionSummary = document.getElementById('selectionSummary');
        const resetBtn = document.getElementById('resetBtn');

        // Geselecteerde tijdblokken
        let selectedSlots = [];

        // Initialisatie
        document.addEventListener('DOMContentLoaded', function() {
            // Stel vandaag in als standaard datum
            const today = new Date();
            const formattedDate = today.toISOString().split('T')[0];
            datePicker.value = formattedDate;
            updateSelectedDate(today);
            
            // Genereer tijdblokken
            generateTimeSlots();
            
            // Voeg event listeners toe
            datePicker.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                updateSelectedDate(selectedDate);
                resetSelection();
            });
            
            resetBtn.addEventListener('click', resetSelection);
        });

        // Functie om geselecteerde datum bij te werken
        function updateSelectedDate(date) {
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            selectedDate.textContent = date.toLocaleDateString('nl-NL', options);
        }

        // Functie om tijdblokken te genereren
        function generateTimeSlots() {
            timeSlotsContainer.innerHTML = '';
            
            timeSlots.forEach(slot => {
                const slotElement = document.createElement('div');
                slotElement.className = 'time-slot p-3 border border-gray-300 rounded-lg text-center cursor-pointer transition-all hover:shadow-md';
                slotElement.textContent = slot;
                slotElement.dataset.slot = slot;
                
                slotElement.addEventListener('click', function() {
                    toggleTimeSlot(this);
                });
                
                timeSlotsContainer.appendChild(slotElement);
            });
        }

        // Functie om tijdblok te selecteren/deselecteren
        function toggleTimeSlot(element) {
            const slot = element.dataset.slot;
            
            if (selectedSlots.includes(slot)) {
                // Deselecteer
                selectedSlots = selectedSlots.filter(s => s !== slot);
                element.classList.remove('bg-groen', 'text-white');
                element.classList.add('bg-white', 'text-gray-800');
            } else {
                // Selecteer
                selectedSlots.push(slot);
                element.classList.remove('bg-white', 'text-gray-800');
                element.classList.add('bg-groen', 'text-white');
            }
            
            updateSelectionSummary();
        }

        // Functie om selectie samenvatting bij te werken
        function updateSelectionSummary() {
            if (selectedSlots.length === 0) {
                selectionSummary.textContent = 'Nog geen tijdblokken geselecteerd';
            } else {
                selectionSummary.textContent = `Geselecteerd: ${selectedSlots.join(', ')}`;
            }
        }

        // Functie om selectie te resetten
        function resetSelection() {
            selectedSlots = [];
            
            // Reset alle tijdblokken
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.remove('bg-groen', 'text-white');
                slot.classList.add('bg-white', 'text-gray-800');
            });
            
            updateSelectionSummary();
        }
    </script>
</body>
