<?php
/**
* @package module_generator
* @version Ver_1.0.0
* @copyright (c) 2012 Pertneer
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License
*/

$zipName = base64_decode($_GET['zip']);

header('Content-type: application/zip');
header('Content-Disposition: attachment; filename="'.$zipName.'"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: '.filesize($zipName) );
//read a file and send
readfile($zipName);

?>