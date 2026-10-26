<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* tijdelijke test login */
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 2;
}

/* DB connectie */
if (!isset($pdo)) {
    require_once __DIR__ . '/../Private/pas_connection.php';
}

/* profieladres ophalen */
$profielAdres = null;

$stmt = $pdo->prepare("
    SELECT a.straat, a.postcode, a.plaats, a.huisnummer
    FROM gebruikers g
    JOIN adressen a ON a.adress_id = g.adress_id
    WHERE g.user_id = :uid
    LIMIT 1
");
$stmt->execute(['uid' => $_SESSION['user_id']]);
$profielAdres = $stmt->fetch(PDO::FETCH_ASSOC);

/* feedback */
$success = $_SESSION['pakket_bevestiging'] ?? null;
$error   = $_SESSION['pakket_fout'] ?? null;

unset($_SESSION['pakket_bevestiging'], $_SESSION['pakket_fout']);
?>

<?php if ($success): ?>
    <div class="alert success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<section class="pakket-aanmelden-wrapper">
    <div class="pakket-aanmelden-card">
        <h2 class="pakket-aanmelden-titel">Pakket aanmelden</h2>

        <p class="pakket-aanmelden-subtitel">
            Vul de gegevens van je pakket in. De koerier haalt het pakket op bij het afhaaladres.
        </p>

        <form class="pakket-form"
              action="/pas-pakket-afhaal-service/PHP/nieuw_pakket_aanmelden.php"
              method="post">

            <!-- Afmetingen -->
            <fieldset class="pakket-fieldset">
                <legend>Afmetingen & gewicht</legend>

                <div class="grid-afmetingen">
                    <div class="form-group">
                        <label for="lengte">Lengte (cm)</label>
                        <input type="number" id="lengte" name="lengte" min="1" max="300" required>
                    </div>

                    <div class="form-group">
                        <label for="breedte">Breedte (cm)</label>
                        <input type="number" id="breedte" name="breedte" min="1" max="300" required>
                    </div>

                    <div class="form-group">
                        <label for="hoogte">Hoogte (cm)</label>
                        <input type="number" id="hoogte" name="hoogte" min="1" max="300" required>
                    </div>
                </div>

                <div class="form-group form-group-half">
                    <label for="gewicht">Gewicht (kg)</label>
                    <input type="number" id="gewicht" name="gewicht" min="0.1" step="0.1" required>
                </div>

                <div class="form-group">
                    <label for="omschrijving">Inhoud (omschrijving)</label>
                    <textarea id="omschrijving"
                              name="omschrijving"
                              rows="3"
                              placeholder="Bijv. documenten, elektronica, kleding"></textarea>
                </div>
            </fieldset>

            <!-- Afhaaladress -->
            <fieldset class="pakket-fieldset">
                <legend>Afhaaladres</legend>
                <p class="form-help">De koerier haalt het pakket op dit adres op.</p>

                <div class="form-group checkbox-inline">
                    <label>
                        <input type="checkbox"
                               id="use_profile_address"
                            <?= $profielAdres ? '' : 'disabled' ?>>
                        Gebruik mijn adres uit profiel
                    </label>

                    <?php if (!$profielAdres): ?>
                        <small class="form-help">
                            Geen profieladres gevonden. Zet in de database een adress_id bij deze gebruiker.
                        </small>
                    <?php endif; ?>
                </div>

                <div class="grid-adres">
                    <div class="form-group">
                        <label for="ophaal_postcode">Postcode</label>
                        <input type="text"
                               id="ophaal_postcode"
                               name="ophaal_postcode"
                               placeholder="1234AB"
                               maxlength="6"
                               pattern="^[1-9][0-9]{3}[A-Za-z]{2}$"
                               title="Postcode formaat: 1234AB"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="ophaal_huisnummer">Huisnummer</label>
                        <input type="text"
                               id="ophaal_huisnummer"
                               name="ophaal_huisnummer"
                               placeholder="bijv. 21A"
                               maxlength="10"
                               pattern="^[0-9]{1,5}([A-Za-z]{1,3}|[-\/ ][0-9A-Za-z]{1,5})?$"
                               title="Voorbeeld: 21, 21A, 12-3"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="ophaal_straat">Straat</label>
                        <input type="text" id="ophaal_straat" name="ophaal_straat" required>
                    </div>

                    <div class="form-group">
                        <label for="ophaal_plaats">Plaats</label>
                        <input type="text" id="ophaal_plaats" name="ophaal_plaats" required>
                    </div>
                </div>
            </fieldset>

            <!-- Bezorgadres -->
            <fieldset class="pakket-fieldset">
                <legend>Bezorgadres</legend>
                <p class="form-help">Het pakket wordt op dit adres afgeleverd.</p>

                <div class="grid-adres">
                    <div class="form-group">
                        <label for="aflever_postcode">Postcode</label>
                        <input type="text"
                               id="aflever_postcode"
                               name="aflever_postcode"
                               placeholder="1234AB"
                               maxlength="6"
                               pattern="^[1-9][0-9]{3}[A-Za-z]{2}$"
                               title="Postcode formaat: 1234AB"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="aflever_huisnummer">Huisnummer</label>
                        <input type="text"
                               id="aflever_huisnummer"
                               name="aflever_huisnummer"
                               placeholder="bijv. 21A"
                               maxlength="10"
                               pattern="^[0-9]{1,5}([A-Za-z]{1,3}|[-\/ ][0-9A-Za-z]{1,5})?$"
                               title="Voorbeeld: 21, 21A, 12-3"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="aflever_straat">Straat</label>
                        <input type="text" id="aflever_straat" name="aflever_straat" required>
                    </div>

                    <div class="form-group">
                        <label for="aflever_plaats">Plaats</label>
                        <input type="text" id="aflever_plaats" name="aflever_plaats" required>
                    </div>
                </div>
            </fieldset>

            <!-- Datums -->
            <fieldset class="pakket-fieldset">
                <legend>Planning</legend>

                <div class="grid-datums">
                    <div class="form-group">
                        <label for="ophaaldatum">Afhaaldatum</label>
                        <input type="date" id="ophaaldatum" name="ophaaldatum" required>
                        <small class="form-help">Minimaal morgen.</small>
                    </div>

                    <div class="form-group">
                        <label for="afleverdatum">Gewenste Afleverdatum</label>
                        <input type="date" id="afleverdatum" name="afleverdatum" required>
                        <small class="form-help">1 t/m 7 dagen na afhaaldatum.</small>
                    </div>
                </div>
            </fieldset>

            <div class="kosten-en-knop">
                <button type="submit" class="btn-primary">Pakket aanmelden</button>
            </div>
        </form>
    </div>
</section>

<script>
    (function () {
        const $ = (id) => document.getElementById(id);

        // Profiel adres vanuit PHP
        const profileAddress = <?= json_encode($profielAdres ?? null) ?>;

        // Postcode normaliseren
        function normalizePostcode(input) {
            if (!input) return;
            input.addEventListener('input', () => {
                input.value = input.value
                    .toUpperCase()
                    .replace(/\s+/g, '')
                    .replace(/[^0-9A-Z]/g, '')
                    .slice(0, 6);
            });
        }

        normalizePostcode($('ophaal_postcode'));
        normalizePostcode($('aflever_postcode'));

        // Checkbox: profieladres togglen (aan = vullen, uit = leeg)
        const checkboxProfile = $('use_profile_address');

        const ophaalFields = {
            postcode: $('ophaal_postcode'),
            huisnummer: $('ophaal_huisnummer'),
            straat: $('ophaal_straat'),
            plaats: $('ophaal_plaats')
        };

        function fillProfileAddress() {
            if (!profileAddress) return;
            ophaalFields.straat.value = profileAddress.straat ?? '';
            ophaalFields.postcode.value = (profileAddress.postcode ?? '').toUpperCase().replace(/\s+/g, '');
            ophaalFields.plaats.value = profileAddress.plaats ?? '';
            ophaalFields.huisnummer.value = profileAddress.huisnummer ?? '';
        }

        function clearOphaalAddress() {
            ophaalFields.postcode.value = '';
            ophaalFields.huisnummer.value = '';
            ophaalFields.straat.value = '';
            ophaalFields.plaats.value = '';
            ophaalFields.postcode.focus();
        }

        if (checkboxProfile) {
            checkboxProfile.addEventListener('change', () => {
                if (checkboxProfile.checked) {
                    fillProfileAddress();
                } else {
                    clearOphaalAddress();
                }
            });
        }

        // Datums: afhaal = minimaal morgen, aflever = 1-7 dagen daarna
        const ophaal = $('ophaaldatum');
        const aflever = $('afleverdatum');

        function toYMD(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`; // ✅ FIX
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);

        if (ophaal) ophaal.min = toYMD(tomorrow);

        if (ophaal && aflever) {
            ophaal.addEventListener('change', () => {
                if (!ophaal.value) return;

                const pick = new Date(ophaal.value);
                pick.setHours(0, 0, 0, 0);

                // block verleden + vandaag
                if (pick < tomorrow) {
                    ophaal.value = '';
                    aflever.value = '';
                    aflever.min = '';
                    aflever.max = '';
                    return;
                }

                const minAflever = new Date(pick);
                minAflever.setDate(minAflever.getDate() + 1);

                const maxAflever = new Date(pick);
                maxAflever.setDate(maxAflever.getDate() + 7);

                aflever.min = toYMD(minAflever);
                aflever.max = toYMD(maxAflever);

                if (aflever.value) {
                    const a = new Date(aflever.value);
                    a.setHours(0, 0, 0, 0);
                    if (a < minAflever || a > maxAflever) {
                        aflever.value = '';
                    }
                }
            });

            aflever.addEventListener('change', () => {
                if (!ophaal.value) {
                    aflever.value = '';
                    ophaal.focus();
                }
            });
        }
    })();
</script>
