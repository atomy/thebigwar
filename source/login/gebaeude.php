<?php
    require_once( '../include/config_inc.php' );
    require( TBW_ROOT.'login/scripts/include.php' );

    $planets = $me->getPlanetsList();
    $active_planet = $me->getActivePlanet();
    $act = array_search($active_planet, $planets);

    # Naechsten nicht bauenden Planeten herausfinden
    $i = $act+1;
    $fastbuild_next = false;
    while(true)
    {
        if($i >= count($planets))
            $i = 0;
        if($planets[$i] == $active_planet)
            break;

        $me->setActivePlanet($planets[$i]);
        $building = $me->checkBuildingThing('gebaeude');
        if(!$building)
        {
            $fastbuild_next = $planets[$i];
            break;
        }

        $i++;
    }

    # Vorigen herausfinden
    $i = $act-1;
    $fastbuild_prev = false;
    while(true)
    {
        if($i < 0)
            $i = count($planets)-1;
        if($i == $act)
            break;

        $me->setActivePlanet($planets[$i]);
        $building = $me->checkBuildingThing('gebaeude');
        if(!$building)
        {
            $fastbuild_prev = $planets[$i];
            break;
        }

        $i--;
    }

    $me->setActivePlanet($active_planet);

    if(isset($_GET['ausbau']))
    {
        $a_id = $_GET['ausbau'];
        $rueckbau = false;
    }
    elseif(isset($_GET['abbau']))
    {
        $a_id = $_GET['abbau'];
        $rueckbau = true;
    }

    if(isset($a_id) && $me->permissionToAct() && $me->buildGebaeude($a_id, $rueckbau))
    {
        if($me->checkSetting('fastbuild') && $fastbuild_next !== false)
        {
            # Fastbuild

            $_SESSION['last_click_ignore'] = true;
            $url = global_setting("PROTOCOL").'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?planet='.urlencode($fastbuild_next).'&'.session_name().'='.urlencode(session_id());
            header('Location: '.$url, true, 303);
            die('HTTP redirect: <a href="'.htmlentities($url).'">'.htmlentities($url).'</a>');
        }
        else
            delete_request();
    }

    if(isset($_GET['cancel']))
    {
        $building = $me->checkBuildingThing('gebaeude');
        if($building && $building[0] == $_GET['cancel'] && $me->removeBuildingThing('gebaeude'))
            delete_request();
    }

    login_gui::html_head();
?>
<h2>Gebäude</h2>
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
    <li class="c-voriger"><a href="gebaeude.php?planet=<?php=htmlentities(urlencode($fastbuild_prev))?>&amp;<?php=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Voriger unbeschäftigter Planet: &bdquo;<?php=utf8_htmlentities($me->planetName())?>&ldquo; (<?php=utf8_htmlentities($me->getPosString())?>) [U]" tabindex="1" accesskey="u" rel="prev">&larr;</a></li>
<?php
        }
        if($fastbuild_next !== false)
        {
            $me->setActivePlanet($fastbuild_next);
?>
    <li class="c-naechster"><a href="gebaeude.php?planet=<?php=htmlentities(urlencode($fastbuild_next))?>&amp;<?php=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Nächster unbeschäftigter Planet: &bdquo;<?php=utf8_htmlentities($me->planetName())?>&ldquo; (<?php=utf8_htmlentities($me->getPosString())?>) [Q]" tabindex="2" accesskey="q" rel="next">&rarr;</a></li>
<?php
        }
        $me->setActivePlanet($active_planet);
