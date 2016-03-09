<?php
$image = new ImageMagick();

if($f=fopen("http://mailtest.spitzeco.dk/osticketLocal/file.php?key=p0u5jcnzlzq134cjew27utvxohxe1n94&expires=1457654400&signature=7ea5d189d83a8cb87a8d34894b004ee395154891", "w")){ 
  $image->writeImageFile($f);
}
echo $image;

?>