<?php
    require_once( '../include/config_inc.php' );
    require( TBW_ROOT.'login/scripts/include.php' );

    if(!$me->checkSetting('ajax'))
        $DISABLE_ADS = true;
    login_gui::html_head();

    $pos = $me->getPos();

    $galaxy_n = $pos[0];
    $system_n = $pos[1];
    if(isset($_GET['galaxy']))
        $galaxy_n = $_GET['galaxy'];
    if(isset($_GET['system']))
        $system_n = $_GET['system'];

    __autoload('Galaxy');
    $galaxy_count = getGalaxiesCount();

    $next_galaxy = $galaxy_n+1;
    $prev_galaxy = $galaxy_n-1;
    if($next_galaxy > $galaxy_count)
        $next_galaxy = 1;
    if($prev_galaxy < 1)
        $prev_galaxy = $galaxy_count;

    $galaxy = Classes::Galaxy($galaxy_n);
    $system_count = $galaxy->getSystemsCount();

    $next_system = $system_n+1;
    $prev_system = $system_n-1;
    if($next_system > $system_count)
        $next_system = 1;
    if($prev_system < 1)
        $prev_system = $system_count;

    $verb_list = $me->getVerbuendetList();
    if(count($verb_list) > 0)
    {
        $verb_jcheck = array();
        foreach($verb_list as $verb)
            $verb_jcheck[] = "sinfo[i]['owner'] == '".str_replace("'", "\\'", $verb)."'";
        $verb_jcheck = implode(' || ', $verb_jcheck);
    }
    else $verb_jcheck = 'false';

    $shortcuts_list = $me->getPosShortcutsList();
    if(count($shortcuts_list) > 0)
    {
        $sc_jcheck = array();
        foreach($shortcuts_list as $sc)
            $sc_jcheck[] = "scp != '".str_replace("'", "\\'", $sc)."'";
        $sc_jcheck = implode(' && ', $sc_jcheck);
    }
    else $sc_jcheck = 'true';

    if($me->checkSetting('ajax'))
    {
?>
<script type="text/javascript">
// <![CDATA[
    var current_system = <?php echo$system_n?>;
    var min_preload = 5;
    var max_preload = 15;

    function change_system(new_system)
    {
        document.getElementById("sys").firstChild.nodeValue = new_system;
        system_split = new_system.split(/:/);
        if(typeof preloaded_systems[new_system] == 'undefined')
        {
            if(!preloading_systems[new_system])
                preload_systems(new_system);
            setTimeout("change_system('"+new_system+"');", 250);
            return;
        }

        tbody = document.getElementById('karte-system');
        sinfo = preloaded_systems[new_system];
        for(i=0,j=0; i<tbody.childNodes.length; i++)
        {
            if(tbody.childNodes[i].nodeType == 1)
            {
                if(j>=sinfo.length-1)
                {
                    tbody.removeChild(tbody.childNodes[i]);
                    i--;
                }
                else j++;
            }
            else
            {
                tbody.removeChild(tbody.childNodes[i]);
                i--;
            }
        }

        for(i=1; i<sinfo.length; i++)
        {
            if(tbody.childNodes[i-1])
            {
                new_tr = tbody.childNodes[i-1];
                while(new_tr.childNodes.length > 0) new_tr.removeChild(new_tr.firstChild);
            }
            else
            {
                new_tr = document.createElement('tr');
                tbody.appendChild(new_tr);
            }

            if(!sinfo[i]['owner'])
                new_tr.className = 'leer';
            else
            {
                if(sinfo[i]['owner'] == '<?php echostr_replace("'", "\\'", $_SESSION['username'])?>')
                    new_tr.className = 'eigen';
                else if(<?php echo$verb_jcheck?>)
                    new_tr.className = 'verbuendet';
                else
                    new_tr.className = 'fremd';
                if(sinfo[i]['flag'] == 'U')
                    new_tr.className += ' urlaub';
                else if(sinfo[i]['flag'] == 'g')
                    new_tr.className += ' gesperrt';
            }

            var new_td = document.createElement('td');
            new_td.className = 'c-planet';
            new_td.appendChild(document.createTextNode(i));
            if(sinfo[i]['truemmerfeld'])
            {
                new_td.appendChild(document.createTextNode(' '));
                new_tf = document.createElement('abbr');
                new_tf.setAttribute('title', 'Trümmerfeld: '+ths(sinfo[i]['truemmerfeld'][0])+'\u00a0Carbon, '+ths(sinfo[i]['truemmerfeld'][1])+'\u00a0Aluminium, '+ths(sinfo[i]['truemmerfeld'][2])+'\u00a0Wolfram, '+ths(sinfo[i]['truemmerfeld'][3])+'\u00a0Radium');
                new_tf.appendChild(document.createTextNode('T'));
                new_td.appendChild(new_tf);
            }
            new_tr.appendChild(new_td);

            var new_td = document.createElement('td');
            new_td.className = 'c-name';
            if(sinfo[i]['owner'])
            {
                if(sinfo[i]['alliance'])
                {
                    var new_alliance1 = document.createElement('span');
                    new_alliance1.className = 'allianz';
                    if(sinfo[i]['alliance'] == '<?php echostr_replace("'", "\\'", $me->allianceTag())?>')
                        new_alliance1.className += ' verbuendet';
                    new_alliance1.appendChild(document.createTextNode('['));
                    var new_alliance2 = document.createElement('a');
                    new_alliance2.href = 'help/allianceinfo.php?alliance='+encodeURIComponent(sinfo[i]['alliance'])+'&<?php echourlencode(session_name()).'='.urlencode(session_id())?>';
                    new_alliance2.title = 'Informationen zu dieser Allianz anzeigen';
                    new_alliance2.appendChild(document.createTextNode(sinfo[i]['alliance']));
                    new_alliance1.appendChild(new_alliance2);
                    new_alliance1.appendChild(document.createTextNode(']'));
                    new_td.appendChild(new_alliance1);
                    new_td.appendChild(document.createTextNode(' '));
                }
                new_td.appendChild(document.createTextNode(sinfo[i]['name']+' '));

                var playername1 = document.createElement('span');
                playername1.className = 'playername';
                playername1.appendChild(document.createTextNode('('));
                var playername2 = document.createElement('a');
                playername2.href = 'help/playerinfo.php?player='+encodeURIComponent(sinfo[i]['owner'])+'&<?php echourlencode(session_name()).'='.urlencode(session_id())?>';
                playername2.title = 'Informationen zu diesem Spieler anzeigen';
                playername2.appendChild(document.createTextNode(sinfo[i]['owner']));
                playername1.appendChild(playername2);
                if(sinfo[i]['flag'])
                    playername1.appendChild(document.createTextNode(' ('+sinfo[i]['flag']+')'));
                playername1.appendChild(document.createTextNode(')'));
                new_td.appendChild(playername1);
            }
            new_tr.appendChild(new_td);

            var new_td = document.createElement('td');
            new_td.className = 'c-aktionen';

            // Koordinaten verwenden
            new_td.appendChild(document.createElement('ul'));
            new_td.firstChild.appendChild(document.createElement('li'));
            new_td.firstChild.firstChild.className = 'c-koordinaten-verwenden';
            new_td.firstChild.firstChild.appendChild(document.createElement('a'));
            new_td.firstChild.firstChild.firstChild.title = 'Die Koordinaten dieses Planeten ins Flottenmenü einsetzen';
            new_td.firstChild.firstChild.firstChild.href = 'flotten.php?action_galaxy='+encodeURIComponent(system_split[0])+'&action_system='+encodeURIComponent(system_split[1])+'&action_planet='+encodeURIComponent(i)+'&<?php echourlencode(session_name()).'='.urlencode(session_id())?>';
            new_td.firstChild.firstChild.firstChild.appendChild(document.createTextNode('Koordinaten verwenden'));

            var scp = new_system+':'+i;
            if(<?php echo$sc_jcheck?>)
            {
                new_td.firstChild.appendChild(new_el1 = document.createElement('li'));
                new_el1.className = 'c-lesezeichen';
                new_el1.appendChild(new_el2 = document.createElement('a'));
                new_el2.href = 'flotten.php?action=shortcut&action_galaxy='+encodeURIComponent(system_split[0])+'&action_system='+encodeURIComponent(system_split[1])+'&action_planet='+encodeURIComponent(i)+'&<?php echourlencode(session_name()).'='.urlencode(session_id())?>';
                new_el2.title = 'Die Koordinaten dieses Planeten zu den Lesezeichen hinzufügen';
                new_el2.onclick = new Function('return fast_action(this, "shortcut", '+system_split[0]+', '+system_split[1]+', '+i+');');
                new_el2.appendChild(document.createTextNode('Lesezeichen'));
            }

            if(sinfo[i]['owner'] != '<?php echostr_replace("'", "\\'", $_SESSION['username'])?>')
            {
<?php
        if($me->permissionToAct() && $me->getItemLevel('S5', 'schiffe') > 0)
        {
?>
                // Spionieren
                if(sinfo[i]['flag'] != 'U')
                {
                    new_td.firstChild.appendChild(new_el1 = document.createElement('li'));
                    new_el1.className = 'c-spionieren';
                    new_el1.appendChild(new_el2 = document.createElement('a'));
                    new_el2.href = 'flotten.php?action=spionage&action_galaxy='+encodeURIComponent(system_split[0])+'&action_system='+encodeURIComponent(system_split[1])+'&action_planet='+encodeURIComponent(i)+'&<?php echourlencode(session_name()).'='.urlencode(session_id())?>';
                    new_el2.title = 'Spionieren Sie diesen Planeten aus';
                    new_el2.onclick = new Function('return fast_action(this, "spionage", '+system_split[0]+', '+system_split[1]+', '+i+');');
                    new_el2.appendChild(document.createTextNode('Spionieren'));
                }
<?php
        }
?>
                // Nachricht schreiben
                if(sinfo[i]['owner'])
                {
                    new_td.firstChild.appendChild(new_el1 = document.createElement('li'));
                    new_el1.className = 'c-nachricht';
                    new_el1.appendChild(new_el2 = document.createElement('a'));
                    new_el2.href = 'nachrichten.php?to='+encodeURIComponent(sinfo[i]['owner'])+'&<?php echourlencode(session_name()).'='.urlencode(session_id())?>';
                    new_el2.title = 'Schreiben Sie diesem Spieler eine Nachricht';
                    new_el2.appendChild(document.createTextNode('Nachricht'));
                }
<?php
        if($me->permissionToAct() && $me->checkPlanetCount() && $me->getItemLevel('S6', 'schiffe') > 0)
        {
?>
                // Besiedeln
                if(!sinfo[i]['owner'])
                {
                    new_td.firstChild.appendChild(new_el1 = document.createElement('li'));
                    new_el1.className = 'c-besiedeln';
                    new_el1.appendChild(new_el2 = document.createElement('a'));
                    new_el2.href = 'flotten.php?action=besiedeln&action_galaxy='+encodeURIComponent(system_split[0])+'&action_system='+encodeURIComponent(system_split[1])+'&action_planet='+encodeURIComponent(i)+'&<?php echourlencode(session_name()).'='.urlencode(session_id())?>';
                    new_el2.title = 'Schicken Sie ein Besiedelungsschiff zu diesem Planeten';
                    new_el2.onclick = new Function('return fast_action(this, "besiedeln", '+system_split[0]+', '+system_split[1]+', '+i+');');
                    new_el2.appendChild(document.createTextNode('Besiedeln'));
                }
<?php
        }
        if($me->permissionToAct() && $me->getItemLevel('S3', 'schiffe') > 0)
        {
?>
                // Sammeln
                if(sinfo[i]['truemmerfeld'])
                {
                    new_td.firstChild.appendChild(new_el1 = document.createElement('li'));
                    new_el1.className = 'c-truemmerfeld';
                    new_el1.appendChild(new_el2 = document.createElement('a'));
                    new_el2.href = 'flotten.php?action=sammeln&action_galaxy='+encodeURIComponent(system_split[0])+'&action_system='+encodeURIComponent(system_split[1])+'&action_planet='+encodeURIComponent(i)+'&<?php echourlencode(session_name()).'='.urlencode(session_id())?>';
                    new_el2.title = 'Schicken Sie ausreichend Sammler zu diesem Trümmerfeld.';
                    new_el2.onclick = new Function('return fast_action(this, "sammeln", '+system_split[0]+', '+system_split[1]+', '+i+');');
                    new_el2.appendChild(document.createTextNode('Sammeln'));
                }
<?php
        }
?>
            }
            new_tr.appendChild(new_td);
        }

        current_system = parseInt(system_split[1]);
        get_systems_around();
        document.getElementById('current-system-number').value = current_system;
        document.getElementById('galaxy-prev-link').href = 'karte.php?galaxy=<?php echourlencode($prev_galaxy)?>&system='+encodeURIComponent(current_system)+'&<?php echourlencode(session_name()).'='.urlencode(session_id())?>';
        document.getElementById('galaxy-next-link').href = 'karte.php?galaxy=<?php echourlencode($next_galaxy)?>&system='+encodeURIComponent(current_system)+'&<?php echourlencode(session_name()).'='.urlencode(session_id())?>';
        document.getElementById('system-prev-link').href = 'karte.php?galaxy=<?php echourlencode($galaxy_n)?>&system='+encodeURIComponent(prev_system)+'&<?php echourlencode(session_name()).'='.urlencode(session_id())?>';
        document.getElementById('system-next-link').href = 'karte.php?galaxy=<?php echourlencode($galaxy_n)?>&system='+encodeURIComponent(next_system)+'&<?php echourlencode(session_name()).'='.urlencode(session_id())?>';
    }

    function get_systems_around()
    {
        next_system = get_system_after(current_system);
        prev_system = get_system_before(current_system);

        var has_to_preload = false;
        var to_preload = new Object();
        if(typeof preloaded_systems['<?php echo$galaxy_n?>:'+current_system] == 'undefined' && !preloading_systems['<?php echo$galaxy_n?>:'+current_system])
            has_to_preload = true;
        to_preload['<?php echo$galaxy_n?>:'+current_system] = true;

        var next_system2 = current_system;
        var prev_system2 = current_system;
        for(i=0; i<max_preload; i++)
        {
            next_system2 = get_system_after(next_system2);
            prev_system2 = get_system_before(prev_system2);
            if(i<min_preload && typeof preloaded_systems['<?php echo$galaxy_n?>:'+next_system2] == 'undefined' && !preloading_systems['<?php echo$galaxy_n?>:'+next_system2])
                has_to_preload = true;
            to_preload['<?php echo$galaxy_n?>:'+next_system2] = true;

            if(i<min_preload && typeof preloaded_systems['<?php echo$galaxy_n?>:'+prev_system2] == 'undefined' && !preloading_systems['<?php echo$galaxy_n?>:'+prev_system2])
                has_to_preload = true;
            to_preload['<?php echo$galaxy_n?>:'+prev_system2] = true;
        }

        to_preload2 = new Array();
        for(i in to_preload)
            to_preload2.push(i);

        if(has_to_preload)
        {
            preload_systems(to_preload2);
            for(i in preloaded_systems)
            {
                if(!to_preload[i])
                    delete preloaded_systems[i];
            }
        }
    }
/*

    window.onload = get_systems_around;

*/

    function get_system_after(which_system)
    {
        new_system = which_system+1;
        if(new_system > <?php echo$galaxy->getSystemsCount()?>)
            new_system = 1;
        return new_system;
    }

    function get_system_before(which_system)
    {
        new_system = which_system-1;
        if(new_system < 1)
            new_system = <?php echo$galaxy->getSystemsCount()?>;
        return new_system;
    }

/*    
    function sw_next_system()
    {
        change_system('<?php echo$galaxy_n?>:'+next_system);
        return false;
    }

    function sw_prev_system()
    {
        change_system('<?php echo$galaxy_n?>:'+prev_system);
        return false;
    }
*/    
// ]]>
</script>
<?php
    }
