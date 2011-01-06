<?php
    require_once( '../include/config_inc.php' );
    require( TBW_ROOT.'login/scripts/include.php' );

    login_gui::html_head();

    if(isset($_GET['to']))
    {
        # Neue Nachricht verfassen
?>
<h2><a href="nachrichten.php?<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" title="Zurück zur Nachrichtenkategorienübersicht [W]" accesskey="w" tabindex="5">Nachrichten</a></h2>
<?php
        $error = '';
        $show_form = true;

        if(isset($_POST['empfaenger']) && isset($_POST['betreff']) && isset($_POST['inhalt']))
        {
            # Nachricht versenden, versuchen
            if(!$me->userLocked())
                $_POST['empfaenger'] = trim($_POST['empfaenger']);
            else 
                $_POST['empfaenger'] = GLOBAL_SGOACCNAME;
            
            if( !User::userExists( $_POST['empfaenger'] ) )
                $error = 'Der Empfänger, den Sie eingegeben haben, existiert nicht.';
               else if( strtolower( strtolower( $me->getName() ) == GLOBAL_DEMOACCNAME ) )
                    $error = 'Demo-Account kann keine Nachrichten verfassen.';
            else if( strtolower( $_POST['empfaenger'] ) == GLOBAL_DEMOACCNAME )
                $error = 'Demo-Account kann keine Nachrichten empfangen.';
            else if( strtolower( $_POST['empfaenger'] ) == strtolower($_SESSION['username']))
                $error = 'Sie können sich nicht selbst eine Nachricht schicken.';
            else if( strlen( $_POST['betreff'] ) > 30 )
                $error = 'Der Betreff darf maximal 30 Bytes lang sein.';
            else if( strlen( $_POST['inhalt'] ) <= 0 )
                $error = 'Sie müssen eine Nachricht eingeben.';
            else
            {
                # Nachricht versenden
                $message = Classes::Message();
                
                if( !$message->create() )
                    $error = 'Datenbankfehler &#40;1040&#41;';
                else
                {
                    $message->text( $_POST['inhalt'] );
                    $message->subject( $_POST['betreff'] );
                    $message->from( $_SESSION['username'] );
                    $message->to($_POST['empfaenger'] );
                    $message->addUser( User::resolveName( $_POST['empfaenger'] ), 6 );
                    $message->addUser( $_SESSION['username'], 8 );
?>
<p class="successful">
    Die Nachricht wurde erfolgreich versandt.
</p>
<?php
                    $show_form = false;
                    unset( $message );
                }
            }
        }

        if(trim($error) != '')
        {
?>
<p class="error">
    <?php echo utf8_htmlentities($error)."\n"?>
</p>
<?php
        }

        if($show_form)
        {
            # Formular zum Absenden anzeigen
?>
<form action="nachrichten.php?to=&amp;<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" method="post" class="nachrichten-neu" onsubmit="this.setAttribute('onsubmit', 'return confirm(\'Doppelklickschutz: Sie haben ein zweites Mal auf \u201eAbsenden\u201c geklickt. Dadurch wird die Nachricht auch ein zweites Mal abgeschickt. Sind Sie sicher, dass Sie diese Aktion durchführen wollen?\');');">
    <fieldset>
        <legend>Nachricht verfassen</legend>
        <dl>
            <dt class="c-empfaenger"><label for="empfaenger-input">Empfänger</label></dt>
<?php
        $empfaenger = $_GET['to'];
        
        if(isset($_POST['empfaenger']))
            $empfaenger = $_POST['empfaenger'];

        $betreff = '';
        if(isset($_GET['subject']))
            $betreff = $_GET['subject'];
        if(isset($_POST['betreff']))
            $betreff = $_POST['betreff'];
?>
            <dd class="c-empfaenger"><input type="text" id="empfaenger-input" name="empfaenger" value="<?php echo utf8_htmlentities($empfaenger)?>" tabindex="1" accesskey="z" title="[Z]" /></dd>

            <dt class="c-betreff"><label for="betreff-input">Betreff</label></dt>
            <dd class="c-betreff"><input type="text" id="betreff-input" name="betreff" value="<?php echo utf8_htmlentities($betreff)?>" maxlength="30" tabindex="2" accesskey="j" title="[J]" /></dd>

            <dt class="c-inhalt"><label for="inhalt-input">Inhalt</label></dt>
            <dd class="c-inhalt"><textarea id="inhalt-input" name="inhalt" cols="50" rows="10" tabindex="3" accesskey="x" title="[X]"><?php echo isset($_POST['inhalt']) ? preg_replace("/[\n\r\t]/e", '\'&#\'.ord(\'$0\').\';\'', utf8_htmlentities($_POST['inhalt'])) : ''?></textarea></dd>
        </dl>
    </fieldset>
    <div><button type="submit" accesskey="n" tabindex="4">Abse<kbd>n</kbd>den</button></div>
</form>
<?php
            if($me->checkSetting('ajax'))
            {
?>
<script type="text/javascript">
    // Autocompletion des Empfaengers
    activate_users_list(document.getElementById('empfaenger-input'));
</script>
<?php
            
            }
        }
    }
    
    elseif(isset($_GET['type']) && isset($message_type_names[$_GET['type']]))
    {
        #$message = Classes::Message();
        #$absender = $message->from();
        # Nachrichtentyp wurde bereits ausgewaehlt, Nachricht oder Nachrichtenliste anzeigen
        if(isset($_GET['message']))
        {
        
            # Nachricht anzeigen
             
?>
<h2><a href="nachrichten.php?<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Zurück zur Nachrichtenkategorienübersicht [W]" tabindex="6" accesskey="w">Nachrichten</a>: <a href="nachrichten.php?type=<?php echo htmlentities(urlencode($_GET['type']))?>&amp;<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" title="Zurück zur Nachrichtenübersicht: <?php echo utf8_htmlentities($message_type_names[$_GET['type']])?> [O]" tabindex="5" accesskey="o"><?php echo utf8_htmlentities($message_type_names[$_GET['type']])?></a></h2>
<?php
                
        if(!$me->checkMessage($_GET['message'], $_GET['type']))
            {
?>
<p class="error">
    Diese Nachricht existiert nicht.
</p>
<?php
            }
            else
            {
                
                $message = Classes::Message($_GET['message']);
                $absender = $message->from();
                if(!$message->getStatus())
                {
?>
<p class="error">
    Datenbankfehler &#40;1041&#41;
</p>
<?php
                }
                if(!$me->userLocked() || $absender == "supergameoperator")
                {
                    # Nachricht kann angezeigt werden
                    $current_status = $me->checkMessageStatus($_GET['message'], $_GET['type']);
                    # Als gelesen markieren
                    if(!isset($_SESSION['admin_username']) && $current_status == 1)
                        $me->setMessageStatus($_GET['message'], $_GET['type'], 0);

                    # Vorige und naechste ungelesene Nachricht bestimmen
                    $unread_prev = false;
                    $unread_next = false;
                    $messages = $me->getMessagesList($_GET['type']);
                    $this_key = array_search($_GET['message'], $messages);
                    for($i=$this_key+1; $i<count($messages); $i++)
                    {
                        if($me->checkMessageStatus($messages[$i], $_GET['type']) == 1)
                        {
                            $unread_next = $messages[$i];
                            break;
                        }
                    }
                    for($i=$this_key-1; $i>=0; $i--)
                    {
                        if($me->checkMessageStatus($messages[$i], $_GET['type']) == 1)
                        {
                            $unread_prev = $messages[$i];
                            break;
                        }
                    }

                    if($unread_next !== false || $unread_prev !== false)
                    {
                        # Vorige und naechste verlinken
?>
<ul class="ungelesene-nachrichten">
<?php
                        if($unread_prev !== false)
                        {
?>
    <li class="c-vorige"><a href="nachrichten.php?type=<?php echo htmlentities(urlencode($_GET['type']))?>&amp;message=<?php echo htmlentities(urlencode($unread_prev))?>&amp;<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" title="Vorige ungelesene Nachricht [U]" accesskey="u" tabindex="4">&larr;</a></li>

<?php
                        }
                        if($unread_next !== false)
                        {
?>
    <li class="c-naechste"><a href="nachrichten.php?type=<?php echo htmlentities(urlencode($_GET['type']))?>&amp;message=<?php echo htmlentities(urlencode($unread_next))?>&amp;<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" title="Nächste ungelesene Nachricht [Q]" accesskey="q" tabindex="3">&rarr;</a></li>
<?php
                        }
?>
</ul>
<?php
                    }
?>
<dl class="nachricht-informationen type-<?php echo utf8_htmlentities($_GET['type'])?><?php echo $message->html() ? ' html' : ''?>">
<?php
                    if(trim($message->from()) != '')
                    {
?>
    <dt class="c-absender">Absender</dt>
    <dd class="c-absender"><a href="help/playerinfo.php?player=<?php echo htmlentities(urlencode($message->from()))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Informationen zu diesem Spieler anzeigen"><?php echo utf8_htmlentities($message->from())?></a></dd>
       <dt class="c-empfaenger">Empfaenger</dt>
     <dd class="c-empfaenger"><a href="help/playerinfo.php?player=<?php echo htmlentities(urlencode($message->to()))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Informationen zu diesem Spieler anzeigen"><?php echo utf8_htmlentities($message->to())?></a></dd>

<?php
                    }
?>
    <dt class="c-betreff">Betreff</dt>
    <dd class="c-betreff"><?php echo utf8_htmlentities($message->subject())?></dd>

    <dt class="c-zeit">Zeit</dt>
    <dd class="c-zeit"><?php echo date('H:i:s, Y-m-d', $message->getTime())?></dd>

    <dt class="c-nachricht">Nachricht</dt>
    <dd class="c-nachricht">
<?php
                    function repl_links($a, $b, $c)
                    {
                        # Haengt bei Links die Session-ID an
                        $url2 = html_entity_decode($b);
                        if(substr($url2, 0, 7) != 'http://')
                        {
                        $url3 = explode('#', $url2);
                        $url3[0] = explode('?', $url3[0]);
                        $url = array($url3[0][0]);
                        if(isset($url3[0][1]))
                            $url[1] = $url3[0][1];
                        else
                            $url[1] = '';
                        if(isset($url3[1]))
                            $url[2] = $url[1];
                        else
                            $url[2] = '';

                        if($url[1] != '')
                            $url[1] .= '&';
                        $url[1] .= urlencode(session_name()).'='.urlencode(session_id());

                        $url2 = $url[0].'?'.$url[1];
                        if($url[2] != '')
                            $url2 .= '#'.$url[2];
                        }

                        return $a.htmlspecialchars($url2).$c;
                    }
                    $text = $message->text();

                    if($message->html())
                    {
                        # Session-ID an Links anhaengen
                        $text = preg_replace('/(<a [^>]*href=")([^"]*)(")/ei', 'repl_links("\\1", "\\2", "\\3")', $text);
                    }
                    print("\t\t".preg_replace("/\r\n|\r|\n/", "\n\t\t", $text));
?>
    </dd>
</dl>
<?php
                    if($_GET['type'] == '7')
                    {
?>
<ul class="nachrichten-verbuendeten-links">
    <li class="c-verbuendete"><a href="verbuendete.php?<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>">Zur Verbündetenseite</a></li>
    <li class="c-allianz"><a href="allianz.php?<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>">Zur Allianzseite</a></li>
</ul>
<?php
                    }

                    if($message->from() != '' && $message->from() != $_SESSION['username'])
                    {
                        # Bei Nachrichten im Postausgang ist die Antwort nicht moeglich
                        $re_betreff = $message->subject();
                        if(substr($re_betreff, 0, 4) != 'Re: ')
                            $re_betreff = 'Re: '.$re_betreff;
?>
<form action="nachrichten.php" method="get" class="nachricht-antworten-formular">
    <div>
        <input type="hidden" name="<?php echo htmlentities(session_name())?>" value="<?php echo htmlentities(session_id())?>" />
        <input type="hidden" name="to" value="<?php echo utf8_htmlentities($message->from())?>" />
        <input type="hidden" name="subject" value="<?php echo utf8_htmlentities($re_betreff)?>" />
        <button type="submit" accesskey="w" tabindex="1">Ant<kbd>w</kbd>orten</button>
    </div>
</form>
<?php
                    }
?>
<form action="nachrichten.php?type=<?php echo htmlentities(urlencode($_GET['type']))?>&amp;<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" method="post" class="nachricht-loeschen-formular">
    <div><input type="hidden" name="message[<?php echo htmlentities($_GET['message'])?>]" value="on" /><input type="submit" name="delete" accesskey="n" tabindex="2" value="Löschen" title="[N]" /> <input type="submit" name="archive" tabindex="3" value="Archivieren" /></div>
</form>
<?php
                    if(isset($_POST['weiterleitung-to']))
                    {
                        $_POST['weiterleitung-to'] = trim($_POST['weiterleitung-to']);

                        if(!User::userExists($_POST['weiterleitung-to']))
                        {
?>
<p class="error">Der Empfänger, den Sie eingegeben haben, existiert nicht.</p>
<?php
                        }
                        elseif($_POST['weiterleitung-to'] == $_SESSION['username'])
                        {
?>
<p class="error">Sie können sich nicht selbst eine Nachricht schicken.</p>
<?php
                        }
                        else
                        {
                            $weiterleitung_message = Classes::Message();
                            if(!$weiterleitung_message->create())
                            {
?>
<p class="error">Datenbankfehler &#40;1042&#41;</p>
<?php
                            }
                            else
                            {
                                $weiterleitung_text = '';
                                if($message->html())
                                    $weiterleitung_text .= "<p class=\"weitergeleitete-nachricht\">\n\t";
                                $weiterleitung_text .= "--- Weitergeleitete Nachricht";
                                if(trim($message->from()) != '')
                                {
                                    $weiterleitung_text .= ", Absender: ";
                                    if($message->html())
                                        $weiterleitung_text .= htmlspecialchars($message->from());
                                    else
                                        $weiterleitung_text .= $message->from();
                                }
                                if($message->getTime())
                                    $weiterleitung_text .= ", Sendezeit: ".date('H:i:s, Y-m-d', $message->getTime());
                                $weiterleitung_text .= " ---\n";
                                if($message->html())
                                    $weiterleitung_text .= "</p>";
                                $weiterleitung_text .= "\n ";
                                $weiterleitung_message->text($weiterleitung_text.$message->rawText());
                                $weiterleitung_message->subject('Fwd: '.$message->subject());
                                $weiterleitung_message->from($_SESSION['username']);
                                $weiterleitung_message->addUser(User::resolveName($_POST['weiterleitung-to']), $_GET['type']);
                                $weiterleitung_message->html($message->html());
?>
<p class="successful">Die Nachricht wurde erfolgreich weitergeleitet.</p>
<?php
                                unset($_POST['weiterleitung-to']);
                                unset($weiterleitung_message);
                            }
                        }
                    }

                    if(isset($_GET['publish']) && $_GET['publish'] && !PublicMessage::publicMessageExists($_GET['message']))
                    {
                        $public_message = Classes::PublicMessage($_GET['message']);
                        $public_message->createFromMessage($message);
                        if($_GET['type'] != 1)
                            $public_message->to($_SESSION['username']);
                        else
                            $public_message->subject('');
                        $public_message->type($_GET['type']);
                        unset($public_message);
                    }

                    if(PublicMessage::publicMessageExists($_GET['message']))
                    {
                        $host = get_default_hostname();
?>
<p id="nachricht-veroeffentlichen">
    Sie können diese Nachricht öffentlich verlinken: <a href="http://<?php echo htmlentities($host.h_root)?>/public_message.php?id=<?php echo htmlentities(urlencode($_GET['message']))?>&amp;database=<?php echo htmlentities(urlencode($_SESSION['database']))?>">http://<?php echo htmlentities($host.h_root)?>/public_message.php?id=<?php echo htmlentities(urlencode($_GET['message']))?>&amp;database=<?php echo htmlentities(urlencode($_SESSION['database']))?></a>
</p>
<?php
                    }
                    else
                    {
?>
<ul id="nachricht-veroeffentlichen">
    <li><a href="nachrichten.php?type=<?php echo htmlentities(urlencode($_GET['type']))?>&amp;message=<?php echo htmlentities(urlencode($_GET['message']))?>&amp;publish=1&amp;<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>#nachricht-veroeffentlichen">Nachricht veröffentlichen</a></li>
</ul>
<?php
                    }
?>
<form action="nachrichten.php?type=<?php echo htmlentities(urlencode($_GET['type']))?>&amp;message=<?php echo htmlentities(urlencode($_GET['message']))?>&amp;<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>#nachricht-weiterleiten-formular" method="post" id="nachricht-weiterleiten-formular" class="nachricht-weiterleiten-formular">
    <fieldset>
        <legend>Nachricht weiterleiten</legend>
        <dl>
            <dt><label for="empfaenger-input">Empfänger</label></dt>
            <dd><input type="text" id="empfaenger-input" name="weiterleitung-to" value="<?php echo isset($_POST['weiterleitung-to']) ? utf8_htmlentities($_POST['weiterleitung-to']) : ''?>" title="[X]" accesskey="x" tabindex="5" /></dd>
        </dl>
        <div><button type="submit" tabindex="6">Weiterleiten</button></div>
    </fieldset>
</form>

<?php
                    if($me->checkSetting('ajax'))
                    {
?>
<script type="text/javascript">
    activate_users_list(document.getElementById('empfaenger-input'));
</script>
<?php
                    }
                }
            
            }
        if($me->userLocked() && $absender != "supergameoperator")
        {
?>
<p class="error">
    Sie können während der Sperre nur die Nachrichten des Supergameoperators lesen. Alle von Ihnen während der Sperre gesendeten Nachrichten gehen automatisch an den Supergameoperator!
</p>
<?php
            }
        }
        else
        {
            # Nachrichtenuebersicht einer Kategorie anzeigen
?>
<h2><a href="nachrichten.php?<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" title="Zurück zur Nachrichtenkategorienübersicht [W]" accesskey="w" tabindex="4">Nachrichten</a>: <?php echo utf8_htmlentities($message_type_names[$_GET['type']])?></h2>
<?php
            $messages_list = $me->getMessagesList($_GET['type']);
            if(count($messages_list) > 0)
            {
                if(isset($_POST['read']) && isset($_POST['message']) && is_array($_POST['message']))
                {
                    # Als gelesen markieren
                    foreach($_POST['message'] as $message_id=>$v)
                        $me->setMessageStatus($message_id, $_GET['type'], 0);
                }
                elseif(isset($_POST['delete']) && isset($_POST['message']) && is_array($_POST['message']))
                {
                    # Loeschen
                    foreach($_POST['message'] as $message_id=>$v)
                        $me->removeMessage($message_id, $_GET['type']);
                }
                elseif(isset($_POST['archive']) && isset($_POST['message']) && is_array($_POST['message']))
                {
                    # Archivieren
                    foreach($_POST['message'] as $message_id=>$v)
                        $me->setMessageStatus($message_id, $_GET['type'], 2);
                }
?>
<script type="text/javascript">
// <![CDATA[
    function toggle_selection()
    {
        var formular = document.getElementById('nachrichten-liste').elements;
        for(var i=0; i<formular.length; i++)
        {
            if(formular[i].checked != undefined)
                formular[i].checked = !formular[i].checked;
        }
    }
// ]]>
</script>
<form action="nachrichten.php?type=<?php echo htmlentities(urlencode($_GET['type']))?>&amp;<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" method="post" class="nachrichten-liste type-<?php echo utf8_htmlentities($_GET['type'])?>" id="nachrichten-liste">
    <table>
        <thead>
            <tr>
                <th class="c-auswaehlen"></th>
                <th class="c-betreff">Betreff</th>
                <th class="c-absender">Absender</th>
                           <th class="c-empfaenger">Empfaenger</th>
                <th class="c-datum">Datum</th>
            </tr>
        </thead>
        <tbody>
<?php
                $tabindex = 5;
                foreach($messages_list as $message_id)
                {
                    $status = $me->checkMessageStatus($message_id, $_GET['type']);
                    $message = Classes::Message($message_id);
                    if(!$message->getStatus())
                    {
                        $me->removeMessage($message_id, $_GET['type']);
                        continue;
                    }
                    if($status === 2) $class = 'archiviert';
                    elseif($status == 1 && $_GET['type'] != 8) $class = 'neu';
                    else $class = 'alt';
?>
            <tr class="<?php echo $class?>">
                <td class="c-auswaehlen"><input type="checkbox" name="message[<?php echo htmlentities($message_id)?>]" tabindex="<?php echo $tabindex++?>" /></td>
                <td class="c-betreff"><a href="nachrichten.php?type=<?php echo htmlentities(urlencode($_GET['type']))?>&amp;message=<?php echo htmlentities(urlencode($message_id))?>&amp;<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" tabindex="<?php echo $tabindex++?>"><?php echo utf8_htmlentities($message->subject())?></a></td>
                <td class="c-absender"><?php echo utf8_htmlentities($message->from())?></td>
                           <td class="c-"><?php echo utf8_htmlentities($message->to())?></td>
                <td class="c-datum"><?php echo date('H:i:s, Y-m-d', $message->getTime())?></td>
            </tr>
<?php
                }
                $me->write();
?>
        </tbody>
        <tfoot>
            <tr>
                <td class="c-auswaehlen">
                    <script type="text/javascript">
                        // <![CDATA[
                        document.write('<button onclick="toggle_selection(); return false;" class="auswahl-button" title="[O]" accesskey="o" tabindex="1"><abbr title="Auswahl umkehren">A</abbr></button>');
                        // ]]>
                    </script>
                </td>
                <td colspan="3"><input type="submit" name="delete" class="loeschen-button" accesskey="n" tabindex="2" value="Löschen" title="[N]" /> <input type="submit" name="read" class="als-gelesen-markieren-button" tabindex="3" accesskey="u" title="[U]" value="Als gelesen markieren" /> <input type="submit" name="archive" class="archivieren-button" tabindex="4" value="Archivieren" /></td>
            </tr>
        </tfoot>
    </table>
</form>
<?php
            }
        }
    }
    else
    {
        $ncount = array(
            1 => array(0, 0, 'leer'),
            2 => array(0, 0, 'leer'),
            3 => array(0, 0, 'leer'),
            4 => array(0, 0, 'leer'),
            5 => array(0, 0, 'leer'),
            6 => array(0, 0, 'leer'),
            7 => array(0, 0, 'leer'),
            8 => array(0, 0, 'leer')
        );
        $ges_ncount = array(0, 0, 'leer');

        $cats = $me->getMessageCategoriesList();
        foreach($cats as $cat)
        {
            $message_ids = $me->getMessagesList($cat);
            foreach($message_ids as $message)
            {
                $status = $me->checkMessageStatus($message, $cat);
                $ncount[$cat][1]++;
                $ges_ncount[1]++;

                if($status == 1 && $cat != 8)
                {
                    $ncount[$cat][0]++;
                    $ges_ncount[0]++;
                }
            }
        }

        foreach($ncount as $type=>$cat)
        {
            if($cat[0] > 0 && $type != 8)
                $cat[2] = 'neu';
            elseif($cat[1] > 0)
                $cat[2] = 'alt';
            else
                $cat[2] = 'leer';
            $ncount[$type] = $cat;
        }

        if($ges_ncount[0] > 0)
            $ges_ncount[2] = 'neu';
        elseif($ges_ncount[1] > 0)
            $ges_ncount[2] = 'alt';
        else
            $ges_ncount[2] = 'leer';
?>
<h2>Nachrichten</h2>
<?php
    if(!$me->userLocked())
    {
?>
<ul class="nachrichten-neu-link">
    <li><a href="nachrichten.php?to=&amp;<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" accesskey="n" tabindex="1"><kbd>N</kbd>eue Nachricht</a></li>
</ul>
<?php
    }
?>
<dl class="nachrichten-kategorien">
    <dt class="c-kaempfe <?php echo $ncount[1][2]?>"><a href="nachrichten.php?type=1&amp;<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" accesskey="ä" tabindex="2">K<kbd>ä</kbd>mpfe</a></dt>
    <dd class="c-kaempfe <?php echo $ncount[1][2]?>"><?php echo utf8_htmlentities($ncount[1][0])?> <span class="gesamt">(<?php echo utf8_htmlentities($ncount[1][1])?>)</span></dd>

    <dt class="c-spionage <?php echo $ncount[2][2]?>"><a href="nachrichten.php?type=2&amp;<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" accesskey="o" tabindex="3">Spi<kbd>o</kbd>nage</a></dt>
    <dd class="c-spionage <?php echo $ncount[2][2]?>"><?php echo utf8_htmlentities($ncount[2][0])?> <span class="gesamt">(<?php echo utf8_htmlentities($ncount[2][1])?>)</span></dd>

    <dt class="c-transport <?php echo $ncount[3][2]?>"><a href="nachrichten.php?type=3&amp;<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" accesskey="j" title="[J]" tabindex="4">Transport</a></dt>
    <dd class="c-transport <?php echo $ncount[3][2]?>"><?php echo utf8_htmlentities($ncount[3][0])?> <span class="gesamt">(<?php echo utf8_htmlentities($ncount[3][1])?>)</span></dd>

    <dt class="c-sammeln <?php echo $ncount[4][2]?>"><a href="nachrichten.php?type=4&amp;<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" accesskey="q" title="[Q]" tabindex="5">Sammeln</a></dt>
    <dd class="c-sammeln <?php echo $ncount[4][2]?>"><?php echo utf8_htmlentities($ncount[4][0])?> <span class="gesamt">(<?php echo utf8_htmlentities($ncount[4][1])?>)</span></dd>

    <dt class="c-besiedelung <?php echo $ncount[5][2]?>"><a href="nachrichten.php?type=5&amp;<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" accesskey="u" tabindex="6">Besiedel<kbd>u</kbd>ng</a></dt>
    <dd class="c-besiedelung <?php echo $ncount[5][2]?>"><?php echo utf8_htmlentities($ncount[5][0])?> <span class="gesamt">(<?php echo utf8_htmlentities($ncount[5][1])?>)</span></dd>

    <dt class="c-benutzernachrichten <?php echo $ncount[6][2]?>"><a href="nachrichten.php?type=6&amp;<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" accesskey="z" tabindex="7">Benut<kbd>z</kbd>ernachrichten</a></dt>
    <dd class="c-benutzernachrichten <?php echo $ncount[6][2]?>"><?php echo utf8_htmlentities($ncount[6][0])?> <span class="gesamt">(<?php echo utf8_htmlentities($ncount[6][1])?>)</span></dd>

    <dt class="c-verbeundete <?php echo $ncount[7][2]?>"><a href="nachrichten.php?type=7&amp;<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" accesskey="ü" tabindex="8">Verb<kbd>ü</kbd>ndete</a></dt>
    <dd class="c-verbuendete <?php echo $ncount[7][2]?>"><?php echo utf8_htmlentities($ncount[7][0])?> <span class="gesamt">(<?php echo utf8_htmlentities($ncount[7][1])?>)</span></dd>

    <dt class="c-postausgang <?php echo $ncount[8][2]?>"><a href="nachrichten.php?type=8&amp;<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" accesskey="w" title="[W]" tabindex="9">Postausgang</a></dt>
    <dd class="c-postausgang <?php echo $ncount[8][2]?>"><?php echo utf8_htmlentities($ncount[8][1])?></dd>

    <dt class="c-gesamt <?php echo $ges_ncount[2]?>">Gesamt</dt>
    <dd class="c-gesamt <?php echo $ges_ncount[2]?>"><?php echo utf8_htmlentities($ges_ncount[0])?> <span class="gesamt">(<?php echo utf8_htmlentities($ges_ncount[1])?>)</span></dd>
</dl>



<?php
    }

    login_gui::html_foot();
?>
