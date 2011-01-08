<?php
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd()."/..";
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require( $_SERVER['DOCUMENT_ROOT'].'/login/scripts/include.php' );
    
    // $me = the user itself
    $planets = $me->getPlanetsList();
    $active_planet = $me->getActivePlanet();
    $act = array_search($active_planet, $planets);
    
    # Naechsten nicht roboter bauenden Planeten herausfinden
    $i = $act+1;
    $fastbuild_next = false;
    
    while( true )
    {
        if( $i >= count($planets) )
            $i = 0;
            
        if( $planets[$i] == $active_planet )
            break;

        /*
         * setting active planet and checking if there's stuff building right now 
         */
        $me->setActivePlanet( $planets[$i] );
            
        $building = $me->checkBuildingThing( 'roboter' );
        $buildingb = $me->checkBuildingThing( 'gebaeude' );
        
        // idle and the we can build robots, lets take it as fastbuild
        if( !$building && $me->getItemLevel( 'B9', 'gebaeude', false ) > 0 && $buildingb[0] != 'B9' )
        {
            $fastbuild_next = $planets[$i];
            break;
        }

        $i++;
    }

    # Vorigen herausfinden
    $i = $act-1;
    $fastbuild_prev = false;
    
    while( true )
    {
        if( $i < 0 )
            $i = count( $planets ) - 1;
            
        if( $i == $act )
            break;

        /*
         * setting active planet and checking if there's stuff building right now 
         */
        $me->setActivePlanet( $planets[$i] );
            
        $building = $me->checkBuildingThing( 'roboter' );
        $buildingb = $me->checkBuildingThing( 'gebaeude' );
        
        // idle and the we can build robots, lets take it as fastbuild
        if( !$building && $me->getItemLevel( 'B9', 'gebaeude', false ) > 0 && $buildingb[0] != 'B9' )
        {
            $fastbuild_prev = $planets[$i];
            break;
        }

        $i--;
    }
    
    $me->setActivePlanet($active_planet);

    if(isset($_POST['cancel-all-roboter']))
    {
        if($me->checkPassword($_POST['cancel-all-roboter']) && $me->removeBuildingThing('roboter', true))
            delete_request();
    }

    if($me->permissionToAct() && isset($_POST['roboter']) && is_array($_POST['roboter']))
    {
        # Roboter in Auftrag geben
        $built = 0;
        foreach($_POST['roboter'] as $id=>$count)
        {
            if($me->buildRoboter($id, $count)) $built++;
        }
        if( $built > 0 )
        {
            if( $me->checkSetting( 'fastbuild' ) && $fastbuild_next !== false )
            {
                # Fastbuild
                $_SESSION['last_click_ignore'] = true;
                $url = global_setting("PROTOCOL").'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?planet='.urlencode($fastbuild_next).'&'.session_name().'='.urlencode(session_id());
                header('Location: '.$url, true, 303);
                die('HTTP redirect: <a href="'.htmlentities($url).'">'.htmlentities($url).'</a>');
            }
            delete_request();    
        }    
    }

    login_gui::html_head();
?>
<h2>Roboter</h2>
<?php
    if(($fastbuild_prev !== false || $fastbuild_next !== false) && $me->permissionToAct())
    {
?>
<ul class="unbeschaeftigte-planeten">
<?php
        $active_planet = $me->getActivePlanet();
        if($fastbuild_prev !== false)
        {
            $me->setActivePlanet($fastbuild_prev);
?>
    <li class="c-voriger"><a href="roboter.php?planet=<?php echo htmlentities(urlencode($fastbuild_prev))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Voriger unbeschäftigter Planet: &bdquo;<?php echo utf8_htmlentities($me->planetName())?>&ldquo; (<?php echo utf8_htmlentities($me->getPosString())?>) [U]" tabindex="1" accesskey="u" rel="prev">&larr;</a></li>
<?php
        }
        if($fastbuild_next !== false)
        {
            $me->setActivePlanet($fastbuild_next);
?>
    <li class="c-naechster"><a href="roboter.php?planet=<?php echo htmlentities(urlencode($fastbuild_next))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Nächster unbeschäftigter Planet: &bdquo;<?php echo utf8_htmlentities($me->planetName())?>&ldquo; (<?php echo utf8_htmlentities($me->getPosString())?>) [Q]" tabindex="2" accesskey="q" rel="next">&rarr;</a></li>
<?php
        }
        $me->setActivePlanet($active_planet);
?>
</ul>
<?php
    }
