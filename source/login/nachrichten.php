<?php
    require_once( '../include/config_inc.php' );
    require( TBW_ROOT.'login/scripts/include.php' );
    require_once( TBW_ROOT.'include/MessageHelper.php');

    login_gui::html_head();
    
    global $me;

    if(isset($_GET['to']))
    {
        # Neue Nachricht verfassen
?>
<h2><a href="nachrichten.php?<?=htmlentities(session_name().'='.urlencode(session_id()))?>" title="Zurück zur Nachrichtenkategorienübersicht [W]" accesskey="w" tabindex="5">Nachrichten</a></h2>
<?php
        $error = '';
        $show_form = true;

        if(isset($_POST['empfaenger']) && isset($_POST['betreff']) && isset($_POST['inhalt']))
        {
            // Nachricht versenden, versuchen
            
            $_POST['empfaenger'] = trim($_POST['empfaenger']);
            $szToUser = $_POST['empfaenger'];
			$error = MessageHelper::SendNewMessage( $me, $szToUser, $szToUser, $_POST['betreff'], $_POST['inhalt'], $_SESSION['username'], MSGTYPE_USER);
			
			if(strlen($error) == 0)
			{
?>
<p class="successful">
    Die Nachricht wurde erfolgreich versandt.
</p>
<?php
            	$show_form = false;
            }
        }

        if(strlen($error) != 0)
        {
?>
<p class="error">
    <?=utf8_htmlentities($error)."\n"?>
</p>
<?php
        }

        if($show_form)
        {
            # Formular zum Absenden anzeigen
?>
<form action="nachrichten.php?to=&amp;<?=htmlentities(session_name().'='.urlencode(session_id()))?>" method="post" class="nachrichten-neu" onsubmit="this.setAttribute('onsubmit', 'return confirm(\'Doppelklickschutz: Sie haben ein zweites Mal auf \u201eAbsenden\u201c geklickt. Dadurch wird die Nachricht auch ein zweites Mal abgeschickt. Sind Sie sicher, dass Sie diese Aktion durchführen wollen?\');');">
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
            <dd class="c-empfaenger"><input type="text" id="empfaenger-input" name="empfaenger" value="<?=utf8_htmlentities($empfaenger)?>" tabindex="1" accesskey="z" title="[Z]" /></dd>

            <dt class="c-betreff"><label for="betreff-input">Betreff</label></dt>
            <dd class="c-betreff"><input type="text" id="betreff-input" name="betreff" value="<?=utf8_htmlentities($betreff)?>" maxlength="30" tabindex="2" accesskey="j" title="[J]" /></dd>

            <dt class="c-inhalt"><label for="inhalt-input">Inhalt</label></dt>
            <dd class="c-inhalt"><textarea id="inhalt-input" name="inhalt" cols="50" rows="10" tabindex="3" accesskey="x" title="[X]"><?=isset($_POST['inhalt']) ? preg_replace("/[\n\r\t]/e", '\'&#\'.ord(\'$0\').\';\'', utf8_htmlentities($_POST['inhalt'])) : ''?></textarea></dd>
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
    // inhalt des nachrichten-ordners anzeigen
    else if(isset($_GET['type']) && IsValidMessageType($_GET['type']))
    {
        #$message = Classes::Message();
        #$absender = $message->from();
        # Nachrichtentyp wurde bereits ausgewaehlt, Nachricht oder Nachrichtenliste anzeigen
        if(isset($_GET['message']))
        {
        
            # Nachricht anzeigen
             
?>
<h2><a href="nachrichten.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Zurück zur Nachrichtenkategorienübersicht [W]" tabindex="6" accesskey="w">Nachrichten</a>: <a href="nachrichten.php?type=<?=htmlentities(urlencode($_GET['type']))?>&amp;<?=htmlentities(session_name().'='.urlencode(session_id()))?>" title="Zurück zur Nachrichtenübersicht: <?=utf8_htmlentities(GetNameOfMessageType($_GET['type']))?> [O]" tabindex="5" accesskey="o"><?=utf8_htmlentities(GetNameOfMessageType($_GET['type']))?></a></h2>
<?php
			if(isset($_SESSION['admin_username']))
        		$msgObj = &MessageHelper::ReadMessage($me->getName(), $_GET['message'], false);
        	else
        		$msgObj = &MessageHelper::ReadMessage($me->getName(), $_GET['message'], true);
        
        	if(!$msgObj)
        	{
?>
<p class="error">
    Diese Nachricht existiert nicht.
</p>
<?php
           	}
            else
            {           
            	$fromUserID = $msgObj->GetFromUserID();
            	$toUserID = $msgObj->GetToUserID();
                $absender = UserHelper::GetUserNameOfUserID($fromUserID);
                $empfaenger = UserHelper::GetUserNameOfUserID($toUserID);
                
                if(!$me->userLocked())
                {
                    # Vorige und naechste ungelesene Nachricht bestimmen
                    $unread_prev = false;
                    $unread_next = false;
                    $unread_prev = MessageHelper::GetPrevUnreadMessageID($me->getName(), $_GET['type']);
                    $unread_next = MessageHelper::GetNextUnreadMessageID($me->getName(), $_GET['type']);

                    if($unread_next !== false || $unread_prev !== false)
                    {
                        # Vorige und naechste verlinken
?>
<ul class="ungelesene-nachrichten">
<?php
                        if($unread_prev !== false)
                        {
?>
    <li class="c-vorige"><a href="nachrichten.php?type=<?=htmlentities(urlencode($_GET['type']))?>&amp;message=<?=htmlentities(urlencode($unread_prev))?>&amp;<?=htmlentities(session_name().'='.urlencode(session_id()))?>" title="Vorige ungelesene Nachricht [U]" accesskey="u" tabindex="4">&larr;</a></li>

<?php
                        }
                        if($unread_next !== false)
                        {
?>
    <li class="c-naechste"><a href="nachrichten.php?type=<?=htmlentities(urlencode($_GET['type']))?>&amp;message=<?=htmlentities(urlencode($unread_next))?>&amp;<?=htmlentities(session_name().'='.urlencode(session_id()))?>" title="Nächste ungelesene Nachricht [Q]" accesskey="q" tabindex="3">&rarr;</a></li>
<?php
                        }
?>
</ul>
<?php
                    }
?>
<dl class="nachricht-informationen type-<?=utf8_htmlentities($_GET['type'])?><?=false ? ' html' : ''?>">
<?php
                    if(trim($absender) != '')
                    {
?>
    <dt class="c-absender">Absender</dt>
    <dd class="c-absender"><a href="help/playerinfo.php?player=<?=htmlentities(urlencode($absender))?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Informationen zu diesem Spieler anzeigen"><?=utf8_htmlentities($absender)?></a></dd>
       <dt class="c-empfaenger">Empfaenger</dt>
     <dd class="c-empfaenger"><a href="help/playerinfo.php?player=<?=htmlentities(urlencode($empfaenger))?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Informationen zu diesem Spieler anzeigen"><?=utf8_htmlentities($empfaenger)?></a></dd>

<?php
                    }
?>
    <dt class="c-betreff">Betreff</dt>
    <dd class="c-betreff"><?=utf8_htmlentities($msgObj->GetSubject())?></dd>

    <dt class="c-zeit">Zeit</dt>
    <dd class="c-zeit"><?=date('H:i:s, Y-m-d', $msgObj->GetTime())?></dd>

    <dt class="c-nachricht">Nachricht</dt>
    <dd class="c-nachricht">
    <p>
<?php
                    print($msgObj->GetText());
?>
	</p>
    </dd>
</dl>
<?php
                    if($_GET['type'] == MSGTYPE_ALLY)
                    {
?>
<ul class="nachrichten-verbuendeten-links">
    <li class="c-verbuendete"><a href="verbuendete.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>">Zur Verbündetenseite</a></li>
    <li class="c-allianz"><a href="allianz.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>">Zur Allianzseite</a></li>
</ul>
<?php
                    }

                    if($absender != '' && $absender != $_SESSION['username'])
                    {
                        # Bei Nachrichten im Postausgang ist die Antwort nicht moeglich
                        $re_betreff = $msgObj->GetSubject();
                        if(substr($re_betreff, 0, 4) != 'Re: ')
                            $re_betreff = 'Re: '.$re_betreff;
?>
<form action="nachrichten.php" method="get" class="nachricht-antworten-formular">
    <div>
        <input type="hidden" name="<?=htmlentities(session_name())?>" value="<?=htmlentities(session_id())?>" />
        <input type="hidden" name="to" value="<?=utf8_htmlentities($absender)?>" />
        <input type="hidden" name="subject" value="<?=utf8_htmlentities($re_betreff)?>" />
        <button type="submit" accesskey="w" tabindex="1">Ant<kbd>w</kbd>orten</button>
    </div>
</form>
<?php
                    }
?>
<form action="nachrichten.php?type=<?=htmlentities(urlencode($_GET['type']))?>&amp;<?=htmlentities(session_name().'='.urlencode(session_id()))?>" method="post" class="nachricht-loeschen-formular">
    <div><input type="hidden" name="message[<?=htmlentities($_GET['message'])?>]" value="on" /><input type="submit" name="delete" accesskey="n" tabindex="2" value="Löschen" title="[N]" /> <input type="submit" name="archive" tabindex="3" value="Archivieren" /></div>
</form>
<?php
                    if(isset($_POST['weiterleitung-to']))
                    {
                        $error = MessageHelper::ForwardMessageTo($msgObj, $me, $_POST['weiterleitung-to']);

  						if ( $error == "" )
  						{
?>
<p class="successful">Die Nachricht wurde erfolgreich weitergeleitet.</p>
<?php  		  							 				
  						}	
  						else
  						{
?>
<p class="error"><?=$error?></p>
<?php 					
  						}

                        unset($_POST['weiterleitung-to']);
                    }

                    // TODO
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
    Sie können diese Nachricht öffentlich verlinken: <a href="http://<?=htmlentities($host.h_root)?>/public_message.php?id=<?=htmlentities(urlencode($_GET['message']))?>&amp;database=<?=htmlentities(urlencode($_SESSION['database']))?>">http://<?=htmlentities($host.h_root)?>/public_message.php?id=<?=htmlentities(urlencode($_GET['message']))?>&amp;database=<?=htmlentities(urlencode($_SESSION['database']))?></a>
</p>
<?php
                    }
                    else
                    {
?>
<ul id="nachricht-veroeffentlichen">
    <li><a href="nachrichten.php?type=<?=htmlentities(urlencode($_GET['type']))?>&amp;message=<?=htmlentities(urlencode($_GET['message']))?>&amp;publish=1&amp;<?=htmlentities(session_name().'='.urlencode(session_id()))?>#nachricht-veroeffentlichen">Nachricht veröffentlichen</a></li>
</ul>
<?php
                    }
?>
<form action="nachrichten.php?type=<?=htmlentities(urlencode($_GET['type']))?>&amp;message=<?=htmlentities(urlencode($_GET['message']))?>&amp;<?=htmlentities(session_name().'='.urlencode(session_id()))?>#nachricht-weiterleiten-formular" method="post" id="nachricht-weiterleiten-formular" class="nachricht-weiterleiten-formular">
    <fieldset>
        <legend>Nachricht weiterleiten</legend>
        <dl>
            <dt><label for="empfaenger-input">Empfänger</label></dt>
            <dd><input type="text" id="empfaenger-input" name="weiterleitung-to" value="<?=isset($_POST['weiterleitung-to']) ? utf8_htmlentities($_POST['weiterleitung-to']) : ''?>" title="[X]" accesskey="x" tabindex="5" /></dd>
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
        if($me->userLocked())
        {
?>
<p class="error">
    Sie können während der Sperre die Nachrichtenfunktion nicht nutzen, bei Fragen nutzen sie das Ticketsystem.
</p>
<?php
            }
        }
        else
        {
            # Nachrichtenuebersicht einer Kategorie anzeigen
?>
<h2><a href="nachrichten.php?<?=htmlentities(session_name().'='.urlencode(session_id()))?>" title="Zurück zur Nachrichtenkategorienübersicht [W]" accesskey="w" tabindex="4">Nachrichten</a>: <?=utf8_htmlentities($g_MSGTYPE_NAMES[$_GET['type']])?></h2>
<?php
            $messageObjArray = &MessageHelper::GetMessagesByType($me->getName(), $_GET['type']);
            
            if(count($messageObjArray) > 0)
            {
                if(isset($_POST['read']) && isset($_POST['message']) && is_array($_POST['message']))
                {
                    # Als gelesen markieren
                    foreach($_POST['message'] as $message_id=>$v)
                        MessageHelper::MarkMessageAsRead($me->getName(), $message_id);
                }
                elseif(isset($_POST['delete']) && isset($_POST['message']) && is_array($_POST['message']))
                {
                    # Loeschen
                    foreach($_POST['message'] as $message_id=>$v)
                        MessageHelper::DeleteMessage($me->getName(), $message_id);
                }
                elseif(isset($_POST['archive']) && isset($_POST['message']) && is_array($_POST['message']))
                {
                    # Archivieren
                    foreach($_POST['message'] as $message_id=>$v)
                    	MessageHelper::ArchieveMessage($me->getName(), $message_id);
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
<form action="nachrichten.php?type=<?=htmlentities(urlencode($_GET['type']))?>&amp;<?=htmlentities(session_name().'='.urlencode(session_id()))?>" method="post" class="nachrichten-liste type-<?=utf8_htmlentities($_GET['type'])?>" id="nachrichten-liste">
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
                foreach($messageObjArray as $messageObj)
                {        
                	$iFromUserID = $messageObj->GetFromUserID();
                	$iToUserID = $messageObj->GetToUserID();
                	$szFrom = UserHelper::GetUserNameOfUserID($iFromUserID);
                	$szToUser = UserHelper::GetUserNameOfUserID($iToUserID);  
                	$message_id = $messageObj->GetID();
                	                       
                    if($messageObj->GetIsArchieved()) 
                    	$class = 'archiviert';
                    else if(!$messageObj->GetIsRead()) 
                    	$class = 'neu';
                    else 
                    	$class = 'alt';
?>
            <tr class="<?=$class?>">
                <td class="c-auswaehlen"><input type="checkbox" name="message[<?=htmlentities($message_id)?>]" tabindex="<?=$tabindex++?>" /></td>
                <td class="c-betreff"><a href="nachrichten.php?type=<?=htmlentities(urlencode($_GET['type']))?>&amp;message=<?=htmlentities(urlencode($message_id))?>&amp;<?=htmlentities(session_name().'='.urlencode(session_id()))?>" tabindex="<?=$tabindex++?>"><?=utf8_htmlentities($messageObj->GetSubject())?></a></td>
                <td class="c-absender"><?=utf8_htmlentities($szFrom)?></td>
                           <td class="c-"><?=utf8_htmlentities($szToUser)?></td>
                <td class="c-datum"><?=date('H:i:s, Y-m-d', $messageObj->GetTime())?></td>
            </tr>
<?php
                }
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
?>
<h2>Nachrichten</h2>
<?php
    if(!$me->userLocked())
    {
?>
<ul class="nachrichten-neu-link">
    <li><a href="nachrichten.php?to=&amp;<?=htmlentities(session_name().'='.urlencode(session_id()))?>" accesskey="n" tabindex="1"><kbd>N</kbd>eue Nachricht</a></li>
</ul>
<?php
    }
?>
<dl class="nachrichten-kategorien">
    <dt class="c-kaempfe"><a href="nachrichten.php?type=<?=MSGTYPE_BATTLE?>&amp;<?=htmlentities(session_name().'='.urlencode(session_id()))?>" accesskey="ä" tabindex="2">K<kbd>ä</kbd>mpfe</a></dt>
    <dd class="c-kaempfe"><?=utf8_htmlentities(MessageHelper::GetMessageCountForType($me->getName(), MSGTYPE_BATTLE, true))?> <span class="gesamt">(<?=utf8_htmlentities(MessageHelper::GetMessageCountForType($me->getName(), MSGTYPE_BATTLE, true))?>)</span></dd>

    <dt class="c-spionage"><a href="nachrichten.php?type=<?=MSGTYPE_SPY?>&amp;<?=htmlentities(session_name().'='.urlencode(session_id()))?>" accesskey="o" tabindex="3">Spi<kbd>o</kbd>nage</a></dt>
    <dd class="c-spionage"><?=utf8_htmlentities(MessageHelper::GetMessageCountForType($me->getName(), MSGTYPE_SPY, true))?> <span class="gesamt">(<?=utf8_htmlentities(MessageHelper::GetMessageCountForType($me->getName(), MSGTYPE_SPY, false))?>)</span></dd>

    <dt class="c-transport"><a href="nachrichten.php?type=<?=MSGTYPE_FLEET?>&amp;<?=htmlentities(session_name().'='.urlencode(session_id()))?>" accesskey="j" title="[J]" tabindex="4">Flotte</a></dt>
    <dd class="c-transport"><?=utf8_htmlentities(MessageHelper::GetMessageCountForType($me->getName(), MSGTYPE_FLEET, true))?> <span class="gesamt">(<?=utf8_htmlentities(MessageHelper::GetMessageCountForType($me->getName(), MSGTYPE_FLEET, false))?>)</span></dd>

    <dt class="c-benutzernachrichten"><a href="nachrichten.php?type=<?=MSGTYPE_USER?>&amp;<?=htmlentities(session_name().'='.urlencode(session_id()))?>" accesskey="z" tabindex="5">Benut<kbd>z</kbd>ernachrichten</a></dt>
    <dd class="c-benutzernachrichten"><?=utf8_htmlentities(MessageHelper::GetMessageCountForType($me->getName(), MSGTYPE_USER, true))?> <span class="gesamt">(<?=utf8_htmlentities(MessageHelper::GetMessageCountForType($me->getName(), MSGTYPE_USER, false))?>)</span></dd>

    <dt class="c-verbeundete"><a href="nachrichten.php?type=<?=MSGTYPE_ALLY?>&amp;<?=htmlentities(session_name().'='.urlencode(session_id()))?>" accesskey="ü" tabindex="6">Verb<kbd>ü</kbd>ndete</a></dt>
    <dd class="c-verbuendete"><?=utf8_htmlentities(MessageHelper::GetMessageCountForType($me->getName(), MSGTYPE_ALLY, true))?> <span class="gesamt">(<?=utf8_htmlentities(MessageHelper::GetMessageCountForType($me->getName(), MSGTYPE_ALLY, false))?>)</span></dd>

    <dt class="c-postausgang"><a href="nachrichten.php?type=<?=MSGTYPE_SENT?>&amp;<?=htmlentities(session_name().'='.urlencode(session_id()))?>" accesskey="w" title="[W]" tabindex="7">Postausgang</a></dt>
    <dd class="c-postausgang"><?=utf8_htmlentities(MessageHelper::GetMessageCountForType($me->getName(), MSGTYPE_SENT, true))?></dd>

    <dt class="c-gesamt">Gesamt</dt>
    <dd class="c-gesamt"><?=utf8_htmlentities(MessageHelper::GetMessageCountForType($me->getName(), -1, true))?> <span class="gesamt">(<?=utf8_htmlentities(MessageHelper::GetMessageCountForType($me->getName(), -1, false))?>)</span></dd>
</dl>



<?php
    }

    login_gui::html_foot();
?>
