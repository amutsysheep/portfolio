<?php

function ShowMessage(string $type, string $message): void
{
    $_SESSION['svgMelding'] = [
        'type' => $type,
        'message' => $message
    ];
}

function DisplayMessage(): void
{
    if (!isset($_SESSION['svgMelding'])) {
        return;
    }

    $melding = $_SESSION['svgMelding'];

    // Melding direct verwijderen zodat deze maar één keer wordt getoond
    unset($_SESSION['svgMelding']);

    $message = htmlspecialchars($melding['message']);

    if ($melding['type'] === 'success') {
        echo '<div class="register-success">';
        echo $message;
        echo '</div>';
    } elseif ($melding['type'] === 'error') {
        echo '<div class="register-error">';
        echo $message;
        echo '</div>';
    }
}