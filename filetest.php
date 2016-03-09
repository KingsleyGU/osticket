<?php
require('client.inc.php');
require_once(INCLUDE_DIR.'mpdf/mpdf.php');
$pdf = new mPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);



$file = file_get_contents("http://mailtest.spitzeco.dk/osticket/file.php?key=p0u5jcnzlzq134cjew27utvxohxe1n94&expires=1457654400&signature=15aa64ad32f4ac088eba051a169598dea5a73b3e");
$pdf ->WriteHTML($file,4);

$content = $pdf->Output('doc.pdf','F');

?>