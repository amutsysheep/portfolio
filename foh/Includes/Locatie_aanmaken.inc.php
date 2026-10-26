<?php
require_once 'PHP/ShowMessage.php';
?>

<div class="beheer-container">

    <div class="beheer-header formulier-header">
        <h2>Locatie aanmaken</h2>
    </div>

    <?php DisplayMessage(); ?>

    <form
        action="PHP/Locatie_aanmaken.php"
        method="POST"
        class="beheer-form"
    >
        <div class="form-group">
            <label for="Naam">Naam</label>
            <input
                type="text"
                id="Naam"
                name="Naam"
                required
            >
        </div>
        <div class="form-group">
            <label for="Zaal">Zaal</label>
            <input
                type="text"
                id="Zaal"
                name="Zaal"
            >
        </div>
        <div class="form-group">
            <label for="Plaats">Plaats</label>
            <input
                type="text"
                id="Plaats"
                name="Plaats"
                required
            >
        </div>
        <div class="form-group">
            <label for="Straatnaam">Straatnaam</label>
            <input
                type="text"
                id="Straatnaam"
                name="Straatnaam"
            >
        </div>
        <div class="form-group">
            <label for="Huisnummer">Huisnummer</label>
            <input
                type="text"
                id="Huisnummer"
                name="Huisnummer"
            >
        </div>
        <div class="form-group">
            <label for="Postcode">Postcode</label>
            <input
                type="text"
                id="Postcode"
                name="Postcode"
                maxlength="6"
            >
        </div>
        <div class="form-actions">

            <button
                type="submit"
                class="button-primary"
            >
                Locatie aanmaken
            </button>

            <a
                href="index.php?page=Locatie_overzicht"
                class="button-primary"
            >
                Annuleren
            </a>
        </div>
    </form>
</div>