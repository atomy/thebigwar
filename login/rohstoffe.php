<?php
	require_once( '../include/config_inc.php' );
	require( TBW_ROOT.'login/scripts/include.php' );

	if(isset($_POST['prod']) && is_array($_POST['prod']) && count($_POST['prod']) > 0)
	{
		$changed = false;
		foreach($_POST['prod'] as $id=>$prod)
			$me->setProductionFactor($id, $prod);

		if(isset($_POST['show_days']))
			$me->setSetting('prod_show_days', $_POST['show_days']);
	}

	login_gui::html_head();
?>
<h2>Rohstoffproduktion pro Stunde</h2>
<form action="rohstoffe.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" method="post">
	<table class="ress-prod">
		<thead>
			<tr>
				<th class="c-gebaeude">Gebäude</th>
				<th class="c-carbon">Carbon</th>
				<th class="c-aluminium">Aluminium</th>
				<th class="c-wolfram">Wolfram</th>
				<th class="c-radium">Radium</th>
				<th class="c-tritium">Tritium</th>
				<th class="c-energie">Energie</th>
				<th class="c-produktion">Prod<kbd>u</kbd>ktion</th>
			</tr>

		</thead>
		<tbody>
<?php
	function get_prod_class($prod)
	{
		if($prod > 0)
			return 'positiv';
		elseif($prod < 0)
			return 'negativ';
		else
			return 'null';
	}

	$tabindex = 1;
	$ges_prod = $me->getProduction();
	$ges_prod[5] = round($ges_prod[5]*$ges_prod[6]);
	$gebaeude = $me->getItemsList('gebaeude');
	foreach($gebaeude as $id)
	{
		$item_info = $me->getItemInfo($id, 'gebaeude');
		
		if($item_info['level'] <= 0 || !$item_info['has_prod'])
			continue; # Es wird nichts produziert, also nicht anzeigen
		$prod = $me->checkProductionFactor($id);
?>
			<tr>
				<td class="c-gebaeude"><a href="help/description.php?id=<?=htmlentities(urlencode($id))?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Genauere Informationen anzeigen"><?=utf8_htmlentities($item_info['name'])?></a> <span class="stufe">(Stufe&nbsp;<?=utf8_htmlentities($item_info['level'])?>)</span></td>
				<td class="c-carbon <?=get_prod_class($item_info['prod'][0])?>"><?=ths($item_info['prod'][0]*$ges_prod[6])?></td>
				<td class="c-aluminium <?=get_prod_class($item_info['prod'][1])?>"><?=ths($item_info['prod'][1]*$ges_prod[6])?></td>
				<td class="c-wolfram <?=get_prod_class($item_info['prod'][2])?>"><?=ths($item_info['prod'][2]*$ges_prod[6])?></td>
				<td class="c-radium <?=get_prod_class($item_info['prod'][3])?>"><?=ths($item_info['prod'][3]*$ges_prod[6])?></td>
				<td class="c-tritium <?=get_prod_class($item_info['prod'][4])?>"><?=ths($item_info['prod'][4]*$ges_prod[6])?></td>
				<td class="c-energie <?=get_prod_class($item_info['prod'][5])?>"><?=ths($item_info['prod'][5]*$ges_prod[6])?></td>
				<td class="c-produktion">
					<select name="prod[<?=utf8_htmlentities($id)?>]" onchange="this.form.submit();" tabindex="<?=$tabindex?>"<?=($tabindex == 1) ? ' accesskey="u"' : ''?>>
<?php
		for($i=1,$h=100; $i>=0; $i-=.05,$h-=5)
		{
			$i = round($i, 4);
?>
						<option value="<?=htmlentities($i)?>"<?=($prod == $i) ? ' selected="selected"' : ''?>><?=htmlentities($h)?>&thinsp;%</option>
<?php
			$diff = $i-$prod;
			if($diff >= 0.0001 && $diff <= 0.0499)
			{
?>
						<option value="<?=htmlentities($prod)?>" selected="selected"><?=htmlentities(str_replace('.', ',', $prod*100))?>&thinsp;%</option>
<?php
			}
		}
?>
					</select>
				</td>
			</tr>
<?php
		$tabindex++;
	}