?>
<h3>Karte <span class="karte-koords">(<span id="sys"><?php echoutf8_htmlentities($galaxy_n)?>:<?php echoutf8_htmlentities($system_n)?></span>)</span></h3>
<form action="karte.php" method="get" class="karte-wahl"<?php if($me->checkSetting('ajax')){?> onsubmit="if(document.getElementById('current-galaxy-number').value=='<?php echo$galaxy_n?>'){change_system('<?php echo$galaxy_n?>:'+document.getElementById('current-system-number').value);return false;}"<?php }?>>
    <fieldset class="karte-galaxiewahl">
        <legend>Galaxie</legend>
        <ul>
            <li><a href="karte.php?galaxy=<?php echohtmlentities(urlencode($prev_galaxy))?>&amp;system=<?php echohtmlentities(urlencode($system_n))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" tabindex="6" accesskey="u" title="[U]" id="galaxy-prev-link">Vorige</a></li>
            <li><input type="text" name="galaxy" value="<?php echoutf8_htmlentities($galaxy_n)?>" tabindex="1" id="current-galaxy-number" /></li>
            <li><a href="karte.php?galaxy=<?php echohtmlentities(urlencode($next_galaxy))?>&amp;system=<?php echohtmlentities(urlencode($system_n))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" tabindex="5" accesskey="x" title="[X]" id="galaxy-next-link">Nächste</a></li>
        </ul>
    </fieldset>
    <fieldset class="karte-systemwahl">
        <legend>System</legend>
        <ul>
            <li><a href="karte.php?galaxy=<?php echohtmlentities(urlencode($galaxy_n))?>&amp;system=<?php echohtmlentities(urlencode($prev_system))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" tabindex="4" rel="prev" accesskey="o" id="system-prev-link"<?php if($me->checkSetting('ajax')){?> onclick="return sw_prev_system();"<?php }?>>V<kbd>o</kbd>rige</a></li>
            <li><input type="text" name="system" value="<?php echoutf8_htmlentities($system_n)?>" tabindex="2" id="current-system-number" /></li>
            <li><a href="karte.php?galaxy=<?php echohtmlentities(urlencode($galaxy_n))?>&amp;system=<?php echohtmlentities(urlencode($next_system))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" tabindex="3" rel="next" accesskey="n" id="system-next-link"<?php if($me->checkSetting('ajax')){?> onclick="return sw_next_system();"<?php }?>><kbd>N</kbd>ächste</a></li>
        </ul>
    </fieldset>
    <div class="karte-wahl-absenden">
        <button type="submit" tabindex="7" accesskey="w"><kbd>W</kbd>echseln</button><input type="hidden" name="<?php echohtmlentities(session_name())?>" value="<?php echohtmlentities(session_id())?>" />
    </div>
