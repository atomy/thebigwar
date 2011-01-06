<?php
    require_once( '../include/config_inc.php' );
    require( TBW_ROOT.'admin/include.php' );

    /**
	 * check for access to that page
	 * @extern $adminObj
	 */
	if( !isset($adminObj) || !$adminObj->can(ADMIN_VIEWLOGS))
	{
		die('No access.');
	}

    admin_gui::html_head();

    if(!isset($_GET['session']))
    {
        $sessions = array();
        $fh = fopen(global_setting("DB_ADMIN_LOGFILE"), 'r');
        fancy_flock($fh, LOCK_SH);

        while(($line = fgets($fh)) !== false)
        {
            $expl = explode("\t", preg_replace("/(\r|\n|(\r\n))$/", "", $line));
            if(count($expl) < 4) continue;

            if(!isset($sessions[$expl[0]])) $sessions[$expl[0]] = array($expl[2], $expl[1], ftell($fh)-strlen($line), false);
            $sessions[$expl[0]][3] = ftell($fh);
        }

        flock($fh, LOCK_UN);
        fclose($fh);
?>
<ul>
<?php
        foreach($sessions as $sid=>$sess)
        {
            $string = $sid.": ".$sess[0].", ".date('Y-m-d, H:i:s', $sess[1]);
?>
    <li><a href="logs.php?<?php echo htmlspecialchars('session='.urlencode($sid).'&start='.urlencode($sess[2]).'&end='.urlencode($sess[3]))?>"><?php echo htmlspecialchars($string)?></a></li>
<?php
        }
?>
</ul>
<?php
    }
    else
    {
        protocol("10", $_GET['session']);

        $fh = fopen(global_setting("DB_ADMIN_LOGFILE"), 'r');
        fancy_flock($fh, LOCK_SH);

        if(isset($_GET['start'])) fseek($fh, $_GET['start']);
?>
<table border="1">
    <thead>
        <tr>
            <th>Zeit</th>
            <th>Benutzername</th>
            <th>Aktion</th>
        </tr>
    </thead>
    <tbody>
<?php
        while(($line = fgets($fh)) !== false)
        {
            $expl = explode("\t", preg_replace("/(\r|\n|(\r\n))$/", "", $line));
            $count = count($expl);
            if($count < 4) continue;

            if(isset($actions[$expl[3]]))
            {
                $code = '$action_string = sprintf($actions[$expl[3]], $expl[2]';
                for($i=4; $i<$count; $i++) $code .= ', \''.preg_replace("/['\\\\]/", "\\\\$0", $expl[$i]).'\'';
                $code .= ');';
                eval($code);
            }
            else
            {
                if($count == 4) $action_string = $expl[3];
                else
                {
                    $action_string = $expl[3];
                    for($i=4; $i<$count; $i++)
                    {
                        if($i == 4) $action_string .= ": ";
                        else $action_string .= ", ";
                        $action_string .= $expl[$i];
                    }
                }
            }
?>
        <tr>
            <td><?php echo date('Y-m-d, H:i:s', $expl[1])?></td>
            <td><?php echo htmlspecialchars($expl[2])?></td>
            <td><?php echo htmlspecialchars($action_string)?></td>
        </tr>
<?php
            if(isset($_GET['end']) && ftell($fh) >= $_GET['end']) break;
        }
?>
    </tbody>
</table>
<?php
        flock($fh, LOCK_UN);
        fclose($fh);
    }

    admin_gui::html_foot();
?>