<?php
if(($subnav=$nav->getSubMenu()) && is_array($subnav)){
    $activeMenu=$nav->getActiveMenu();
    if($activeMenu>0 && !isset($subnav[$activeMenu-1]))
        $activeMenu=0;
    foreach($subnav as $k=> $item) {
        if($item['droponly']) continue;
        $class=$item['iconclass'];
        if ($activeMenu && $k+1==$activeMenu
                or (!$activeMenu
                    && (strpos(strtoupper($item['href']),strtoupper(basename($_SERVER['SCRIPT_NAME']))) !== false
                        or ($item['urls']
                            && in_array(basename($_SERVER['SCRIPT_NAME']),$item['urls'])
                            )
                        )))
            $class="$class active";
        if (!($id=$item['id']))
            $id="subnav$k";

        echo sprintf('<li><a class="%s" href="%s" title="%s" id="%s" onclick="%s">%s</a></li>',
                $class, $item['href'], $item['title'], $id,$item['onclick'], $item['desc'] );
        // echo sprintf('<a  href="http://mailtest.spitzeco.dk/scp/downloadUserInfoCSV.php"  target="_blank" >download</a>');
    }
}
