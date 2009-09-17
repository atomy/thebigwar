<?php
	require_once( '../include/config_inc.php' );
	require( TBW_ROOT.'login/scripts/include.php' );

	$active_planet = $me->getActivePlanet();

	switch(isset($_GET['action']) ? $_GET['action'] : false)
	{
		case 'roboter':
		case 'flotte':
			$action = $_GET['action'];
			break;
		default:
			$action = 'ress';
			break;
	}

	function get_prod_class($prod)
	{
		if($prod > 0)
			return 'positiv';
		elseif($prod < 0)
			return 'negativ';
		else
			return 'null';
	}

	login_gui::html_head();

	$tabindex = 1;
?>
<h2>Imperium</h2>
<ul class="imperium-modi">
	<li class="c-rohstoffe<?=($action == 'ress') ? ' active' : ''?>"><a href="imperium.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>"<?=($action == 'ress') ? '' : ' tabindex="'.htmlentities($tabindex++).'"'?>>Rohstoffe</a></li>
	<li class="c-roboter<?=($action == 'roboter') ? ' active' : ''?>"><a href="imperium.php?action=roboter&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>"<?=($action == 'roboter') ? '' : ' tabindex="'.htmlentities($tabindex++).'"'?>>Roboter</a></li>
	<li class="c-flotte<?=($action == 'flotte') ? ' active' : ''?>"><a href="imperium.php?action=flotte&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>"<?=($action == 'flotten') ? '' : ' tabindex="'.htmlentities($tabindex++).'"'?>>Flotten</a></li>
