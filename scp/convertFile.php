<?php
try {
	echo INCLUDE_DIR.'/i18n/myphar.phar';
    $phar = new Phar(INCLUDE_DIR.'/i18n/myphar.phar');
    $phar->extractTo(INCLUDE_DIR.'/i18n/', 'lang.txt'); // extract only file.txt

} catch (Exception $e) {
   echo "unsuccessful";
}
?>