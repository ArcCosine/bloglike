<?php

$pickuphtml = "";
$fp = @fopen("pickup.dat","r");
if( $fp ){
    $pickuphtml = file_get_contents("pickup.dat");
}else{
    $pickuphtml = "ファイルが存在しません";
}

$xhtml =<<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=Shift-JIS" />
	<title>PicUp</title>
</head>
<body>
$pickuphtml
</body>
</html>
EOT;

echo $xhtml;
?>
