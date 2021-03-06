<?php
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd()."/..";
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require($_SERVER['DOCUMENT_ROOT'].'/login/scripts/include.php');

    $planet_error = false;
    if(isset($_POST['planet_name']))
    {
        if(trim($_POST['planet_name']) == '')
            $_POST['planet_name'] = $me->planetName();
        elseif(strlen($_POST['planet_name']) <= 24)
            $planet_error = !$me->planetName($_POST['planet_name']);
    }

    # Herausfinden, ob eigene Flotten zu/von diesem Planeten unterwegs sind
    $flotte_unterwegs = $me->checkOwnFleetWithPlanet();
    $planets = $me->getPlanetsList();

    if(isset($_POST['act_planet']) && isset($_POST['password']) && !$me->userLocked() && !$flotte_unterwegs && count($planets) > 1)
    {
        if(!$me->checkPassword($_POST['password']))
            $aufgeben_error = 'Sie haben ein falsches Passwort eingegeben.';
        elseif($_POST['act_planet'] != $_SESSION['act_planet'])
            $aufgeben_error = 'Sicherheit: Da inzwischen der Planet gewechselt wurde, hätten Sie es wohl bereut, wenn Sie den aktuellen aufgegeben hätten.';
        else
        {
            $me->removePlanet($_SESSION['act_planet']);
            $_SESSION['act_planet'] = $me->getActivePlanet();
            $planets = $me->getPlanetsList();
        }
    }

    if(isset($_GET['down']))
    {
        $me->movePlanetDown($_GET['down']);
        $_SESSION['act_planet'] = $me->getActivePlanet();
        $planets = $me->getPlanetsList();
    }
    if(isset($_GET['up']))
    {
        $me->movePlanetUp($_GET['up']);
        $_SESSION['act_planet'] = $me->getActivePlanet();
        $planets = $me->getPlanetsList();
    }

    login_gui::html_head();
    $keyarray = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', ' ');
    #Planiname Zeichen pruefen
    if(isset($_POST['planet_name']))
    {
        $stringplanet = $_POST['planet_name'];
        $nameSyntaxOk = true;
        for($i=0;$i<strlen($stringplanet);$i++)
        {
            $explode[$i] = substr($stringplanet, $i, 1);
            if(!in_array($explode[$i],$keyarray)) $nameSyntaxOk = false;
        }
    }
    
    // check if planetName returned error and syntax IS ok (planetName will return false on wrong syntax too!)
    if( $planet_error && $nameSyntaxOk )
    {
?>
<p class="error">
    Datenbankfehler &#40;1110&#41;
</p>
<?php
    }
    else if( $me->getName() == GLOBAL_DEMOACCNAME )
    {
?>
<p class="error">
    Nicht verfügbar im Demo-Account.
</p>
<?php        
    }
    else if(isset($_POST['planet_name']) && strlen($_POST['planet_name']) > 17)
    {
?>
<p class="error">
    Der Name darf maximal 20&nbsp;Bytes lang sein.
</p>
<?php
    }
    else if(isset($_POST['planet_name']) && $nameSyntaxOk == false)
    {
?>
<p class="error">
            Der Name des Planeten enthält ungültige Zeichen.
</p>
<?php
    }
?>
<form action="rename.php?<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" method="post">
    <fieldset>
        <legend>Planeten umbenennen</legend>
        <dl>
            <dt><label for="name"><kbd>N</kbd>euer Name</label></dt>
            <dd><input type="text" id="name" name="planet_name" value="<?php echo utf8_htmlentities($me->planetName())?>" maxlength="20" accesskey="n" tabindex="1" /></dd>
        </dl>
        <div><button type="submit" accesskey="u" tabindex="2"><kbd>U</kbd>mbenennen</button></div>
    </fieldset>
</form>
<?php
    if($flotte_unterwegs || count($planets) <= 1)
    {
?>
<p class="planeten-nicht-aufgeben">
    Sie können diesen Planeten derzeit nicht aufgeben, da Flottenbewegungen Ihrerseits von/zu diesem Planeten unterwegs sind oder dies Ihr einziger Planet ist.
</p>
<?php
    }
    else
    {
        if(isset($aufgeben_error) && trim($aufgeben_error) != '')
        {
?>
<p class="error">
    <?php echo htmlentities($aufgeben_error)."\n"?>
</p>
<?php
        }
?>
<form action="<?php echo htmlentities(global_setting("USE_PROTOCOL").'://'.$_SERVER['HTTP_HOST'].h_root.'/login/scripts/rename.php?'.urlencode(session_name()).'='.urlencode(session_id()))?>" method="post">
    <fieldset>
        <legend>Planeten aufgeben<input type="hidden" name="act_planet" value="<?php echo htmlentities($_SESSION['act_planet'])?>" /></legend>
        <dl>
            <dt><label for="password">Passwort</label></dt>
            <dd><input type="password" id="password" name="password" tabindex="3" /></dd>
        </dl>
        <div><input type="submit" name="umode" value="Löschen" tabindex="<?php echo $tabindex++?>" onclick="return confirm('Achtung! Sie sind im Begriff, diesen Planeten zu löschen. Wollen Sie dies wirklich tun?');" /></div>

    </fieldset>
</form>
<?php
    }

    if(count($planets) > 1)
    {
?>
<fieldset class="planeten-reihenfolge">
    <legend>Planeten-Reihenfolge</legend>
    <ol>
<?php
        $active_planet = $me->getActivePlanet();
        foreach($planets as $i=>$planet)
        {
            $me->setActivePlanet($planet);
?>
        <li><?php echo utf8_htmlentities($me->planetName())?> <span class="pos">(<?php echo utf8_htmlentities($me->getPosString())?>)</span><span class="aktionen"><?php if($i != 0){?> &ndash; <a href="rename.php?up=<?php echo htmlentities(urlencode($planet))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.session_id())?>" class="hoch">[Hoch]</a><?php } if($i != count($planets)-1){?> &ndash; <a href="rename.php?down=<?php echo htmlentities(urlencode($planet))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.session_id())?>" class="runter">[Runter]</a><?php }?></span></li>
<?php
        }
        $me->setActivePlanet($active_planet);
?>
    </ol>
</fieldset>
<?php
    }

    login_gui::html_foot();
?>
