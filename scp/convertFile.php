<?php

$here = dirname(__FILE__);
$here = ($h = realpath($here)) ? $h : $here;
define('ROOT_DIR',str_replace('\\', '/', $here.'/'));
unset($here); unset($h);

define('INCLUDE_DIR', ROOT_DIR . 'include/'); // Set by installer
try {
	echo INCLUDE_DIR.'/i18n/da.phar';
    $phar = new Phar(INCLUDE_DIR.'/i18n/myphar.phar');
    $phar->extractTo(INCLUDE_DIR.'/i18n/', 'lang.txt'); // extract only file.txt

} catch (Exception $e) {
   echo $e->getMessage();
?>