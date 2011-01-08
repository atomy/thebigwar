<?php
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd()."/..";
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require( $_SERVER['DOCUMENT_ROOT'].'/login/scripts/include.php' );

    if(!isset($_GET['action']))
        $_GET['action'] = false;

    login_gui::html_head();

    switch($_GET['action'])
    {
        case 'handel':
            if(!isset($_GET['id'])) $flotten_id = false;
            else $flotten_id = $_GET['id'];

            $fleet = Classes::Fleet($_GET['id']);
            if(!$fleet->getStatus()) $flotten_id = false;
            $flotten_id = $fleet->getName();

            $planet_key = $me->getPlanetByPos($fleet->getCurrentTarget());
            $type = $fleet->getCurrentType();

            if($planet_key === false || $type != '4' || $fleet->isFlyingBack())
                $flotten_id = false;

            if(!$flotten_id)
            {
?>
<p class="error">Ungültiger Transport ausgewählt.</p>
<?php
                login_gui::html_foot();
                exit();
            }

            $active_planet = $me->getActivePlanet();
            $me->setActivePlanet($planet_key);
            $available_ress = $me->getRess();
            $available_robs = array();
            foreach($me->getItemsList('roboter') as $id)
                $available_robs[$id] = $me->getItemLevel($id);
?>
<h2 id="handel">Handel</h2>
<p>Die Handelsfunktion ermöglicht es Ihnen, herannahenden Transporten Rohstoffe  mit auf den Weg zu geben, ohne dass Sie dazu einen zusätzlichen Transport starten müssen.</p>
<?php
            foreach($fleet->getUsersList() as $username)
            {
                $verb = ($username);

                if($username == $_SESSION['username']) $class = 'eigen';
                elseif($verb) $class = 'verbuendet';
                else $class = 'fremd';
?>
<form action="flotten_actions.php?action=handel&amp;id=<?php echo htmlentities(urlencode($_GET['id']).'&'.urlencode(session_name()).'='.urlencode(session_id()))?>" method="post" class="handel <?php echo $class?>">
    <fieldset>
        <legend><a href="help/playerinfo.php?player=<?php echo htmlentities(urlencode($username).'&'.urlencode(session_name()).'='.urlencode(session_id()))?>" title="Informationen zu diesem Spieler anzeigen"><?php echo utf8_htmlentities($username)?></a></legend>
<?php
                $trans = $fleet->getTransportCapacity($username);
                $handel = $fleet->getHandel($username);
                $remaining_trans = array($trans[0]-array_sum($handel[0]), $trans[1]-array_sum($handel[1]));

                if(isset($_POST['handel_username']) && $_POST['handel_username'] == $username && isset($_POST['handel']) && is_array($_POST['handel']))
                {
                    if(!isset($_POST['handel_type']) || ($_POST['handel_type'] != 'set' && $_POST['handel_type'] != 'add'))
                        $type = ($verb ? 'set' : 'add');
                    else $type = $_POST['handel_type'];

                    $new_handel = array(array(0,0,0,0,0),array());
                    if(isset($_POST['handel'][0]) && is_array($_POST['handel'][0]))
                    {
                        if(isset($_POST['handel'][0][0])) $new_handel[0][0] = $_POST['handel'][0][0];
                        if(isset($_POST['handel'][0][1])) $new_handel[0][1] = $_POST['handel'][0][1];
                        if(isset($_POST['handel'][0][2])) $new_handel[0][2] = $_POST['handel'][0][2];
                        if(isset($_POST['handel'][0][3])) $new_handel[0][3] = $_POST['handel'][0][3];
                        if(isset($_POST['handel'][0][4])) $new_handel[0][4] = $_POST['handel'][0][4];
                    }
                    if(isset($_POST['handel'][1]) && is_array($_POST['handel'][1]))
                        $new_handel[1] = $_POST['handel'][1];

                    foreach($new_handel[0] as $i=>$v)
                    {
                        $av = $available_ress[$i];
                        if($type == 'set')
                        {
                            $add = $handel[0][$i];
                            if(!$verb && $v < $add) $v = $add;
                            $av += $add;
                        }

                        if($v > $av) $v = $av;
                        $new_handel[0][$i] = $v;
                    }
                    foreach($new_handel[1] as $i=>$v)
                    {
                        if(!isset($available_robs[$i]))
                        {
                            unset($new_handel[1][$i]);
                            continue;
                        }
                        $av = $available_robs[$i];
                        if($type == 'set')
                        {
                            $add = 0;
                            if(isset($handel[1][$i])) $add = $handel[1][$i];
                            if(!$verb && $v < $add) $v = $add;
                            $av += $add;
                        }
                        if($v > $av) $v = $av;
                        $new_handel[1][$i] = $v;
                    }

                    if($type == 'set') $max = $trans;
                    else $max = $remaining_trans;

                    $new_handel = array(fit_to_max($new_handel[0], $max[0]), fit_to_max($new_handel[1], $max[1]));

                    if($type == 'set') $status = $fleet->setHandel($_POST['handel_username'], $new_handel[0], $new_handel[1]);
                    else $status = $fleet->addHandel($_POST['handel_username'], $new_handel[0], $new_handel[1]);
                    if($status)
                    {
                        # Gueter vom Planeten abziehen
                        if($type == 'set')
                        {
                            $ress_sub = array($new_handel[0][0]-$handel[0][0],
                                              $new_handel[0][1]-$handel[0][1],
                                              $new_handel[0][2]-$handel[0][2],
                                              $new_handel[0][3]-$handel[0][3],
                                              $new_handel[0][4]-$handel[0][4]);
                            $rob_sub = array();
                            foreach($me->getItemsList('roboter') as $id)
                            {
                                $old = $new = 0;
                                if(isset($handel[1][$id])) $old = $handel[1][$id];
                                if(isset($new_handel[1][$id])) $new = $new_handel[1][$id];
                                if($new != $old)
                                    $rob_sub[$id] = $new-$old;
                            }
                        }
                        else list($ress_sub, $rob_sub) = $new_handel;

                        #$me->subtractRess($ress_sub, false);
                        $available_ress = $me->getRess();
                        foreach($rob_sub as $id=>$sub)
                        {
                            $available_robs[$id] -= $sub;
                            $me->changeItemLevel($id, -$sub, 'roboter');
                        }

                        if($type == 'set') $handel = $new_handel;
                        else
                        {
                            $handel[0][0] += $new_handel[0][0];
                            $handel[0][1] += $new_handel[0][1];
                            $handel[0][2] += $new_handel[0][2];
                            $handel[0][3] += $new_handel[0][3];
                            $handel[0][4] += $new_handel[0][4];
                            foreach($new_handel[1] as $k=>$v)
                                $handel[1][$k] += $v;
                        }
                        $remaining_trans = array($trans[0]-array_sum($handel[0]), $trans[1]-array_sum($handel[1]));
                    }
                }

                if($verb)
                {
                    $mess1 = 'Sie können das Handelsangebot zu diesem Spieler ändern.';
                    if($username == $_SESSION['username']) $mess2 = 'Die Flotte hat Platz für %1$s Tonnen Rohstoffe (%3$s verbleibend).';
                    else $mess2 = 'Die Flotte hat Platz für %1$s Tonnen Rohstoffe (%3$s verbleibend).';
                    $input_name = 'set';
                    $value = '%u';
                    $disabled = '';
                    $show_submit = true;
                }
                else
                {
                    $mess1 = 'Sie können das Handelsangebot für diesen Spieler ändern.';
                    $mess2 = 'Es verbleibt Platz für %3$s Tonnen Rohstoffe.';
                    $input_name = 'set';
                    $value = '%u';
                    if($remaining_trans[0] == 0)
                    {
                        $disabled = ' disabled="disabled"';
                        $show_submit = false;
                    }
                    else
                    {
                        $disabled = '';
                        $show_submit = true;
                    }
                }
?>
        <input type="hidden" name="handel_username" value="<?php echo utf8_htmlentities($username)?>" />
        <input type="hidden" name="handel_type" value="<?php echo $input_name?>" />
        <p><?php echo htmlspecialchars($mess1)?></p>
        <p><?php printf($mess2, ths($trans[0]), ths($trans[1]), ths($remaining_trans[0]), ths($remaining_trans[1]))?></p>
        <table>
            <thead>
                <tr>
                    <th class="c-gut">Gut</th>
                    <th class="c-einlagern">Einlagern</th>
<?php
                if(!$verb)
                {
?>
                    <th class="c-bereits-eingelagert">Bereits eingelagert</th>
<?php
                }
?>
                    <th class="c-verfuegbar">Verfügbar</th>
                </tr>
            </thead>
            <tbody>
<?php
                if($trans[0] > 0)
                {
?>
                <tr class="c-carbon">
                    <th class="c-gut">Carbon</th>
                    <td class="c-einlagern"><input type="text" name="handel[0][0]" value="<?php printf($value, $handel[0][0])?>"<?php echo $disabled?> id="carbon"/></td>
<?php
                    if(!$verb)
                    {
?>
                    <td class="c-bereits-eingelagert"><?php echo ths($handel[0][0])?></td>
<?php
                    }
?>
                    <td class="c-verfuegbar"><?php echo ths($available_ress[0])?><input type="button" onclick='document.getElementById("carbon").value = "<?php echo ereg_replace("&nbsp;","",ths($available_ress[0]));?>"' value="max"/ ondblclick='document.getElementById("carbon").value = "0"'/></td>
                </tr>
                <tr class="c-aluminium">
                    <th class="c-gut">Aluminium</th>
                    <td class="c-einlagern"><input type="text" name="handel[0][1]" value="<?php printf($value, $handel[0][1])?>"<?php echo $disabled?> id="aluminium"/></td>
<?php
                    if(!$verb)
                    {
?>
                    <td class="c-bereits-eingelagert"><?php echo ths($handel[0][1])?></td>
<?php
                    }
?>
                    <td class="c-verfuegbar"><?php echo ths($available_ress[1])?><input type="button" onclick='document.getElementById("aluminium").value = "<?php echo ereg_replace("&nbsp;","",ths($available_ress[1]));?>"' value="max" ondblclick='document.getElementById("aluminium").value = "0"'/></td>
                </tr>
                <tr class="c-wolfram">
                    <th class="c-gut">Wolfram</th>
                    <td class="c-einlagern"><input type="text" name="handel[0][2]" value="<?php printf($value, $handel[0][2])?>"<?php echo $disabled?> id="wolfram"/></td>
<?php
                    if(!$verb)
                    {
?>
                    <td class="c-bereits-eingelagert"><?php echo ths($handel[0][2])?></td>
<?php
                    }
?>
                    <td class="c-verfuegbar"><?php echo ths($available_ress[2])?><input type="button" onclick='document.getElementById("wolfram").value = "<?php echo ereg_replace("&nbsp;","",ths($available_ress[2]));?>"' value="max" ondblclick='document.getElementById("wolfram").value = "0"'/></td>
                </tr>
                <tr class="c-radium">
                    <th class="c-gut">Radium</th>
                    <td class="c-einlagern"><input type="text" name="handel[0][3]" value="<?php printf($value, $handel[0][3])?>"<?php echo $disabled?> id="radium"/></td>
<?php
                    if(!$verb)
                    {
?>
                    <td class="c-bereits-eingelagert"><?php echo ths($handel[0][3])?></td>
<?php
                    }
?>
                    <td class="c-verfuegbar"><?php echo ths($available_ress[3])?><input type="button" onclick='document.getElementById("radium").value = "<?php echo ereg_replace("&nbsp;","",ths($available_ress[3]));?>"' value="max"/ ondblclick='document.getElementById("radium").value = "0"'/></td>
                </tr>
                <tr class="c-tritium">
                    <th class="c-gut">Tritium</th>
                    <td class="c-einlagern"><input type="text" name="handel[0][4]" value="<?php printf($value, $handel[0][4])?>"<?php echo $disabled?> id="tritium"/></td>
<?php
                    if(!$verb)
                    {
?>
                    <td class="c-bereits-eingelagert"><?php echo ths($handel[0][4])?></td>
<?php
                    }
?>
                    <td class="c-verfuegbar"><?php echo ths($available_ress[4])?><input type="button" onclick='document.getElementById("tritium").value = "<?php echo ereg_replace("&nbsp;","",ths($available_ress[4]));?>"' value="max" ondblclick='document.getElementById("tritium").value = "0"'/></td>
                </tr>
<?php
                }
                if($username == $_SESSION['username'] && $trans[1] > 0)
                {
                    foreach($me->getItemsList('roboter') as $id)
                    {
                        $item_info = $me->getItemInfo($id, 'roboter');
                        $h = 0;
                        if(isset($handel[1][$id])) $h = $handel[1][$id];
?>
                <tr class="c-ro-<?php echo utf8_htmlentities($id)?>">
                    <th class="c-gut"><?php echo utf8_htmlentities($item_info['name'])?></th>
                    <td class="c-einlagern"><input type="text" name="handel[1][<?php echo $id?>]" value="<?php echo utf8_htmlentities($h)?>" /></td>
                    <td class="c-verfuegbar"><?php echo ths($available_robs[$id])?></td>
                </tr>
<?php
                    }
                }
?>
            </tbody>
<?php
                if($show_submit)
                {
?>
            <tfoot>
                <tr>
                    <td colspan="<?php echo 3-$verb?>"><button type="submit">Handel ändern</button></td>
                </tr>
            </tfoot>
<?php
                }
?>
        </table>
    </fieldset>
</form>
<?php
            }

            $me->setActivePlanet($active_planet);

            break;

        case 'shortcuts':
            if(isset($_GET['up'])) $me->movePosShortcutUp($_GET['up']);
            if(isset($_GET['down'])) $me->movePosShortcutDown($_GET['down']);
            if(isset($_GET['remove'])) $me->removePosShortcut($_GET['remove']);
            $shortcuts = $me->getPosShortcutsList();
            $count = count($shortcuts);
            if($count <= 0)
            {
?>
<p class="nothingtodo">
    Sie haben keine Planetenlesezeichen gespeichert. In der Karte können Sie Lesezeichen anlegen.
</p>
<?php
            }
            else
            {
?>
<fieldset>
    <legend>Lesezeichen verwalten</legend>
    <ul class="shortcuts-verwalten">
<?php
                $i = 0;
                foreach($shortcuts as $shortcut)
                {
                    $s_pos = explode(':', $shortcut);
                    $galaxy_obj = Classes::Galaxy($s_pos[0]);
                    $owner = $galaxy_obj->getPlanetOwner($s_pos[1], $s_pos[2]);
                    $s = $shortcut.': ';
                    if($owner)
                    {
                        $s .= $galaxy_obj->getPlanetName($s_pos[1], $s_pos[2]).' (';
                        $alliance = $galaxy_obj->getPlanetOwnerAlliance($s_pos[1], $s_pos[2]);
                        if($alliance) $s .= '['.$alliance.'] ';
                        $s .= $owner.')';
                    }
                    else $s .= '[unbesiedelt]';
?>
        <li><?php echo htmlspecialchars($s)?> <span class="aktionen"><?php if($i>0){?> &ndash; <a href="flotten_actions.php?action=shortcuts&amp;up=<?php echo htmlentities(urlencode($shortcut))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.session_id())?>" class="hoch">[Hoch]</a><?php } if($i<$count-1){?> &ndash; <a href="flotten_actions.php?action=shortcuts&amp;down=<?php echo htmlentities(urlencode($shortcut))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.session_id())?>" class="runter">[Runter]</a><?php }?> &ndash; <a href="flotten_actions.php?action=shortcuts&amp;remove=<?php echo htmlentities(urlencode($shortcut))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.session_id())?>" class="loeschen">[Löschen]</a></span></li>
<?php
                    $i++;
                }
?>
    </ul>
</fieldset>
<?php
            }
            break;
            
                            case 'buendnisangriff':
                            {
                                    if(!isset($_GET['id'])) $flotten_id = false;
                                    else $flotten_id = $_GET['id'];
            
                                    if($flotten_id)
                                    {
                                            $fleet = Classes::Fleet($flotten_id);
                                            if(!$fleet->getStatus()) $flotten_id = false;
                                            $flotten_id = $fleet->getName();
            
                                            if($flotten_id && ($fleet->getCurrentType() != 3 || $fleet->isFlyingBack() || array_search($me->getName(), $fleet->getUsersList()) !== 0))
                                                    $flotten_id = false;
                                    }
            
                                    if(!$flotten_id)
                                    {
            ?>
            <p class="error">Ung�ltigen Angriff ausgew�hlt.</p>
            <?php
                                            login_gui::html_foot();
                                            exit();
                                    }
            
                                    if(isset($_POST["fleet_passwd"]))
                                    {
                                            $passwd = $me->getFleetPasswd($flotten_id);
                                            $_POST["fleet_passwd"] = trim($_POST["fleet_passwd"]);
                                            if($_POST["fleet_passwd"] != $passwd && $me->resolveFleetPasswd($_POST["fleet_passwd"]) !== null)
                                            {
            ?>
            <p class="error">Dieses Passwort wurde schon für eine andere Flotte verwendet.</p>
            <?php
                                            }
                                            elseif(!$me->changeFleetPasswd($flotten_id, trim($_POST["fleet_passwd"])))
                                            {
            ?>
            <p class="error">Das Passwort konnte nicht geändert werden.</p>
            <?php
                                            }
                                    }
                                    $passwd = $me->getFleetPasswd($flotten_id);
            ?>
            <p class="buendnisangriff-beschreibung-1">Hier können Sie der ausgewählten Flotte ein Flottenpasswort zuweisen, welches es anderen Spielern ermöglicht, Ihrem Angriff eigene Flotten beizusteuern. Möchte ein anderer Spieler dem Flottenverbund beitreten, so muss er im Flottenmenü Ihren Benutzernamen in Verbindung mit dem hier festgelegten Passwort angeben. Übermitteln Sie ihm hierzu das Passwort selbst, zum Beispiel durch eine Nachricht.</p>
            <p class="buendnisangriff-beschreibung-2">Beachten Sie, dass ein Spieler dem Flottenverbund nicht mehr beitreten kann, wenn seine Flugzeit zum ausgewählten Ziel länger ist als 40% der verbleibenden Flugzeit der Verbandsflotte.</p>
            <p class="buendnisangriff-beschreibung-3">Wenn hier kein Passwort eingetragen ist, ist die Flottenverbundfunktion für diese Flotte deaktiviert.</p>
            <form action="flotten_actions.php?action=buendnisangriff&amp;id=<?php echo htmlspecialchars(urlencode($_GET['id']).'&'.urlencode(session_name()).'='.urlencode(session_id()))?>" method="post" class="buendnisangriff">
                    <dl>
                            <dt><label for="i-flottenpasswort">Flottenpasswort</label></dt>
                            <dd><input type="text" name="fleet_passwd"<?php if($passwd !== null){?> value="<?php echo htmlspecialchars($passwd)?>"<?php }?> /></dd>
                    </dl>
                    <div><button type="submit">Speichern</button></div>
            </form>
            <?php
                                    break;
                            }
        default:
        {
?>
<p class="error">Ungültige Aktion.</p>
<?php
            break;
    }
    }

    login_gui::html_foot();
?>