</form>
<table class="karte-system">
    <thead>
        <tr>
            <th class="c-planet">Planet</th>
            <th class="c-name">Name (Eigentümer)</th>
            <th class="c-aktionen">Aktionen</th>
        </tr>
    </thead>
    <tbody id="karte-system">
<?php
    $planets_count = $galaxy->getPlanetsCount($system_n);
    for($i=1; $i <= $planets_count; $i++)
    {
        $planet = array(false, $galaxy->getPlanetOwner($system_n, $i), $galaxy->getPlanetName($system_n, $i), $galaxy->getPlanetOwnerAlliance($system_n, $i), $galaxy->getPlanetOwnerFlag($system_n, $i));
        $class = $galaxy->getPlanetClass($system_n, $i);

        $that_uname = $planet[1];

        if($planet[1])
        {
            if($that_uname == $_SESSION['username'])
                $class2 = 'eigen';
            elseif($me->isVerbuendet($that_uname))
                $class2 = 'verbuendet';
            else
                $class2 = 'fremd';
            if($planet[4] == 'U')
                $class2 .= ' urlaub';
            elseif($planet[4] == 'g')
                $class2 .= ' gesperrt';
        }
        else
            $class2 = 'leer';
?>
        <tr class="<?php echo$class2?> planet_<?php echo$class?>">
<?php
        $truemmerfeld = truemmerfeld::get($galaxy_n, $system_n, $i);
        if($truemmerfeld !== false && array_sum($truemmerfeld) > 0)
            $tf_string = ' <abbr title="Trümmerfeld: '.ths($truemmerfeld[0]).'&nbsp;Carbon, '.ths($truemmerfeld[1]).'&nbsp;Aluminium, '.ths($truemmerfeld[2]).'&nbsp;Wolfram, '.ths($truemmerfeld[3]).'&nbsp;Radium">T</abbr>';
        else
            $tf_string = '';
?>
            <td class="c-planet"><?php echoutf8_htmlentities($i)?><?php echo$tf_string?></td>
<?php
        if($planet[1])
        {
?>
            <td class="c-name"><?php if($planet[3]){?><span class="allianz<?php echo($planet[3] == $me->allianceTag()) ? ' verbuendet' : ''?>">[<a href="help/allianceinfo.php?alliance=<?php echohtmlentities(urlencode($planet[3]))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Informationen zu dieser Allianz anzeigen"><?php echoutf8_htmlentities($planet[3])?></a>]</span> <?php }?><?php echoutf8_htmlentities($planet[2])?> <span class="playername">(<a href="help/playerinfo.php?player=<?php echohtmlentities(urlencode($that_uname))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Informationen zu diesem Spieler anzeigen"><?php echoutf8_htmlentities($that_uname)?></a><?php echo$planet[4] ? ' ('.htmlspecialchars($planet[4]).')' : ''?>)</span></td>
<?php
        }
        else
        {
?>
            <td class="c-name"></td>
<?php
        }

        $show_sammeln = ($me->permissionToAct() && $me->getItemLevel('S3', 'schiffe') > 0 && array_sum($truemmerfeld) > 0);
?>
            <td class="c-aktionen">
                <ul>
                    <li class="c-koordinaten-verwenden"><a href="flotten.php?action_galaxy=<?php echohtmlentities(urlencode($galaxy_n))?>&amp;action_system=<?php echohtmlentities(urlencode($system_n))?>&amp;action_planet=<?php echohtmlentities(urlencode($i))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Die Koordinaten dieses Planeten ins Flottenmenü einsetzen">Koordinaten verwenden</a></li>
<?php
        if(!in_array($galaxy_n.':'.$system_n.':'.$i, $me->getPosShortcutsList()))
        {
?>
                    <li class="c-lesezeichen"><a href="flotten.php?action=shortcut&amp;action_galaxy=<?php echohtmlentities(urlencode($galaxy_n))?>&amp;action_system=<?php echohtmlentities(urlencode($system_n))?>&amp;action_planet=<?php echohtmlentities(urlencode($i))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Die Koordinaten dieses Planeten zu den Lesezeichen hinzufügen"<?php if($me->checkSetting('ajax')){?> onclick="return fast_action(this, 'shortcut', <?php echo$galaxy_n?>, <?php echo$system_n?>, <?php echo$i?>);"<?php }?>>Lesezeichen</a></li>
<?php
        }

        if($that_uname != $_SESSION['username'])
        {
            if($planet[4] != 'U' && $me->permissionToAct() && $me->getItemLevel('S5', 'schiffe') > 0)
            {
?>
                    <li class="c-spionieren"><a href="flotten.php?action=spionage&amp;action_galaxy=<?php echohtmlentities(urlencode($galaxy_n))?>&amp;action_system=<?php echohtmlentities(urlencode($system_n))?>&amp;action_planet=<?php echohtmlentities(urlencode($i))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Spionieren Sie diesen Planeten aus"<?php if($me->checkSetting('ajax')){?> onclick="return fast_action(this, 'spionage', <?php echo$galaxy_n?>, <?php echo$system_n?>, <?php echo$i?>);"<?php }?>>Spionieren</a></li>
<?php
            }

            if($planet[1])
            {
?>
                    <li class="c-nachricht"><a href="nachrichten.php?to=<?php echohtmlentities(urlencode($that_uname))?>&amp;<?php echohtmlentities(session_name().'='.urlencode(session_id()))?>" title="Schreiben Sie diesem Spieler eine Nachricht">Nachricht</a></li>
<?php
            }

            if(!$planet[1] && $me->permissionToAct() && $me->checkPlanetCount() && $me->getItemLevel('S6', 'schiffe') > 0)
            {
?>
                    <li class="c-besiedeln"><a href="flotten.php?action=besiedeln&amp;action_galaxy=<?php echohtmlentities(urlencode($galaxy_n))?>&amp;action_system=<?php echohtmlentities(urlencode($system_n))?>&amp;action_planet=<?php echohtmlentities(urlencode($i))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Schicken Sie ein Besiedelungsschiff zu diesem Planeten"<?php if($me->checkSetting('ajax')){?>  onclick="return fast_action(this, 'besiedeln', <?php echo$galaxy_n?>, <?php echo$system_n?>, <?php echo$i?>);"<?php }?>>Besiedeln</a></li>
<?php
            }
        }

        if($show_sammeln)
        {
?>
                    <li class="c-truemmerfeld"><a href="flotten.php?action=sammeln&amp;action_galaxy=<?php echohtmlentities(urlencode($galaxy_n))?>&amp;action_system=<?php echohtmlentities(urlencode($system_n))?>&amp;action_planet=<?php echohtmlentities(urlencode($i))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Schicken Sie ausreichend Sammler zu diesem Trümmerfeld"<?php if($me->checkSetting('ajax')){?>  onclick="return fast_action(this, 'sammeln', <?php echo$galaxy_n?>, <?php echo$system_n?>, <?php echo$i?>);"<?php }?>>Trümmerfeld</a></li>
<?php
        }
?>
                </ul>
            </td>
        </tr>
<?php
    }
?>
    </tbody>
</table>
<?php
    login_gui::html_foot();
?>
