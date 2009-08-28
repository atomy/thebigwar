<?php
	require_once( '../include/config_inc.php' );
	require( TBW_ROOT.'login/scripts/include.php' );

	$laufende_forschungen = array();
	$planets = $me->getPlanetsList();
	$active_planet = $me->getActivePlanet();
	foreach($planets as $planet)
	{
		$me->setActivePlanet($planet);
		$building = $me->checkBuildingThing('forschung');
		if($building)
			$laufende_forschungen[] = $building[0];
		elseif($building = $me->checkBuildingThing('gebaeude') && $building[0] == 'B8')
			$laufende_forschungen[] = false;
	}
	$me->setActivePlanet($active_planet);

	if(isset($_GET['lokal']))
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
		delete_request();

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
	$tabindex = 1;
	foreach($forschungen as $id)
	{
		$item_info = $me->getItemInfo($id, 'forschung');

		if(!$item_info['deps-okay'] && $item_info['level'] <= 0 && (!$building || $building[0] != $id))
			continue;

		$buildable_global = $item_info['buildable'];
		if($buildable_global && count($laufende_forschungen) > 0)
			$buildable_global = false; # Es wird schon wo geforscht
?>
<div class="item forschung" id="item-<?=htmlentities($id)?>">
	<h3><a href="help/description.php?id=<?=htmlentities(urlencode($id))?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Genauere Informationen anzeigen"><?=utf8_htmlentities($item_info['name'])?></a> <span class="stufe">(Level&nbsp;<?=ths($item_info['level'])?>)</span></h3>
<?php
		if((!($building_geb = $me->checkBuildingThing('gebaeude')) || $building_geb[0] != 'B8') && $item_info['buildable'] && $me->permissionToAct() && !($building = $me->checkBuildingThing('forschung')) && !in_array($id, $laufende_forschungen) && $item_info['deps-okay'])
		{
			$enough_ress = $me->checkRess($item_info['ress']);
			$buildable_global = ($buildable_global && $enough_ress);
?>
	<ul>
		<li class="item-ausbau forschung-lokal<?=$enough_ress ? '' : ' no-ress'?>"><?=$enough_ress ? '<a href="forschung.php?lokal='.htmlentities(urlencode($id)).'&amp;'.htmlentities(urlencode(session_name()).'='.urlencode(session_id())).'" tabindex="'.($tabindex++).'">' : ''?>Lokal weiterentwickeln<?=$enough_ress ? '</a>' : ''?></li>
<?php
			if(count($laufende_forschungen) <= 0)
			{
?>
		<li class="item-ausbau forschung-global<?=$buildable_global ? '' : ' no-ress'?>"><?=$buildable_global ? '<a href="forschung.php?global='.htmlentities(urlencode($id)).'&amp;'.htmlentities(urlencode(session_name()).'='.urlencode(session_id())).'" tabindex="'.($tabindex++).'">' : ''?>Global weiterentwickeln<?=$buildable_global ? '</a>' : ''?></li>
<?php
			}
?>
	</ul>
<?php
		}
		elseif($building && $building[0] == $id)
		{
?>
	<div class="restbauzeit" id="restbauzeit-<?=htmlentities($id)?>">Fertigstellung: <?=date('H:i:s, Y-m-d', $building[1])?> (Serverzeit), <a href="forschung.php?cancel=<?=htmlentities(urlencode($id))?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" class="abbrechen">Abbrechen</a></div>
	<script type="text/javascript">
		init_countdown('<?=$id?>', <?=$building[1]?>);
	</script>
<?php
		}
?>
	<dl>
		<dt class="item-kosten">Kosten</dt>
		<dd class="item-kosten">
			<?=format_ress($item_info['ress'], 3)?>
		</dd>

		<dt class="item-bauzeit forschung-lokal">Bauzeit lokal</dt>
		<dd class="item-bauzeit forschung-lokal"><?=format_btime($item_info['time_local'])?></dd>

		<dt class="item-bauzeit forschung-global">Bauzeit global</dt>
		<dd class="item-bauzeit forschung-global"><?=format_btime($item_info['time_global'])?></dd>
	</dl>
</div>
<?php
	}
?>
<?php
	login_gui::html_foot();
?>