?>
</ul>
<?php
    }

    $tabindex = 3;
    $gebaeude = $me->getItemsList('gebaeude');
    foreach($gebaeude as $id)
    {
        $geb = $me->getItemInfo($id, 'gebaeude');
        $building = false;

        if(!$geb['deps-okay'] && $geb['level'] <= 0) # Abhaengigkeiten nicht erfuellt
            continue;
?>
<div class="item gebaeude" id="item-<?php=htmlentities($id)?>">
    <h3><a href="help/description.php?id=<?php=htmlentities(urlencode($id))?>&amp;<?php=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Genauere Informationen anzeigen"><?php=utf8_htmlentities($geb['name'])?></a> <span class="stufe">(Stufe&nbsp;<?php=ths($geb['level'])?>)</span></h3>
<?php
        if($me->permissionToAct() && ($geb['buildable'] || $geb['debuildable']) && !($building = $me->checkBuildingThing('gebaeude')) && ($id != 'B8' || !$me->checkBuildingThing('forschung')) && ($id != 'B9' || !$me->checkBuildingThing('roboter')) && ($id != 'B10' || (!$me->checkBuildingThing('schiffe') && !$me->checkBuildingThing('verteidigung'))))
        {
?>
    <ul>
<?php
            if($geb['buildable'])
            {
                $enough_ress = $me->checkRess($geb['ress']);
?>
        <li class="item-ausbau<?php=$enough_ress ? '' : ' no-ress'?>"><?php=$enough_ress ? '<a href="gebaeude.php?ausbau='.htmlentities(urlencode($id)).'&amp;'.htmlentities(urlencode(session_name()).'='.urlencode(session_id())).'" tabindex="'.($tabindex++).'">' : ''?>Ausbau auf Stufe&nbsp;<?php=ths($geb['level']+1)?><?php=$enough_ress ? '</a>' : ''?></li>
<?php
            }
            if($geb['debuildable'])
            {
                $ress = $geb['ress'];
                $ress[0] /= 2;
                $ress[1] /= 2;
                $ress[2] /= 2;
                $ress[3] /= 2;
                $enough_ress = $me->checkRess($ress);
?>
        <li class="item-rueckbau<?php=$enough_ress ? '' : ' no-ress'?>"><?php=$enough_ress ? '<a href="gebaeude.php?abbau='.htmlentities(urlencode($id)).'&amp;'.htmlentities(urlencode(session_name()).'='.urlencode(session_id())).'">' : ''?>Rückbau auf Stufe&nbsp;<?php=ths($geb['level']-1)?><?php=$enough_ress ? '</a>' : ''?></li>
<?php
            }
?>
    </ul>
<?php
        }
        elseif($building && $building[0] == $id)
        {
?>
    <div class="restbauzeit" id="restbauzeit-<?php=htmlentities($building[0])?>">Fertigstellung: <?php=date('H:i:s, Y-m-d', $building[1])?> (Serverzeit), <a href="gebaeude.php?cancel=<?php=htmlentities(urlencode($building[0]))?>&amp;<?php=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" class="abbrechen">Abbrechen</a></div>
    <script type="text/javascript">
        init_countdown('<?php=$building[0]?>', <?php=$building[1]?>);
    </script>
<?php
        }
?>
    <dl>
        <dt class="item-kosten">Kosten</dt>
        <dd class="item-kosten">
            <?php=format_ress($geb['ress'], 3)?>
        </dd>

        <dt class="item-bauzeit">Bauzeit</dt>
        <dd class="item-bauzeit"><?php=format_btime($geb['time'])?></dd>
    </dl>
</div>
<?php
    }
    
    if(($fastbuild_prev !== false || $fastbuild_next !== false) && $me->permissionToAct())
    {
?>

<div class="item gebaeude"></div>
<ul class="unbeschaeftigte-planeten2">
<?php
        $active_planet = $me->getActivePlanet();
        if($fastbuild_prev !== false)
        {
            $me->setActivePlanet($fastbuild_prev);
?>
    <li class="c-voriger2"><a href="gebaeude.php?planet=<?php=htmlentities(urlencode($fastbuild_prev))?>&amp;<?php=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Voriger unbeschäftigter Planet: &bdquo;<?php=utf8_htmlentities($me->planetName())?>&ldquo; (<?php=utf8_htmlentities($me->getPosString())?>) [U]" rel="prev">&larr;</a></li>
<?php
        }
        if($fastbuild_next !== false)
        {
            $me->setActivePlanet($fastbuild_next);
?>
    <li class="c-naechster2"><a href="gebaeude.php?planet=<?php=htmlentities(urlencode($fastbuild_next))?>&amp;<?php=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Nächster unbeschäftigter Planet: &bdquo;<?php=utf8_htmlentities($me->planetName())?>&ldquo; (<?php=utf8_htmlentities($me->getPosString())?>) [Q]" rel="next">&rarr;</a></li>
<?php
        }
        $me->setActivePlanet($active_planet);
?>
</ul>


<?php
    }    
?>
<?php
    login_gui::html_foot();
?>
