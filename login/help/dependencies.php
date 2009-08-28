<?php
	require_once( '../../include/config_inc.php' );
	require( TBW_ROOT.'login/scripts/include.php' );

	login_gui::html_head();
	
	$check_deps = array(
		'gebaeude' => 'Gebäude',
		'forschung' => 'Forschung',
		'roboter' => 'Roboter',
		'schiffe' => 'Schiff',
		'verteidigung' => 'Verteidigungsanlage'
	);
	
	foreach($check_deps as $type=>$heading)
	{
?>
<table class="deps" id="deps-<?=htmlentities($type)?>">
	<thead>
		<tr>
			<th class="c-item"><?=utf8_htmlentities($heading)?></th>
			<th class="c-deps">Abhängigkeiten</th>
		</tr>
	</thead>
	<tbody>
<?php
		$items = $me->getItemsList($type);
		foreach($items as $item)
		{
			$item_info = $me->getItemInfo($item, $type);
?>
		<tr id="deps-<?=htmlentities($item)?>">
			<td class="c-item"><a href="description.php?id=<?=htmlentities(urlencode($item))?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Genauere Informationen anzeigen"><?=utf8_htmlentities($item_info['name'])?></a></td>
<?php
			if(!isset($item_info['deps']) || count($item_info['deps']) <= 0)
			{
?>
			<td class="c-deps"></td>
<?php
			}
			else
			{
?>
			<td class="c-deps">
				<ul>
<?php
				foreach($item_info['deps'] as $dep)
				{
					$dep = explode('-', $dep, 2);
					$this_info = $me->getItemInfo($dep[0]);
?>
					<li class="deps-<?=($this_info['level'] >= $dep[1]) ? 'ja' : 'nein'?>"><a href="#deps-<?=htmlentities($dep[0])?>" title="Zu diesem Gegenstand scrollen."><?=utf8_htmlentities($this_info['name'])?></a> <span class="stufe">(Stufe&nbsp;<?=ths($dep[1])?>)</span></li>
<?php
				}
?>
				</ul>
			</td>
<?php
			}
?>
		</tr>
<?php
		}
?>
	</tbody>
</table>
<?php
	}
	
	login_gui::html_foot();
?>
