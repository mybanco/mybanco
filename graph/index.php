<?php
//////////////////////////////////////////////////////////////////////
// PHP script made by Rasmus Beyer Petersen - http://www.peters1.dk //
//////////////////////////////////////////////////////////////////////

$show_label = true; // true = show label, false = don't show label.
$show_percent = true; // true = show percentage, false = don't show percentage.
$show_text = true; // true = show text, false = don't show text.
$show_parts = false; // true = show parts, false = don't show parts.
$label_form = 'round'; // 'square' or 'round' label.
$width = 120;
$background_color = 'FFFFFF'; // background-color of the chart...
$text_color = '000000'; // text-color.
$colors = array('003366', 'CCD6E0', '7F99B2','F7EFC6', 'C6BE8C', 'CC6600','990000','520000','BFBFC1','808080'); // colors of the slices.
$shadow_height = 10; // Height on shadown.
$shadow_dark = false; // true = darker shadow, false = lighter shadow...

// DON'T CHANGE ANYTHING BELOW THIS LINE...

$data = $_GET["data"];
$label = $_GET["label"];

if ($_GET["user"] == "1") {
	$data = '78413*58465';
	$label= 'Disk Used*Disk Free';
} elseif ($_GET["user"] == "2") {
	$data = '8743*5846';
	$label= 'Disk Used*Disk Free';
} else {
	$data = '1*1';
	$label= 'User not found*User not found';
}

$height = $width/2;
$data = explode('*',$data);

if ($label != '') $label = explode('*',$label);

for ($i = 0; $i < count($label); $i++) 
{
	if ($data[$i]/array_sum($data) < 0.1) $number[$i] =
		' '.number_format(($data[$i]/array_sum($data))*100,1,',','.').'%';
	else $number[$i] = number_format(($data[$i]/array_sum($data))*100,1,',','.').'%';
	if (strlen($label[$i]) > $text_length) $text_length = strlen($label[$i]);
}

if (is_array($label))
{
	$antal_label = count($label);
	$xtra = (5+15*$antal_label)-($height+ceil($shadow_height));
	if ($xtra > 0) $xtra_height = (5+15*$antal_label)-($height+ceil($shadow_height));
	
	$xtra_width = 5;
	if ($show_label) $xtra_width += 0;
	if ($show_percent) $xtra_width += 40;
	if ($show_text) $xtra_width += $text_length*8;
	if ($show_parts) $xtra_width += 35;
}

//$img = ImageCreateTrueColor($width+$xtra_width, $height+ceil($shadow_height)+$xtra_height);
$img = ImageCreateTrueColor($width+$xtra_width, $height+ceil($shadow_height)+$xtra_height);
imagealphablending($img, true);

ImageFill($img, 0, 0, colorHex($img, $background_color));
//Imagecolortransparent($img, colorHex($img, $background_color));

foreach ($colors as $colorkode) 
{
	$fill_color[] = colorHex($img, $colorkode);
	$shadow_color[] = colorHexshadow($img, $colorkode, $shadow_dark);
}

$label_place = 5;

if (is_array($label))
{
for ($i = 0; $i < count($label); $i++) 
{
	if ($label_form == 'round' && $show_label)
	{
		imagefilledellipse($img,$width+11,$label_place+5,10,10,colorHex($img, $colors[$i % count($colors)]));
		imageellipse($img,$width+11,$label_place+5,10,10,colorHex($img, $text_color));
	}
	else if ($label_form == 'square' && $show_label)
	{
		imagefilledrectangle($img,$width+6,$label_place,$width+16,$label_place+10,colorHex($img, $colors[$i % count($colors)]));
		imagerectangle($img,$width+6,$label_place,$width+16,$label_place+10,colorHex($img, $text_color));
	}

	if ($show_percent) $label_output = $number[$i].' ';
	if ($show_text) $label_output = $label_output.$label[$i].' ';
	if ($show_parts) $label_output = $label_output.$data[$i];

	//imagestring($img,'2',$width+20,$label_place,$label_output,colorHex($img, $text_color));
	$font = '/usr/share/fonts/truetype/ttf-dejavu/DejaVuSans.ttf';
	imagettftext($img,8,0,$width+20,$label_place+9,colorHex($img, $text_color),$font,$label_output);
	$label_output = '';

	$label_place = $label_place + 15;
}
}
$centerX = round($width/2);
$centerY = round($height/2);
$diameterX = $width-4;
$diameterY = $height-4;

$data_sum = array_sum($data);

$start = 270;

for ($i = 0; $i < count($data); $i++) 
{
	$value += $data[$i];
	$end = ceil(($value/$data_sum)*360) + 270;
	$slice[] = array($start, $end, $shadow_color[$value_counter % count($shadow_color)], $fill_color[$value_counter % count($fill_color)]);
	$start = $end;
	$value_counter++;
}

for ($i=$centerY+$shadow_height; $i>$centerY; $i--) 
{
	for ($j = 0; $j < count($slice); $j++)
	{
		ImageFilledArc($img, $centerX, $i, $diameterX, $diameterY, $slice[$j][0], $slice[$j][1], $slice[$j][2], IMG_ARC_PIE);
	}
}

for ($j = 0; $j < count($slice); $j++)
{
	ImageFilledArc($img, $centerX, $centerY, $diameterX, $diameterY, $slice[$j][0], $slice[$j][1], $slice[$j][3], IMG_ARC_PIE);
}

OutputImage($img);
ImageDestroy($img);

function colorHex($img, $HexColorString) 
{
	$R = hexdec(substr($HexColorString, 0, 2));
	$G = hexdec(substr($HexColorString, 2, 2));
	$B = hexdec(substr($HexColorString, 4, 2));
	return ImageColorAllocate($img, $R, $G, $B);
}

function colorHexshadow($img, $HexColorString, $mork) 
{
	$R = hexdec(substr($HexColorString, 0, 2));
	$G = hexdec(substr($HexColorString, 2, 2));
	$B = hexdec(substr($HexColorString, 4, 2));

	if ($mork)
	{
		($R > 99) ? $R -= 100 : $R = 0;
		($G > 99) ? $G -= 100 : $G = 0;
		($B > 99) ? $B -= 100 : $B = 0;
	}
	else
	{
		($R < 220) ? $R += 35 : $R = 255;
		($G < 220) ? $G += 35 : $G = 255;
		($B < 220) ? $B += 35 : $B = 255;
	}

	return ImageColorAllocate($img, $R, $G, $B);
}

function OutputImage($img) 
{
	header('Content-type: image/png');
	ImagePNG($img);
}

?> 
