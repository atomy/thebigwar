<?php
    require('include.php');
?>
<?='<?xml version="1.0" encoding="UTF-8"?>'."\n"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
    <head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
        <title xml:lang="en">TBW&ndash;The Big War</title>
<?php
    $skins = get_skins();
    if($skins && isset($skins['default']) && count($skins['default'][1]) > 0)
    {
        $keys = array_keys($skins['default'][1]);
        $sub_skin = array_shift($keys);
?>
        <link rel="stylesheet" href="<?=h_root?>/login/style/skin.php?skin=default&amp;type=<?=htmlspecialchars($sub_skin)?>" type="text/css" />
<?php
    }
?>
    </head>
    <body><div id="content-1"><div id="content-2"><div id="content-3"><div id="content-4"><div id="content-5"><div id="content-6"><div id="content-7"><div id="content-8"><div id="content-9"><div id="content-10"><div id="content-11"><div id="content-12"><div id="content-13">
    <h1 class="nachricht-informationen">Öffentliche Nachricht</h1>

<!-- Werbecode Start --->

<p>
<div class="nachricht-informationen" style="text-align:center">
<fieldset>
		<legend>Anzeige</legend>

                       <script type="text/javascript"><!--
                       google_ad_client = "pub-1723997077347194";
                       google_ad_width = 468;
                       google_ad_height = 60;
                       google_ad_format = "468x60_as";
                       google_ad_type = "text";
                       google_ad_channel = "";
                       google_color_border = "000000";
                       google_color_bg = "FFFFFF";
                       google_color_link = "000000";
                       google_color_text = "000000";
                       google_color_url = "008000";
                       //-->
                       </script>
                       <!-- <script type="text/javascript"                          src="http://pagead2.googlesyndication.com/pagead/show_ads.js">                        </script> -->
	</fieldset>
</div>
</p>

<!-- Werbecode Ende --->



<?php
    $databases = get_databases();
    if(isset($_GET['database']) && isset($databases[$_GET['database']]))
        define_globals($_GET['database']);
    if(!isset($_GET['database']) || !isset($databases[$_GET['database']]) || !isset($_GET['id']) || !PublicMessage::publicMessageExists($_GET['id']))
    {
?>
        <p class="error">Die gewüe Nachricht existiert nicht.</p>
<?php
    }
    else
    {
        $message = Classes::PublicMessage($_GET['id']);
?>
        <dl class="nachricht-informationen type-<?=utf8_htmlentities($message->type())?><?=$message->html() ? ' html' : ''?>">
<?php
        if($message->from() != '')
        {
?>
            <dt class="c-absender">Absender</dt>
            <dd class="c-absender"><?=utf8_htmlentities($message->from())?></dd>
<?php
        }
?>
            <dt class="c-empfaenger">Empfänger</dt>
            <dd class="c-empfaenger"><?=utf8_htmlentities($message->to())?></dd>

            <dt class="c-betreff">Betreff</dt>
            <dd class="c-betreff"><?=utf8_htmlentities($message->subject())?></dd>

            <dt class="c-zeit">Zeit</dt>
            <dd class="c-zeit"><?=date('H:i:s, Y-m-d', $message->time())?></dd>

            <dt class="c-nachricht">Nachricht</dt>
            <dd class="c-nachricht">
<?php
        print("\t\t\t\t".preg_replace("/\r\n|\r|\n/", "\n\t\t\t\t", $message->text()));
?>
            </dd>
        </dl>
<?php
    }
?>
       
<!-- Werbecode Start --->

<p>
<div class="nachricht-informationen" style="text-align:center">
<fieldset>
		<legend>Anzeige</legend>

                      <!-- eBay RelevanceAd -->
<script language='JavaScript' type='text/javascript'>
//<-- DO NOT CHANGE -->
// <!--<[CDATA[
    era_width = '468';
    era_height = '60';
    era_layout = 'img';
    era_color_border = 'FF0000';
    era_color_bg = 'FFFFFF';
    era_color_text = '000000';
    era_color_title = '000000';
    era_itemtype = '0';
    era_minprice = '1';
    era_usepulse = '1';
    era_publisher='385740';
// ]]> -->
</script>
<!-- <script language='JavaScript' type='text/javascript' src='http://ebayrelevancead.webmasterplan.com/js/show_ads.js'></script> -->
<!-- /eBay RelevanceAd -->
	</fieldset>
</div>
</p>

<!-- Werbecode Ende --->

	   </div></div></div></div></div></div></div></div></div></div></div></div></div>
    </body>
</html>




