<?php 
// Path to html2pdf binary, adding backslash for readability
$html2pdf = '"C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe"';

// Margins around paper and their defaults in mm
$marginLeft = 10.0;

$marginRight = 10.0;

$marginTop = 10.0;

$marginBottom = 10.0;

function callback($buffer)
{
	global 	$fileName,
				$width, $height,
				$html2pdf;
	
	//header('Content-type: application/pdf');
	//header('Content-Disposition: inline; filename="'. $fileName . '.pdf"');
	
	$tmpPdf = tempnam("./tmp", "htm");
	unlink($tmpPdf);
	$tmpPdf = explode(".", $tmpPdf);
	
	$tmpHtml = $tmpPdf[0] . ".html";
	
	file_put_contents($tmpHtml, $buffer);

	$tmpPdf = tempnam("./tmp", "pdf");
	unlink($tmpPdf);
	$tmpPdf = explode(".", $tmpPdf);
	
	$tmpPdf = $tmpPdf[0] . ".pdf";
	
	"--page-width=210 --page-height=297";
	$cmd = $html2pdf . "  " .   $tmpHtml . " " .  $tmpPdf;
	
	`$cmd`;

	$out = file_get_contents($tmpPdf);
	
	unlink($tmpHtml);
	unlink($tmpPdf);
	
	//return $out; 		
	return $buffer;
}

ob_start("callback");

include 'html.php';

$fileName = ($barcodeNumber - $rows * $columns) . "-" . ($barcodeNumber - 1);

ob_end_flush();

?>