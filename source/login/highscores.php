<?php
	require_once( '../include/config_inc.php' );
	require_once( TBW_ROOT.'include/util.php' );
	require( TBW_ROOT.'login/scripts/include.php' );

	login_gui::html_head();
	
	if(isset($_GET['alliances']) && $_GET['alliances'])
	{
		$mode = 'alliances';
		$mode_prefix = 'alliances='.urlencode($_GET['alliances']).'&';
	}
	else
	{
		$mode = 'users';
		$mode_prefix = '';
	}
?>
<ul class="highscores-modi">
	<li class="c-spieler<?php echo($mode=='users') ? ' active' :''?>"><a href="highscores.php?<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>">Spieler</a></li>
	<li class="c-allianzen<?php echo($mode=='alliances') ? ' active' : ''?>"><a href="highscores.php?alliances=1&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>">Allianzen</a></li>
</ul>
<?php
	$highscores = Classes::Highscores();
	$count = $highscores->getCount($mode);
	$start = 1;
	if(isset($_GET['start']) && $_GET['start'] <= $count && $_GET['start'] >= 1)
		$start = (int) $_GET['start'];
	
	$sort_field = false;
	if(isset($_GET['alliances']) && $_GET['alliances'] == '2') $sort_field = 'scores_total';
	$list = $highscores->getList($mode, $start, $start+100, $sort_field);
	
	if($count > 100)
	{
?>
<ul class="highscores-seiten">
<?php
		if($start > 1)
		{
			$start_prev = $start-100;
			if($start_prev < 1) $start_prev = 1;
?>
	<li class="c-vorige"><a href="highscores.php?<?php echohtmlentities($mode_prefix)?>start=<?php echohtmlentities(urlencode($start_prev))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" rel="prev">&larr; <?php echohtmlentities($start_prev)?>&ndash;<?php echohtmlentities($start_prev+99)?></a></li>
<?php
		}
		if($start+100 <= $count)
		{
			$start_next = $start+100;
			$end_next = $start_next+99;
			if($end_next > $count) $end_next = $count;
?>
	<li class="c-naechste"><a href="highscores.php?<?php echohtmlentities($mode_prefix)?>start=<?php echohtmlentities(urlencode($start_next))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" rel="next"><?php echohtmlentities($start_next)?>&ndash;<?php echohtmlentities($end_next)?> &rarr;</a></li>
<?php
		}
?>
</ul>
<?php
	}
	
	if($mode == 'users')
	{
?>
<table class="highscores spieler">
	<thead>
		<tr>
			<th class="c-platz">Platz</th>
			<th class="c-spieler">Spieler</th>
			<th class="c-allianz">Allianz</th>
			<th class="c-punktzahl">Punktzahl</th>
			<th class="c-lastactive">Letzte Aktivit&auml;t</th>
		</tr>
	</thead>
<?php
	}
	else
	{
?>
<table class="highscores allianzen">
	<thead>
		<tr>
			<th class="c-platz">Platz</th>
			<th class="c-allianz">Allianz</th>
			<th class="c-punkteschnitt"><?php echo($_GET['alliances']=='2') ? '<a href="highscores.php?alliances=1&amp;'.htmlentities(urlencode(session_name()).'='.urlencode(session_id())).'">Punkteschnitt</a>' : 'Punkteschnitt'?></th>
			<th class="c-gesamtpunkte"><?php echo($_GET['alliances']=='2') ? 'Gesamtpunkte' : '<a href="highscores.php?alliances=2&amp;'.htmlentities(urlencode(session_name()).'='.urlencode(session_id())).'">Gesamtpunkte</a>'?></th>
			<th class="c-mitglieder">Mitglieder</th>
		</tr>
	</thead>
<?php
	}
?>
	<tbody>
<?php
	for($i=0; list(,$info)=each($list); $i++)
	{
		if($mode == 'users')
		{
			$class = 'fremd';
			if($info['username'] == $_SESSION['username'])
				$class = 'eigen';
			elseif($me->isVerbuendet($info['username']))
				$class = 'verbuendet';
			
			$alliance_class = 'fremd';
			if($info['alliance'] && $me->allianceTag() == $info['alliance'])
				$alliance_class = 'verbuendet';
?>
		<tr class="<?php echo$class?> allianz-<?php echo$alliance_class?>">
			<th class="c-platz"><?php echoths($start+$i)?></th>
			<td class="c-spieler"><a href="help/playerinfo.php?player=<?php echohtmlentities(urlencode($info['username']))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Informationen zu diesem Spieler anzeigen" class="playername"><?php echoutf8_htmlentities($info['username'])?></a></td>
<?php
			if($info['alliance'])
			{
?>
			<td class="c-allianz"><a href="help/allianceinfo.php?alliance=<?php echohtmlentities(urlencode($info['alliance']))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Informationen zu dieser Allianz anzeigen"><?php echoutf8_htmlentities($info['alliance'])?></a></td>
<?php
			}
			else
			{
?>
			<td class="c-allianz keine"></td>
<?php
			}
?>
			<td class="c-punktzahl"><?php echoths($info['scores'])?></td>
<?php

			// show lastactive in highscores for friends and friendly ally members
			$strLastActive = '';
			
			if ( $class == 'verbuendet' || $class == 'eigen' || $alliance_class == 'verbuendet' || isset( $_SESSION['admin_username'] ) )
			{
				if ( User::userExists( $info['username'] ) )
				{
					$user = Classes::User( $info['username'] );
					$strLastActive = timeAgo( $user->getLastActivity() );
				}
				else
				{
					$strLastActive = 'N/A';
				}
			} 
			else
			{ 
				$strLastActive = '?';
			}
				
?>                  
			<th class="c-lastactive"><?php print $strLastActive; ?></td>

<?php

?>				
		</tr>
<?php
		}
		else
		{
			$class = 'fremd';
			if($info['tag'] == $me->allianceTag())
				$class = 'verbuendet';
			
?>
		<tr class="<?php echo$class?>">
			<th class="c-platz"><?php echoths($start+$i)?></th>
			<td class="c-allianz"><a href="help/allianceinfo.php?alliance=<?php echohtmlentities(urlencode($info['tag']))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Informationen zu dieser Allianz anzeigen"><?php echoutf8_htmlentities($info['tag'])?></a></td>
			<td class="c-punkteschnitt"><?php echoths($info['scores_average'])?></td>
			<td class="c-gesamtpunkte"><?php echoths($info['scores_total'])?></td>
			<td class="c-mitglieder"><?php echoths($info['members_count'])?></td>
		</tr>
<?php
		}
	}
?>
	</tbody>
</table>
<?php
	login_gui::html_foot();
?>
