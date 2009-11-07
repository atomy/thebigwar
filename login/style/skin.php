<?php
    require_once( '../../include/config_inc.php' );
    require( TBW_ROOT.'engine/include.php' );

    header('Content-type: text/css; charset=ISO-8859-1');
    header('Cache-control: max-age=152800');
    header('Expires: '.strftime('%a, %d %b %Y %T %Z', time()+152800));

    if(!isset($_GET['skin']) || !isset($_GET['type'])) exit(1);

    $skins = get_skins();
    if(!isset($skins[$_GET['skin']]) || !isset($skins[$_GET['skin']][1][$_GET['type']])) exit(1);

    foreach($skins[$_GET['skin']][1][$_GET['type']][1] as $fname)
    {
        $fname = $_GET['skin'].'/'.str_replace('\\', '/', $fname);
        if(strstr($fname, '/../') || !is_file($fname)) continue;
        echo "/* ".$fname." */\n\n";
        readfile($fname);
        echo "\n\n\n";
    }
?>