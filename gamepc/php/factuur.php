<?php
require_once('../libs/fpdf.php');



$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);  // Set the font for the PDF

$pdf->Cell(200, 10, 'Factuur', 0, 1, 'C');  // Add the title
$pdf->Ln(10);  // Line break

// Add invoice details
$pdf->Cell(100, 10, 'Naam: John Doe');
$pdf->Ln();
$pdf->Cell(100, 10, 'Factuurnummer: 12345');
$pdf->Ln();
$pdf->Cell(100, 10, 'Datum: 15-Jan-2025');
$pdf->Ln();

// Add some line breaks and more content (e.g., items, totals)
$pdf->Ln(10);
$pdf->Cell(100, 10, 'Product 1: $50');
$pdf->Ln();
$pdf->Cell(100, 10, 'Product 2: $30');
$pdf->Ln();

// Total
$pdf->Cell(100, 10, 'Totaal: $80');

$pdf->Output();  // Display the PDF in the browser
// Or save it to a file:
// $pdf->Output('F', 'factuur_12345.pdf');
