<?
header("Content-type:image/jpeg");
$font = "Arial_black.ttf";
session_start();
putenv('GDFONTPATH=' . realpath('.'));
$_SESSION['captcha'] = substr(md5(microtime().uniqid()), 0, 4);
$text = "";
for ($i=0; $i<4; $i++)
{
	$text .= rand(0,9);
}
$_SESSION['captcha'] = $text;
$string = $_SESSION['captcha'];
$array = Array();
for ($i = 0; $i < strlen($string); $i++) {
	$array[] = substr(((rand(-10,10) > 0)? $string : strtoupper($string)), $i, 1);
}
$image = imagecreatetruecolor(100, 40);
$color = imagecolorallocate($image,255,255,255);
imagefill($image, 0, 0, $color);
$x = 10;
$y = 30;
foreach ($array as $index => $value) {
	$size = rand(15,25);
	$color = imagecolorallocate($image, rand(10,200), rand(10,200), rand(10,200));
	$color = imagecolorallocate($image, 118, 185, 0);
	$ang = rand(30,-30);
	$ang = rand(0,0);
	$w = imagettfbbox($size, $ang, $font, $value);
	imagettftext($image, $size, $ang, $x, $y, $color, $font, $value);
	$x = $x+($w['2']-$w['0']);
}
createnoise($image);
imageJPEG($image);

function createnoise($image)
 {
	for ($i = 1; $i < 100; $i++) {
		if ($i > 5) {
			$color = imagecolorallocate($image, rand(0,255), rand(0,255), rand(0,255));
			for ($j = 0; $j < rand(3,7); $j++) {
				$x = rand(0,imagesx($image));
				$y = rand(0,imagesy($image));
				if (rand(10,-10) > 5) $x = $x + 1;
				if (rand(10,-10) < -5) $y = $y - 1;
				imagesetpixel($image, $x,$y, $color);
		}
	} else {
			$x1 = rand(0,imagesx($image));
			$x2 = rand(0,imagesx($image));
			$y1 = rand(0,imagesy($image));
			$y2 = rand(0,imagesy($image));
			$color = imagecolorallocate($image, rand(0,255), rand(0,255), rand(0,255));
			imageline($image, $x1,$y1, $x2, $y2, $color);
		}
	}
}
?>