?>
<form action="roboter.php?<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" method="post">
<?php
    $tabindex = 1;
    $roboter = $me->getItemsList('roboter');
    $building_possible = (!($building_gebaeude = $me->checkBuildingThing('gebaeude')) || $building_gebaeude[0] != 'B9');
    foreach($roboter as $id)
    {
        $item_info = $me->getItemInfo($id);

        if(!$item_info['buildable'] && $item_info['level'] <= 0)
            continue;
?>
    <div class="item roboter" id="item-<?php echo htmlentities($id)?>">
        <h3><a href="help/description.php?id=<?php echo htmlentities(urlencode($id))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Genauere Informationen anzeigen"><?php echo utf8_htmlentities($item_info['name'])?></a> <span class="anzahl">(<?php echo utf8_htmlentities($item_info['level'])?>)</span></h3>
<?php
        if($me->permissionToAct() && $building_possible && $item_info['buildable'])
        {
?>
        <ul>
            <li class="item-bau"><input type="text" name="roboter[<?php echo utf8_htmlentities($id)?>]" value="0" tabindex="<?php echo $tabindex++?>" /></li>
        </ul>
<?php
        }
?>
        <dl>
            <dt class="item-kosten">Kosten</dt>
            <dd class="item-kosten">
                <?php echo format_ress($item_info['ress'], 4)?>
            </dd>

            <dt class="item-bauzeit">Bauzeit</dt>
            <dd class="item-bauzeit"><?php echo format_btime($item_info['time'])?></dd>
        </dl>
    </div>
<?php
    }

    if($tabindex > 1)
    {
?>
    <div><button type="submit" tabindex="<?php echo $tabindex++?>" accesskey="u">In A<kbd>u</kbd>ftrag geben</button></div>
<?php
    }
?>
</form>
<?php
    $building_roboter = $me->checkBuildingThing('roboter');
    if(count($building_roboter) > 0)
    {
?>
<h3 id="aktive-auftraege">Aktive Aufträge</h3>
<ol class="queue roboter">
<?php
        $i = 0;

        $keys = array_keys($building_roboter);
        $first_building = &$building_roboter[array_shift($keys)];
        $first = array($first_building[0], $first_building[1]+$first_building[3]);
        $first_building[1] += $first_building[3];
        $first_building[2]--;
        if($first_building[2] <= 0) array_shift($building_roboter);
        $first_info = $me->getItemInfo($first[0]);
?>
    <li class="<?php echo utf8_htmlentities($first[0])?> active<?php echo (count($building_roboter) <= 0) ? ' last' : ''?>" title="Fertigstellung: <?php echo date('H:i:s, Y-m-d', (int)$first[1])?> (Serverzeit)"><strong><?php echo utf8_htmlentities($first_info['name'])?> <span class="restbauzeit" id="restbauzeit-<?php echo $i++?>">Fertigstellung: <?php echo date('H:i:s, Y-m-d', (int)$first[0])?> (Serverzeit)</span></strong></li>
<?php
        if(count($building_roboter) > 0)
        {
            $keys = array_keys($building_roboter);
            $last = array_pop($keys);
            foreach($building_roboter as $key=>$bau)
            {
                $finishing_time = $bau[1]+$bau[2]*$bau[3];
                $item_info = $me->getItemInfo($bau[0]);
?>
    <li class="<?php echo utf8_htmlentities($bau[0])?><?php echo ($key == $last) ? ' last' : ''?>" title="Fertigstellung: <?php echo date('H:i:s, Y-m-d', $finishing_time)?> (Serverzeit)"><?php echo utf8_htmlentities($item_info['name'])?> &times; <?php echo $bau[2]?><?php if($key == $last){?> <span class="restbauzeit" id="restbauzeit-<?php echo $i++?>">Fertigstellung: <?php echo date('H:i:s, Y-m-d', $finishing_time)?> (Serverzeit)</span><?php }?></li>
<?php
            }
        }
?>
</ol>
<script type="text/javascript">
    init_countdown('0', <?php echo $first[1]?>, false);
<?php
        if(count($building_roboter) > 0)
        {
?>
    init_countdown('<?php echo $i-1?>', <?php echo $finishing_time?>, false);
<?php
        }
?>
</script>
<form action="<?php echo htmlentities(global_setting("USE_PROTOCOL").'://'.$_SERVER['HTTP_HOST'].h_root.'/login/roboter.php?'.urlencode(session_name()).'='.urlencode(session_id()))?>" method="post" class="alle-abbrechen">
    <p>Geben Sie hier Ihr Passwort ein, um alle im Bau befindlichen Roboter <strong>ohne Kostenrückerstattung</strong> abzubrechen.</p>
    <div><input type="password" name="cancel-all-roboter" /><input type="submit" value="Alle abbrechen" /></div>
</form>
<?php
    }

    login_gui::html_foot();
?>
