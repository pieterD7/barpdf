<?php 
/**
 * Creates an html file with a range (+1) of unique using only digits barcodes on labels.
 * The number to start with is read and saved in config.ini
 * The margins around the labels should be done making the pdf.
 * 
 * Todo: multiple page pdf
 */


// DEBUG ON
error_reporting(E_ALL);
ini_set("display_errors", true);

// File to use as ini file
$configFile = "config.ini";

// Width of the paper in mm
$width = 210;

// Height of the paper in mm
$height = 297;

// Number of rows
$rows = 13;

// Number of columns
$columns = 5;

// Number to start the barcode range with
$barcodeNumber = false;


/**
 * Returns a fixed size label in html using relative positioning and floating divs
 */
function addLabel()
{
	global 	$columns, $rows;
	
	$c = 100 / $columns ;
	$r = 100 / $rows;
	
	$style = array(
			"float:left",
			"overflow:hidden",
			"min-width:$c"."%",
			"min-height:$r"."%",
			"width:$c"."%",
			"height:$r"."%"
	);
	
	$style = implode(";", $style);
	
	return "\t<div class='label' style='$style'>\n" . addBarcode() . "\n\t</div>\n";
}

/**
 * Emits the html page
 */
function pageOut()
{
	global 	$columns, $rows, $width, $height;
	
	$style = array(
		"width:$width"."mm",
		"height:$height"."mm"	
			
	);
	
	$style = implode(";", $style);
	
	echo "<div  class='layout' style='$style'>\n";
	
	for ($n = 0; $n < $columns * $rows; $n++)
	{
		echo addLabel();
	}
	
	echo "\n</div>";

}

/**
 * Adds a barcode using barcode.php
 * https://github.com/davidscotttufts/php-barcode/blob/master/barcode.php
 */
function addBarcode()
{
	global $barcodeNumber;
	
	$args = array(
		"size=30",
		"code_type=CODE_128",
		"text=$barcodeNumber"
	);
	
	$args = implode("&", $args);
	
	$html = "		<img src='barcode.php?$args'/>
		<p class='barcode-text'>$barcodeNumber</p>
		<p class='barcode-text-extra'></p>";
	
	$barcodeNumber++;
	
	return $html;
	
}

/**
 * Allows a GET request to override some globals
 */
function parseRequestPayload()
{
	// Allow override these globals
	global 	/*$barcodeNumber,*/ 
				$columns, $rows, $width, $height, 
				$marginLeft, $marginRight, $marginTop, $marginBottom;
	
	// Parse request payload
	foreach ($_REQUEST as $k => $v)
		if(!empty($v))
			$$k = $v;
}

/**
 * Writes an ini file based on the contents of the file and key/values in $arr
 * Comments and grouping of variables is lost.
 */
function writeIniFile($arr)
{
	global $configFile;
	
	$cfg = parse_ini_file( $configFile );
	
	// Replace values
	foreach ($arr as $k => $v)
	{
		$cfg[$k] = $v;
	}
	
	// Write updated file
	$outStr = "";
	foreach ($cfg as $k => $v)
	{
		$outStr .= $k . "=" . $v . "\n";
	}
	
	file_put_contents($configFile, $outStr);
	
	
}

/**
 * Does initial stuff
 */
function init()
{
	global 	$configFile, $barcodeNumber;
	
	// Parse ini file	
	$cfg = parse_ini_file( $configFile );
	
	if(empty($cfg))
		die("Could not read ini file `" . $configFile . "`.");
	
	$barcodeNumber = $cfg['startWith'];	
	
	// Parse GET request 
	parseRequestPayload();	

	if(!is_numeric($barcodeNumber))
		die("Could not read number to start with.");
	
}

/**
 * Closes the program updating the ini file
 */
function close()
{
	global $barcodeNumber;
	
	writeIniFile( array("startWith" => $barcodeNumber ));
}

/**
 * Echo the value of $_GET[$var], else $val
 */
function echoDefault($var, $val)
{
	if(!empty($_GET[$var]))
		echo $_GET[$var];
	else echo $val;
}
?>
<!doctype html">
<html xmlns="http://www.w3.org/1999/xhtml">
<link href='styles.css' type='text/css' rel='stylesheet'/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script type='text/javascript'>

var clipX = 0, 
    clipY = 0,
    clipping = false,
    errorString = " => "

function toggleDetectClipping()
{
	if(clipping)
	{
		clipping = false
		$(".label").css("border-color", "white")
	}
	else
	{
		$("#clipping").html("")
		detectClipping()
	}
}

function detectClipping()
{
	var s =  $(".label")
		
	var lblW = s.prop('scrollWidth'),
	    lblH = s.prop('scrollHeight')
		
	if(lblW != s.width())
	{
		clipping = true
		clipX = lblW - s.width()
		s.css({"border-right-color": "red"})
	}
	else
	{
		s.css({"border-right-color": "white"})
	}

	if(lblH != s.height())
	{
		clipping = true
		clipY = lblH - s.height()
		s.css({"border-bottom-color": "red"})
	
	}
	else
	{
		s.css({"border-bottom-color": "white"})
	}

	if(clipping)
		$("#clipping").html(errorString + "(" + clipX + "px," + clipY + "px)")
}



</script>
<body>
<div class='noprint'>
	<p><br/>Marge rondom: 10 mm. Op snijlijn: 2 mm beide kanten. <a href='javascript:toggleDetectClipping()'>Detecteer clipping <span id='clipping'></span></a></p>
	<form action='?'>
	Rijen : <input type='number' name='rows' value='<?php echoDefault('rows', 13);?>'> 
	Kolommen: <input type='number' name='columns' value='<?php echoDefault('columns', 5);?>'><br/>
	Papier: <input type='number' name='width' value='<?php echoDefault('width', 210);?>'> x 
	<input type='number' name='height' value='<?php echoDefault('height', 297);?>'>mm
	<input type="submit" value='Ok'>
	</form>
</div>
<?php 

// MAIN
init();

pageOut();

close();

?>
<div class='noprint'>
	Generated by PHP, v<?php echo phpversion();?>
</div>
</body>
</html>