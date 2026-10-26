<?php
session_start();

// Verwijder winkelmand bij uitloggen
unset($_SESSION['winkelmand']);  // Verwijder de winkelmand sessie

// Verwijder gebruikersspecifieke sessiegegevens
unset($_SESSION['Rol']);
unset($_SESSION['User_id']);  // Verwijder de User_id als de gebruiker uitlogt

// Redirect naar de homepagina of andere pagina
header('Location: ../index.php?page=home');
exit();  // Stop verder uitvoeren van de code na de redirect
?>

