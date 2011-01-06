<?php
    require_once( '../include/config_inc.php' );
    require( TBW_ROOT.'login/scripts/include.php' );

    // $me = the user itself
    $laufende_forschungen = array();
    $planets = $me->getPlanetsList();
    $active_planet = $me->getActivePlanet();
    $act = array_search($active_planet, $planets);
    
    # Naechsten nicht forschenden Planeten herausfinden
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
            
        $building = $me->checkBuildingThing( 'forschung' );
        $buildingb = $me->checkBuildingThing( 'gebaeude' );
    
        // idle and we can research, lets take it as fastbuild
        if( !$building && $me->getItemLevel( 'B8', 'gebaeude', false ) > 0 && $buildingb[0] != 'B8' )
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
            
        $building = $me->checkBuildingThing( 'forschung' );
        $buildingb = $me->checkBuildingThing( 'gebaeude' );
    
        // idle and we can research, lets take it as fastbuild
        if( !$building && $me->getItemLevel( 'B8', 'gebaeude', false ) > 0 && $buildingb[0] != 'B8' )
        {
            $fastbuild_prev = $planets[$i];
            break;
        }

        $i--;
    }
    
    foreach( $planets as $planet )
    {
        $me->setActivePlanet( $planet );
        $building = $me->checkBuildingThing('forschung');
        
        // if there's a research active save it
        if( $building )
            $laufende_forschungen[] = $building[0];
            
        // check if research lab is currently being worked on (upgrade/downgrade)
        else if( $building = $me->checkBuildingThing( 'gebaeude' ) && $building[0] == 'B8' )
            $laufende_forschungen[] = false;
    }
    
    $me->setActivePlanet($active_planet);

    if( isset($_GET['lokal'] ) )
    {
        $a_id = $_GET['lokal'];
        $global = false;
    }
    elseif(isset($_GET['global']) && count($laufende_forschungen) == 0)
    {
        $a_id = $_GET['global'];
        $global = true;
    }

    if(isset($a_id) && $me->permissionToAct() && $me->buildForschung($a_id, $global))
    {
        if($me->checkSetting('fastbuild') && $fastbuild_next !== false)
        {
            # Fastbuild
            $_SESSION['last_click_ignore'] = true;
            $url = global_setting("PROTOCOL").'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?planet='.urlencode($fastbuild_next).'&'.session_name().'='.urlencode(session_id());
            header('Location: '.$url, true, 303);
            die('HTTP redirect: <a href="'.htmlentities($url).'">'.htmlentities($url).'</a>');
        }    
        delete_request();
    }

    if(isset($_GET['cancel']))
    {
        $building = $me->checkBuildingThing('forschung');
        if($building && $building[0] == $_GET['cancel'] && $me->removeBuildingThing('forschung'))
            delete_request();
    }

    $forschungen = $me->getItemsList('forschung');
    $building = $me->checkBuildingThing('forschung');

    login_gui::html_head();
?>
<h2>Forschung</h2>
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
    <li class="c-voriger"><a href="forschung.php?planet=<?php echohtmlentities(urlencode($fastbuild_prev))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Voriger unbeschäftigter Planet: &bdquo;<?php echoutf8_htmlentities($me->planetName())?>&ldquo; (<?php echoutf8_htmlentities($me->getPosString())?>) [U]" tabindex="1" accesskey="u" rel="prev">&larr;</a></li>
<?php
        }
        if($fastbuild_next !== false)
        {
            $me->setActivePlanet($fastbuild_next);
?>
    <li class="c-naechster"><a href="forschung.php?planet=<?php echohtmlentities(urlencode($fastbuild_next))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Nächster unbeschäftigter Planet: &bdquo;<?php echoutf8_htmlentities($me->planetName())?>&ldquo; (<?php echoutf8_htmlentities($me->getPosString())?>) [Q]" tabindex="2" accesskey="q" rel="next">&rarr;</a></li>
<?php
        }
        $me->setActivePlanet($active_planet);
?>
</ul>
<?php
    }
    
    $tabindex = 1;
    foreach( $forschungen as $id )
    {
        $item_info = $me->getItemInfo($id, 'forschung');

        if(!$item_info['deps-okay'] && $item_info['level'] <= 0 && (!$building || $building[0] != $id))
            continue;

        $buildable_global = $item_info['buildable'];
        if($buildable_global && count($laufende_forschungen) > 0)
            $buildable_global = false; # Es wird schon wo geforscht
?>
<div class="item forschung" id="item-<?php echohtmlentities($id)?>">
    <h3><a href="help/description.php?id=<?php echohtmlentities(urlencode($id))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Genauere Informationen anzeigen"><?php echoutf8_htmlentities($item_info['name'])?></a> <span class="stufe">(Level&nbsp;<?php echoths($item_info['level'])?>)</span></h3>
<?php
        if((!($building_geb = $me->checkBuildingThing('gebaeude')) || $building_geb[0] != 'B8') && $item_info['buildable'] && $me->permissionToAct() && !($building = $me->checkBuildingThing('forschung')) && !in_array($id, $laufende_forschungen) && $item_info['deps-okay'])
        {
            $enough_ress = $me->checkRess($item_info['ress']);
            $buildable_global = ($buildable_global && $enough_ress);
?>
    <ul>
        <li class="item-ausbau forschung-lokal<?php echo$enough_ress ? '' : ' no-ress'?>"><?php echo$enough_ress ? '<a href="forschung.php?lokal='.htmlentities(urlencode($id)).'&amp;'.htmlentities(urlencode(session_name()).'='.urlencode(session_id())).'" tabindex="'.($tabindex++).'">' : ''?>Lokal weiterentwickeln<?php echo$enough_ress ? '</a>' : ''?></li>
<?php
            if(count($laufende_forschungen) <= 0)
            {
?>
        <li class="item-ausbau forschung-global<?php echo$buildable_global ? '' : ' no-ress'?>"><?php echo$buildable_global ? '<a href="forschung.php?global='.htmlentities(urlencode($id)).'&amp;'.htmlentities(urlencode(session_name()).'='.urlencode(session_id())).'" tabindex="'.($tabindex++).'">' : ''?>Global weiterentwickeln<?php echo$buildable_global ? '</a>' : ''?></li>
<?php
            }
?>
    </ul>
<?php
        }
        elseif($building && $building[0] == $id)
        {
?>
    <div class="restbauzeit" id="restbauzeit-<?php echohtmlentities($id)?>">Fertigstellung: <?php echodate('H:i:s, Y-m-d', $building[1])?> (Serverzeit), <a href="forschung.php?cancel=<?php echohtmlentities(urlencode($id))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" class="abbrechen">Abbrechen</a></div>
    <script type="text/javascript">
        init_countdown('<?php echo$id?>', <?php echo$building[1]?>);
    </script>
<?php
        }
?>
    <dl>
        <dt class="item-kosten">Kosten</dt>
        <dd class="item-kosten">
            <?php echoformat_ress($item_info['ress'], 3)?>
        </dd>

        <dt class="item-bauzeit forschung-lokal">Bauzeit lokal</dt>
        <dd class="item-bauzeit forschung-lokal"><?php echoformat_btime($item_info['time_local'])?></dd>

        <dt class="item-bauzeit forschung-global">Bauzeit global</dt>
        <dd class="item-bauzeit forschung-global"><?php echoformat_btime($item_info['time_global'])?></dd>
    </dl>
</div>
<?php
    }
?>
<?php
    login_gui::html_foot();
?>