?>
		</tbody>
		<tfoot>
			<tr class="c-stunde">
				<th>Gesamt pro Stunde</th>
				<td class="c-carbon <?=get_prod_class($ges_prod[0])?>"><?=ths($ges_prod[0])?></td>
				<td class="c-aluminium <?=get_prod_class($ges_prod[1])?>"><?=ths($ges_prod[1])?></td>
				<td class="c-wolfram <?=get_prod_class($ges_prod[2])?>"><?=ths($ges_prod[2])?></td>
				<td class="c-radium <?=get_prod_class($ges_prod[3])?>"><?=ths($ges_prod[3])?></td>
				<td class="c-tritium <?=get_prod_class($ges_prod[4])?>"><?=ths($ges_prod[4])?></td>
				<td class="c-energie <?=get_prod_class($ges_prod[5])?>"><?=ths($ges_prod[5])?></td>
				<td class="c-produktion"></td>
			</tr>
			<tr class="c-tag">
<?php
	$day_prod = array($ges_prod[0]*24, $ges_prod[1]*24, $ges_prod[2]*24, $ges_prod[3]*24, $ges_prod[4]*24);
	$show_day_prod = $day_prod;
	$show_days = $me->checkSetting('prod_show_days');
	$show_day_prod[0] *= $show_days;
	$show_day_prod[1] *= $show_days;
	$show_day_prod[2] *= $show_days;
	$show_day_prod[3] *= $show_days;
	$show_day_prod[4] *= $show_days;
?>
				<th>Gesamt pr<kbd>o</kbd> <input type="text" class="prod-show-days" name="show_days" id="show_days" value="<?=utf8_htmlentities($show_days)?>" tabindex="<?=$tabindex?>" accesskey="o" onchange="recalc_perday();" onclick="recalc_perday();" onkeyup="recalc_perday();" />&nbsp;Tage</th>
				<td class="c-carbon <?=get_prod_class($show_day_prod[0])?>" id="taeglich-carbon"><?=ths($show_day_prod[0])?></td>
				<td class="c-aluminium <?=get_prod_class($show_day_prod[1])?>" id="taeglich-aluminium"><?=ths($show_day_prod[1])?></td>
				<td class="c-wolfram <?=get_prod_class($show_day_prod[2])?>" id="taeglich-wolfram"><?=ths($show_day_prod[2])?></td>
				<td class="c-radium <?=get_prod_class($show_day_prod[3])?>" id="taeglich-radium"><?=ths($show_day_prod[3])?></td>
				<td class="c-tritium <?=get_prod_class($show_day_prod[4])?>" id="taeglich-tritium"><?=ths($show_day_prod[4])?></td>
				<td class="c-speichern" colspan="2"><button type="submit" tabindex="<?=$tabindex+1?>" accesskey="n">Speicher<kbd>n</kbd></button></td>
			</tr>
		</tfoot>
	</table>
</form>
<script type="text/javascript">
// <![CDATA[
	function recalc_perday()
	{
		var show_days = parseFloat(document.getElementById('show_days').value);

		var carbon,aluminium,wolfram,radium,tritium;
		if(isNaN(show_days))
		{
			carbon = 0;
			aluminium = 0;
			wolfram = 0;
			radium = 0;
			tritium = 0;
		}
		else
		{
			carbon = <?=floor($day_prod[0])?>*show_days;
			aluminium = <?=floor($day_prod[1])?>*show_days;
			wolfram = <?=floor($day_prod[2])?>*show_days;
			radium = <?=floor($day_prod[3])?>*show_days;
			tritium = <?=floor($day_prod[4])?>*show_days;
		}

		document.getElementById('taeglich-carbon').firstChild.data = ths(carbon);
		document.getElementById('taeglich-aluminium').firstChild.data = ths(aluminium);
		document.getElementById('taeglich-wolfram').firstChild.data = ths(wolfram);
		document.getElementById('taeglich-radium').firstChild.data = ths(radium);
		document.getElementById('taeglich-tritium').firstChild.data = ths(tritium);

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

		document.getElementById('taeglich-carbon').className = 'c-carbon '+carbon_class;
		document.getElementById('taeglich-aluminium').className = 'c-aluminium '+aluminium_class;
		document.getElementById('taeglich-wolfram').className = 'c-wolfram '+wolfram_class;
		document.getElementById('taeglich-radium').className = 'c-radium '+radium_class;
		document.getElementById('taeglich-tritium').className = 'c-tritium '+tritium_class;
	}
// ]]>
</script>


<?php
	login_gui::html_foot();
?>
