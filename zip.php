<?php
$zipName = base64_decode($_GET['zip']);

header('Content-type: application/zip');
header('Content-Disposition: attachment; filename="'.$zipName.'"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: '.filesize($zipName) );
//read a file and send
readfile($zipName);

?>