</ul>
<?php
	switch($action)
	{
		case 'ress':
?>
<h3 id="rohstoffvorraete">Rohstoffvorräte</h3>
<table class="imperium-tabelle imperium-rohstoffvorraete">
	<thead>
		<tr>
			<th class="c-planet">Planet</th>
			<th class="c-carbon">Carbon</th>
			<th class="c-aluminium">Aluminium</th>
			<th class="c-wolfram">Wolfram</th>
			<th class="c-radium">Radium</th>
			<th class="c-tritium">Tritium</th>
			<th class="c-gesamt">Gesamt</th>
		</tr>
	</thead>
	<tbody>
<?php
			$ges = array(0, 0, 0, 0, 0, 0);
			$planets = $me->getPlanetsList();
			foreach($planets as $planet)
			{
				$me->setActivePlanet($planet);
				$ress = $me->getRess();
				$ges[0] += $ress[0];
				$ges[1] += $ress[1];
				$ges[2] += $ress[2];
				$ges[3] += $ress[3];
				$ges[4] += $ress[4];
				$this_ges = $ress[0]+$ress[1]+$ress[2]+$ress[3]+$ress[4];
				$ges[5] += $this_ges;
?>
		<tr<?=($planet == $active_planet) ? ' class="active"' : ''?>>
			<th class="c-planet" title="<?=utf8_htmlentities($me->planetName())?>"><a href="imperium.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>&amp;planet=<?=htmlentities(urlencode($planet))?>"><?=utf8_htmlentities($me->getPosString())?></a></th>
			<td class="c-carbon"><?=ths($ress[0])?></td>
			<td class="c-aluminium"><?=ths($ress[1])?></td>
			<td class="c-wolfram"><?=ths($ress[2])?></td>
			<td class="c-radium"><?=ths($ress[3])?></td>
			<td class="c-tritium"><?=ths($ress[4])?></td>
			<td class="c-gesamt"><?=ths($this_ges)?></td>
		</tr>
<?php
			}
			$schiffb = $me->getRessOnAllFleets();
			$ges[0] += $schiffb[0];
			$ges[1] += $schiffb[1];
			$ges[2] += $schiffb[2];
			$ges[3] += $schiffb[3];
			$ges[4] += $schiffb[4];
			$this_ges = $schiffb[0]+$schiffb[1]+$schiffb[2]+$schiffb[3]+$schiffb[4];
			$ges[5] += $this_ges;						
?>
		<tr>
			<th class="c-planet">Schiffsbeladung</th>
			<td class="c-carbon"><?=ths($schiffb[0])?></td>
			<td class="c-aluminium"><?=ths($schiffb[1])?></td>
			<td class="c-wolfram"><?=ths($schiffb[2])?></td>
			<td class="c-radium"><?=ths($schiffb[3])?></td>
			<td class="c-tritium"><?=ths($schiffb[4])?></td>
			<td class="c-gesamt"><?=ths($this_ges)?></td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<th class="c-planet">Gesamt</th>
			<td class="c-carbon"><?=ths($ges[0])?></td>
			<td class="c-aluminium"><?=ths($ges[1])?></td>
			<td class="c-wolfram"><?=ths($ges[2])?></td>
			<td class="c-radium"><?=ths($ges[3])?></td>
			<td class="c-tritium"><?=ths($ges[4])?></td>
			<td class="c-gesamt"><?=ths($ges[5])?></td>
		</tr>
	</tfoot>
</table>
<h3 id="rohstoffproduktion">Rohstoffproduktion pro Stunde</h3>
<table class="imperium-tabelle imperium-rohstoffproduktion">
	<thead>
		<tr>
			<th class="c-planet">Planet</th>
			<th class="c-carbon">Carbon</th>
			<th class="c-aluminium">Aluminium</th>
			<th class="c-wolfram">Wolfram</th>
			<th class="c-radium">Radium</th>
			<th class="c-tritium">Tritium</th>
			<th class="c-gesamt">Gesamt</th>
			<th class="c-energie">Energie</th>
		</tr>
	</thead>
	<tbody>
<?php
			$ges = array(0, 0, 0, 0, 0, 0, 0);
			foreach($planets as $planet)
			{
				$me->setActivePlanet($planet);
				$this_prod = $me->getProduction();

				$ges[0] += $this_prod[0];
				$ges[1] += $this_prod[1];
				$ges[2] += $this_prod[2];
				$ges[3] += $this_prod[3];
				$ges[4] += $this_prod[4];
				$ges[5] += $this_prod[5];
				$this_ges = $this_prod[0]+$this_prod[1]+$this_prod[2]+$this_prod[3]+$this_prod[4];
				$ges[6] += $this_ges;
?>
		<tr<?=($planet == $active_planet) ? ' class="active"' : ''?>>
			<th class="c-planet" title="<?=utf8_htmlentities($me->planetName())?>"><a href="imperium.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>&amp;planet=<?=htmlentities(urlencode($planet))?>"><?=utf8_htmlentities($me->getPosString())?></a></th>
			<td class="c-carbon <?=get_prod_class($this_prod[0])?>"><?=ths($this_prod[0])?></td>
			<td class="c-aluminium <?=get_prod_class($this_prod[1])?>"><?=ths($this_prod[1])?></td>
			<td class="c-wolfram <?=get_prod_class($this_prod[2])?>"><?=ths($this_prod[2])?></td>
			<td class="c-radium <?=get_prod_class($this_prod[3])?>"><?=ths($this_prod[3])?></td>
			<td class="c-tritium <?=get_prod_class($this_prod[4])?>"><?=ths($this_prod[4])?></td>
			<td class="c-gesamt <?=get_prod_class($this_ges)?>"><?=ths($this_ges)?></td>
			<td class="c-energie <?=get_prod_class($this_prod[5])?>"><?=ths($this_prod[5])?></td>
		</tr>
<?php
			}

			$day_prod = array($ges[0]*24, $ges[1]*24, $ges[2]*24, $ges[3]*24, $ges[4]*24);
			$show_day_prod = $day_prod;
			$show_days = $me->checkSetting('prod_show_days');
			$show_day_prod[0] *= $show_days;
			$show_day_prod[1] *= $show_days;
			$show_day_prod[2] *= $show_days;
			$show_day_prod[3] *= $show_days;
			$show_day_prod[4] *= $show_days;
			$show_day_prod[5] = array_sum($show_day_prod);
?>
	</tbody>
	<tfoot>
		<tr class="gesamt-stuendlich">
			<th class="c-planet">Gesamt</th>
			<td class="c-carbon <?=get_prod_class($ges[0])?>"><?=ths($ges[0])?></td>
			<td class="c-aluminium <?=get_prod_class($ges[1])?>"><?=ths($ges[1])?></td>
			<td class="c-wolfram <?=get_prod_class($ges[2])?>"><?=ths($ges[2])?></td>
			<td class="c-radium <?=get_prod_class($ges[3])?>"><?=ths($ges[3])?></td>
			<td class="c-tritium <?=get_prod_class($ges[4])?>"><?=ths($ges[4])?></td>
			<td class="c-gesamt <?=get_prod_class($ges[6])?>"><?=ths($ges[6])?></td>
			<td class="c-energie <?=get_prod_class($ges[5])?>"><?=ths($ges[5])?></td>
		</tr>
		<tr class="gesamt-taeglich">
			<th class="c-planet">Pr<kbd>o</kbd> <input type="text" class="prod-show-days" name="show_days" id="show_days" value="<?=utf8_htmlentities($show_days)?>" tabindex="<?=htmlentities($tabindex++)?>" accesskey="o" onchange="recalc_perday();" onclick="recalc_perday();" onkeyup="recalc_perday();" />&nbsp;Tage</th>
			<td class="c-carbon <?=get_prod_class($show_day_prod[0])?>" id="taeglich-carbon"><?=ths($show_day_prod[0])?></td>
			<td class="c-aluminium <?=get_prod_class($show_day_prod[1])?>" id="taeglich-aluminium"><?=ths($show_day_prod[1])?></td>
			<td class="c-wolfram <?=get_prod_class($show_day_prod[2])?>" id="taeglich-wolfram"><?=ths($show_day_prod[2])?></td>
			<td class="c-radium <?=get_prod_class($show_day_prod[3])?>" id="taeglich-radium"><?=ths($show_day_prod[3])?></td>
			<td class="c-tritium <?=get_prod_class($show_day_prod[4])?>" id="taeglich-tritium"><?=ths($show_day_prod[4])?></td>
			<td class="c-gesamt <?=get_prod_class($show_day_prod[5])?>" id="taeglich-gesamt"><?=ths($show_day_prod[5])?></td>
			<td class="c-energie"></td>
		</tr>
	</tfoot>
</table>
<script type="text/javascript">
// <![CDATA[
	function recalc_perday()
	{
		var show_days = parseFloat(document.getElementById('show_days').value);

		var carbon,aluminium,wolfram,radium,tritium,gesamt;
		if(isNaN(show_days))
		{
			carbon = 0;
			aluminium = 0;
			wolfram = 0;
			radium = 0;
			tritium = 0;
			gesamt = 0;
		}
		else
		{
			carbon = <?=floor($day_prod[0])?>*show_days;
			aluminium = <?=floor($day_prod[1])?>*show_days;
			wolfram = <?=floor($day_prod[2])?>*show_days;
			radium = <?=floor($day_prod[3])?>*show_days;
			tritium = <?=floor($day_prod[4])?>*show_days;
			gesamt = carbon+aluminium+wolfram+radium+tritium;
		}

		document.getElementById('taeglich-carbon').firstChild.data = ths(carbon);
		document.getElementById('taeglich-aluminium').firstChild.data = ths(aluminium);
		document.getElementById('taeglich-wolfram').firstChild.data = ths(wolfram);
		document.getElementById('taeglich-radium').firstChild.data = ths(radium);
		document.getElementById('taeglich-tritium').firstChild.data = ths(tritium);
		document.getElementById('taeglich-gesamt').firstChild.data = ths(gesamt);

		var carbon_class,aluminium_class,wolfram_class,radium_class,tritium_class;

		if(carbon > 0) carbon_class = 'positiv';
		else if(carbon < 0) carbon_class = 'negativ';
		else carbon_class = 'null';

		if(aluminium > 0) aluminium_class = 'positiv';
		else if(aluminium < 0) aluminium_class = 'negativ';
		else aluminium_class = 'null';

		if(wolfram > 0) wolfram_class = 'positiv';
		else if(wolfram < 0) wolfram_class = 'negativ';
		else wolfram_class = 'null';

		if(radium > 0) radium_class = 'positiv';
		else if(radium < 0) radium_class = 'negativ';
		else radium_class = 'null';

		if(tritium > 0) tritium_class = 'positiv';
		else if(tritium < 0) tritium_class = 'negativ';
		else tritium_class = 'null';

		if(gesamt > 0) gesamt_class = 'positiv';
		else if(gesamt < 0) gesamt_class = 'negativ';
		else gesamt_class = 'null';

		document.getElementById('taeglich-carbon').className = 'c-carbon '+carbon_class;
		document.getElementById('taeglich-aluminium').className = 'c-aluminium '+aluminium_class;
		document.getElementById('taeglich-wolfram').className = 'c-wolfram '+wolfram_class;
		document.getElementById('taeglich-radium').className = 'c-radium '+radium_class;
		document.getElementById('taeglich-tritium').className = 'c-tritium '+tritium_class;
		document.getElementById('taeglich-gesamt').className = 'c-gesamt '+gesamt_class;
	}
// ]]>
</script>
<h3 id="ausgegebene-rohstoffe">Ausgegebene Rohstoffe</h3>
<dl class="punkte">
	<dt class="c-carbon">Carbon</dt>
	<dd class="c-carbon"><?=ths($me->getSpentRess(0))?></dd>

	<dt class="c-eisenerz">Aluminium</dt>
	<dd class="c-eisenerz"><?=ths($me->getSpentRess(1))?></dd>

	<dt class="c-wolfram">Wolfram</dt>
	<dd class="c-wolfram"><?=ths($me->getSpentRess(2))?></dd>

	<dt class="c-radium">Radium</dt>
	<dd class="c-radium"><?=ths($me->getSpentRess(3))?></dd>

	<dt class="c-tritium">Tritium</dt>
	<dd class="c-tritium"><?=ths($me->getSpentRess(4))?></dd>

	<dt class="c-gesamt">Gesamt</dt>
	<dd class="c-gesamt"><?=ths($me->getSpentRess())?></dd>
</dl>
<h3 id="forschungsverbesserungen">Forschungsverbesserungen</h3>
<dl class="imperium-rohstoffe-auswirkungsgrade">
	<dt class="c-energieproduktion">Energieproduktion</dt>
	<dd class="c-energieproduktion"><?=str_replace('.', ',', round((pow(1.05, $me->getItemLevel('F3', 'forschung'))-1)*100, 3))?>&thinsp;<abbr title="Prozent">%</abbr></dd>
</dl>
<?php
			break;
		case 'roboter':
?>
<h3 id="roboterzahlen">Roboterzahlen</h3>
<table class="imperium-tabelle imperium-roboterzahlen">
	<thead>
		<tr>
			<th class="c-planet">Planet</th>
<?php
			$roboter = $me->getItemsList('roboter');
			foreach($roboter as $id)
			{
				$item_info = $me->getItemInfo($id, 'roboter');
?>
			<th class="c-<?=utf8_htmlentities($id)?>"><a href="help/description.php?id=<?=htmlentities(urlencode($id).'&'.urlencode(session_name()).'='.urlencode(session_id()))?>" title="Genauere Informationen anzeigen"><?=utf8_htmlentities($item_info['name'])?></a></th>
<?php
			}
?>
		</tr>
	</thead>
	<tbody>
<?php
			$ges = array();
			$ges_max = array();
			$use_max_limit = !file_exists(global_setting('DB_NO_STRICT_ROB_LIMITS'));
			$planets = $me->getPlanetsList();
			foreach($planets as $planet)
			{
				$me->setActivePlanet($planet);
				$max_rob_limit = floor($me->getBasicFields()/2 *((0.01 + $me->getItemLevel('B9', 'gebaeude'))/10));
?>
		<tr<?=($planet==$active_planet) ? ' class="active"' : ''?>>
			<th class="c-planet" title="<?=utf8_htmlentities($me->planetName())?>"><a href="imperium.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>&amp;planet=<?=htmlentities(urlencode($planet))?>&amp;action=roboter"><?=utf8_htmlentities($me->getPosString())?></a></th>
<?php
				foreach($roboter as $id)
				{
					$count = $me->getItemLevel($id, 'roboter');
					if(!isset($ges[$id])) $ges[$id] = 0;
					$ges[$id] += $count;
					switch($id)
					{
						case 'R01': $max = $max_rob_limit; break;
						case 'R02': $max = ($use_max_limit ? min($max_rob_limit, $me->getItemLevel('B0')) : $me->getItemLevel('B0')); break;
						case 'R03': $max = ($use_max_limit ? min($max_rob_limit, $me->getItemLevel('B1')) : $me->getItemLevel('B1')); break;
						case 'R04': $max = ($use_max_limit ? min($max_rob_limit, $me->getItemLevel('B2')) : $me->getItemLevel('B2')); break;
						case 'R05': $max = ($use_max_limit ? min($max_rob_limit, $me->getItemLevel('B3')) : $me->getItemLevel('B3')); break;
						case 'R06': $max = ($use_max_limit ? min($max_rob_limit, $me->getItemLevel('B4')) : $me->getItemLevel('B4')); break;
					}
					if(!isset($ges_max[$id])) $ges_max[$id] = 0;
					$ges_max[$id] += $max;
?>
			<td class="c-<?=utf8_htmlentities($id)?>"><?=ths($count)?> <span class="maximal">(<?=ths($max)?>)</span></td>
<?php
				}
?>
		</tr>
<?php
			}
?>
	</tbody>
	<tfoot>
		<tr>
			<th>Gesamt</th>
<?php
			foreach($roboter as $id)
			{
?>
			<td class="c-<?=utf8_htmlentities($id)?>"><?=ths($ges[$id])?> <span class="maximal">(<?=ths($ges_max[$id])?>)</span></td>
<?php
			}
?>
		</tr>
	</tfoot>
</table>
<h3 id="roboter-auswirkungsgrade">Roboter-Auswirkungsgrade</h3>
<dl class="imperium-roboter-auswirkungsgrade">
	<dt class="c-bauroboter">Bauroboter</dt>
	<dd class="c-bauroboter"><?=str_replace('.', ',', $me->getItemLevel('F2', 'forschung')*0.025)?>&thinsp;<abbr title="Prozent">%</abbr></dd>

	<dt class="c-minenroboter">Minenroboter</dt>
	<dd class="c-minenroboter"><?=str_replace('.', ',', $me->getItemLevel('F2', 'forschung')*0.03125)?>&thinsp;<abbr title="Prozent">%</abbr></dd>
</dl>
<?php
			break;
		case 'flotte':
		$imperium = true;

?>


<h3 id="stationierte-flotten">Stationierte Flotten</h3>
<table class="imperium-tabelle imperium-stationierte-flotten">
	<thead>
		<tr>
			<th class="c-einheit">Einheit</th>
<?php
			$planets = $me->getPlanetsList();
			foreach($planets as $planet)
			{
				$me->setActivePlanet($planet);
?>
			<th<?=($planet==$active_planet) ? ' class="active"' : ''?> title="<?=utf8_htmlentities($me->planetName())?>"><a href="imperium.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>&amp;planet=<?=htmlentities(urlencode($planet))?>&amp;action=flotte"><?=utf8_htmlentities($me->getPosString())?></a></th>
<?php
			}
?>
			<th class="c-gesamt">Gesamt</th>
		</tr>
	</thead>
	<tbody>
<?php
			$ges_ges = 0;
			$ges = array();
			$einheiten = array_merge($me->getItemsList('schiffe'), $me->getItemsList('verteidigung'));
			foreach($einheiten as $id)
			{
				$item_info = $me->getItemInfo($id);
				$this_ges = 0;
?>
		<tr>
			<th class="c-einheit"><a href="help/description.php?id=<?=htmlentities(urlencode($id).'&'.urlencode(session_name()).'='.urlencode(session_id()))?>" title="Genauere Informationen anzeigen"><?=utf8_htmlentities($item_info['name'])?></a></th>
<?php
				foreach($planets as $i=>$planet)
				{
					$me->setActivePlanet($planet);
					$anzahl = $me->getItemLevel($id);
					if(!isset($ges[$i])) $ges[$i] = 0;
					$ges[$i] += $anzahl;
					$ges_ges += $anzahl;
					$this_ges += $anzahl;
?>
			<td<?=($planet==$active_planet) ? ' class="active"' : ''?>><?=ths($anzahl)?></td>
<?php
				}
?>
			<td class="c-gesamt"><?=utf8_htmlentities($this_ges)?></td>
		</tr>
<?php
			}
?>
	</tbody>
	<tfoot>
		<tr>
			<th class="c-einheit">Gesamt</th>
<?php
			foreach($planets as $i=>$planet)
			{
?>
			<td<?=($planet==$active_planet) ? ' class="active"' : ''?>><?=ths($ges[$i])?></td>
<?php
			}
?>
			<td class="c-gesamt"><?=utf8_htmlentities($ges_ges)?></td>
		</tr>
	</tfoot>
</table>
<h3 id="forschungsverbesserungen">Forschungsverbesserungen</h3>
<dl class="imperium-schiffe-auswirkungsgrade">
	<dt class="c-antriebe">Antriebe</dt>
	<dd class="c-antriebe"><?=str_replace('.', ',', round((pow(1.025, $me->getItemLevel('F6', 'forschung'))*pow(1.05, $me->getItemLevel('F7', 'forschung'))*pow(1.25, $me->getItemLevel('F8', 'forschung'))-1)*100, 3))?>&thinsp;<abbr title="Prozent">%</abbr></dd>

	<dt class="c-waffen">Waffen</dt>
	<dd class="c-waffen"><?=str_replace('.', ',', round((pow(1.05, $me->getItemLevel('F4', 'forschung'))-1)*100, 3))?>&thinsp;<abbr title="Prozent">%</abbr></dd>

	<dt class="c-schilde">Schilde</dt>
	<dd class="c-schilde"><?=str_replace('.', ',', round((pow(1.05, $me->getItemLevel('F5', 'forschung'))-1)*100, 3))?>&thinsp;<abbr title="Prozent">%</abbr></dd>

	<dt class="c-schildreparatur-pro-runde">Schildreparatur pro Runde</dt>
	<dd class="c-schildreparatur-pro-runde"><?=str_replace('.', ',', round((pow(1.025, $me->getItemLevel('F10', 'forschung'))-1)*100, 3))?>&thinsp;<abbr title="Prozent">%</abbr></dd>

	<dt class="c-laderaumvergroesserung">Laderaumvergrößerung</dt>
	<dd class="c-laderaumvergroesserung"><?=str_replace('.', ',', round((pow(1.2, $me->getItemLevel('F11', 'forschung'))-1)*100, 3))?>&thinsp;<abbr title="Prozent">%</abbr></dd>
</dl>
<?php
	}

	$me->setActivePlanet($active_planet);

	login_gui::html_foot();
?>
