<?php
class CookieManager {
    private const COOKIE_CONSENT  = 'cookie_consent';
    private const COOKIE_LIFETIME = 2592000; // 30 dagen

    public static function init() {
        if (!isset($_COOKIE[self::COOKIE_CONSENT])) {
            self::showCookieBanner();
        }
    }

    public static function setConsent($necessary = true, $analytics = false, $marketing = false) {
        $consent = [
                'necessary' => (bool)$necessary,
                'analytics' => (bool)$analytics,
                'marketing' => (bool)$marketing,
                'timestamp' => time(),
        ];

        setcookie(self::COOKIE_CONSENT, json_encode($consent), [
                'expires'  => time() + self::COOKIE_LIFETIME,
                'path'     => '/',
                'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Strict',
        ]);
    }

    public static function showCookieBanner() {
        ?>
        <!-- Cookie Banner HTML en JS -->
        <div id="cookie-banner" class="fixed bottom-0 left-0 right-0 bg-white shadow-lg p-4 z-50">
            <div class="max-w-6xl mx-auto">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold mb-2">Wij gebruiken cookies</h3>
                        <p class="text-gray-600">
                            Wij gebruiken cookies om uw ervaring te verbeteren en het verkeer te analyseren.
                            Door op "Alles accepteren" te klikken, gaat u akkoord met ons gebruik van cookies.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button onclick="showCookieSettings()"
                                class="px-4 py-2 border border-green-600 text-green-600 rounded hover:bg-green-50 transition-colors">
                            Cookie-instellingen
                        </button>
                        <button onclick="acceptAllCookies()"
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                            Alles accepteren
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="cookie-settings" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
            <div class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg p-6 max-w-lg w-full">
                <h2 class="text-xl font-semibold mb-4">Cookie-instellingen</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium">Noodzakelijke cookies</h3>
                            <p class="text-sm text-gray-500">Vereist voor de werking van de website</p>
                        </div>
                        <input type="checkbox" checked disabled class="h-5 w-5 rounded border-gray-300">
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium">Analyse cookies</h3>
                            <p class="text-sm text-gray-500">Helpen ons de website te verbeteren</p>
                        </div>
                        <input type="checkbox" id="analytics-cookies" class="h-5 w-5 rounded border-gray-300">
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium">Marketing cookies</h3>
                            <p class="text-sm text-gray-500">Gebruikt voor gepersonaliseerde advertenties</p>
                        </div>
                        <input type="checkbox" id="marketing-cookies" class="h-5 w-5 rounded border-gray-300">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button onclick="closeCookieSettings()"
                            class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                        Annuleren
                    </button>
                    <button onclick="saveCookieSettings()"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                        Instellingen opslaan
                    </button>
                </div>
            </div>
        </div>

        <script>
            function showCookieSettings() {
                document.getElementById('cookie-settings').classList.remove('hidden');
            }
            function closeCookieSettings() {
                document.getElementById('cookie-settings').classList.add('hidden');
            }
            function acceptAllCookies() {
                saveCookiePreferences(true, true, true);
            }
            function saveCookieSettings() {
                const analytics = document.getElementById('analytics-cookies').checked;
                const marketing = document.getElementById('marketing-cookies').checked;
                saveCookiePreferences(true, analytics, marketing);
            }
            function saveCookiePreferences(necessary, analytics, marketing) {
                fetch(location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ necessary, analytics, marketing })
                }).then(res => {
                    if (res.ok) window.location.reload();
                });
            }
        </script>
        <?php
    }
}

// --- Verwerk AJAX POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ct = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
    $isJson = stripos($ct, 'application/json') !== false;

    if (!$isJson) {
        header('Content-Type: application/json', true, 415);
        echo json_encode(['error' => 'Ongeldig type. Verwacht application/json']);
        exit;
    }

    header('Content-Type: application/json');

    $input = file_get_contents('php://input');
    $data  = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Ongeldige JSON invoer.']);
        exit;
    }

    $necessary = isset($data['necessary']) ? (bool)$data['necessary'] : true;
    $analytics = isset($data['analytics']) ? (bool)$data['analytics'] : false;
    $marketing = isset($data['marketing']) ? (bool)$data['marketing'] : false;

    CookieManager::setConsent($necessary, $analytics, $marketing);

    echo json_encode(['success' => true]);
    exit;
}

// --- Normale flow: toon banner als geen toestemming is gegeven ---
CookieManager::init();
?>
