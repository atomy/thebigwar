<?php
	if ( is_file( '../include/config_inc.php' ) )
	{
        	require_once( '../include/config_inc.php' );
	}
	else
	{
		require_once( 'include/config_inc.php' );
	}
	require_once( TBW_ROOT.'admin/include.php' );
	require_once( TBW_ROOT.'admin/javascript/javascript.php' );
	
	/*if(!$admin_array['permissions'][18]) {
		die('No access.');
	}*/

	admin_gui::html_head();
?>
	<fieldset><legend id="action-19">Geb&auml;ude/ Forschung ersetzten [+/-]</legend>
	<form action="index.php" method="post">
		<table cellpadding="2" cellspacing="2">
			<thead>
				<tr>
					<td>
						<dt><label for="username-input">Benutzername</label></dt>
						<dt><input type="text" name="username" id="username-input" /></dt>
						<script type="text/javascript">
						// Autocompletion
							activate_users_list(document.getElementById('username-input'));
						</script>
					</td>
					
					<td>
						<dt><label for="planet-input">Planetenposition</label></dt>
						<dt><input type="text" name="planetpos" id="planet-input" /></dt>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<center><a href="#" onClick="javascript:getLevels(); return false;">Daten aktualisieren</a></center>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<center><span name="result" id="result">&nbsp;</span></center>
					</td>
				</tr>
			</thead>
			<tbody>
					<tr>
						<td>
							<dl>
								<dt><label for="gebadd-B0">Carbonfabrik</label>&nbsp;<dt><span name="B0" id="B0"></span><a href="#" onClick="javascript:add('B0',-10)">-10</a><a href="#" onClick="javascript:add('B0',-1)">-1</a><a href="#" onClick="javascript:add('B0',1)">+1</a><a href="#" onClick="javascript:add('B0',10)">+10</a></dt>
								<dt><label for="gebadd-B1">Aluminiumgießerei</label>&nbsp;<dt><ispanname="B1" id="B1"></span><a href="#" onClick="javascript:add('B1',-10)">-10</a><a href="#" onClick="javascript:add('B1',-1)">-1</a><a href="#" onClick="javascript:add('B1',1)">+1</a><a href="#" onClick="javascript:add('B1',10)">+10</a></dt>
								<dt><label for="gebadd-B2">Wolframextrator</label>&nbsp;<dt><span name="B2" id="B2"></span><a href="#" onClick="javascript:add('B2',-10)">-10</a><a href="#" onClick="javascript:add('B2',-1)">-1</a><a href="#" onClick="javascript:add('B2',1)">+1</a><a href="#" onClick="javascript:add('B2',10)">+10</a></dt>
								<dt><label for="gebadd-B3">Radiumgrube</label>&nbsp;<dt><span name="B3" id="B3"></span><a href="#" onClick="javascript:add('B3',-10)">-10</a><a href="#" onClick="javascript:add('B3',-1)">-1</a><a href="#" onClick="javascript:add('B3',1)">+1</a><a href="#" onClick="javascript:add('B3',10)">+10</a></dt>
								<dt><label for="gebadd-B4">Tritiumgenerator</label>&nbsp;<dt><span name="B4" id="B4"></span><a href="#" onClick="javascript:add('B4',-10)">-10</a><a href="#" onClick="javascript:add('B4',-1)">-1</a><a href="#" onClick="javascript:add('B4',1)">+1</a><a href="#" onClick="javascript:add('B4',10)">+10</a></dt>
								<dt><label for="gebadd-B5">Solarkraftwerk</label>&nbsp;<dt><span name="B5" id="B5"></span><a href="#" onClick="javascript:add('B5',-10)">-10</a><a href="#" onClick="javascript:add('B5',-1)">-1</a><a href="#" onClick="javascript:add('B5',1)">+1</a><a href="#" onClick="javascript:add('B5',10)">+10</a></dt>
								<dt><label for="gebadd-B6">Sonnenwindkraftwerk</label>&nbsp;<dt><span name="B6" id="B6"></span><a href="#" onClick="javascript:add('B6',-10)">-10</a><a href="#" onClick="javascript:add('B6',-1)">-1</a><a href="#" onClick="javascript:add('B6',1)">+1</a><a href="#" onClick="javascript:add('B6',10)">+10</a></dt>
								<dt><label for="gebadd-B7">Wärmekraftwerk</label>&nbsp;<dt><span name="B7" id="B7"></span><a href="#" onClick="javascript:add('B7',-10)">-10</a><a href="#" onClick="javascript:add('B7',-1)">-1</a><a href="#" onClick="javascript:add('B7',1)">+1</a><a href="#" onClick="javascript:add('B7',10)">+10</a></dt>
								<dt><label for="gebadd-B8">Forschungslabor</label>&nbsp;<dt><span name="B8" id="B8"></span><a href="#" onClick="javascript:add('B8',-10)">-10</a><a href="#" onClick="javascript:add('B8',-1)">-1</a><a href="#" onClick="javascript:add('B8',1)">+1</a><a href="#" onClick="javascript:add('B8',10)">+10</a></dt>
								<dt><label for="gebadd-B9">Roboterfabrik</label>&nbsp;<dt><span name="B9" id="B9"><a href="#" onClick="javascript:add('B9',-10)">-10</a><a href="#" onClick="javascript:add('B9',-1)">-1</a><a href="#" onClick="javascript:add('B9',1)">+1</a><a href="#" onClick="javascript:add('B9',10)">+10</a></dt>
								<dt><label for="gebadd-B10">Werft</label>&nbsp;<dt><span name="B10" id="B10"></span><a href="#" onClick="javascript:add('B10',-10)">-10</a><a href="#" onClick="javascript:add('B10',-1)">-1</a><a href="#" onClick="javascript:add('B10',1)">+1</a><a href="#" onClick="javascript:add('B10',10)">+10</a></dt>
							</dl>
						</td>
		
						<td>
							<dl>
								<dt><label for="forsadd-F0">Kontrollwesen</label>&nbsp;<dt><span name="F0" id="F0"></span><a href="#" onClick="javascript:add('F0',-10)">-10</a><a href="#" onClick="javascript:add('F0',-1)">-1</a><a href="#" onClick="javascript:add('F0',1)">+1</a><a href="#" onClick="javascript:add('F0',10)">+10</a></dt>
								<dt><label for="forsadd-F1">Spionagetechnik</label>&nbsp;<dt><span name="F1" id="F1"></span><a href="#" onClick="javascript:add('F1',-10)">-10</a><a href="#" onClick="javascript:add('F1',-1)">-1</a><a href="#" onClick="javascript:add('F1',1)">+1</a><a href="#" onClick="javascript:add('F1',10)">+10</a></dt>
								<dt><label for="forsadd-F2">Roboterbautechnik</label>&nbsp;<dt><span name="F2" id="F2"></span><a href="#" onClick="javascript:add('F2',-10)">-10</a><a href="#" onClick="javascript:add('F2',-1)">-1</a><a href="#" onClick="javascript:add('F2',1)">+1</a><a href="#" onClick="javascript:add('F2',10)">+10</a></dt>
								<dt><label for="forsadd-F3">Energietechnik</label>&nbsp;<dt><span name="F3" id="F3"></span><a href="#" onClick="javascript:add('F3',-10)">-10</a><a href="#" onClick="javascript:add('F3',-1)">-1</a><a href="#" onClick="javascript:add('F3',1)">+1</a><a href="#" onClick="javascript:add('F3',10)">+10</a></dt>
								<dt><label for="forsadd-F4">Waffentechnik</label>&nbsp;<dt><span name="F4" id="F4"></span><a href="#" onClick="javascript:add('F4',-10)">-10</a><a href="#" onClick="javascript:add('F4',-1)">-1</a><a href="#" onClick="javascript:add('F4',1)">+1</a><a href="#" onClick="javascript:add('F4',10)">+10</a></dt>
								<dt><label for="forsadd-F5">Verteidigungsstrategie</label>&nbsp;<dt><span name="F5" id="F5"></span><a href="#" onClick="javascript:add('F5',-10)">-10</a><a href="#" onClick="javascript:add('F5',-1)">-1</a><a href="#" onClick="javascript:add('F5',1)">+1</a><a href="#" onClick="javascript:add('F5',10)">+10</a></dt>
								<dt><label for="forsadd-F10">Schildtechnik</label>&nbsp;<dt><span name="F10" id="F10"></span><a href="#" onClick="javascript:add('F10',-10)">-10</a><a href="#" onClick="javascript:add('F10',-1)">-1</a><a href="#" onClick="javascript:add('F10',1)">+1</a><a href="#" onClick="javascript:add('F10',10)">+10</a></dt>
								<dt><label for="forsadd-F6">Rückstoßantrieb</label>&nbsp;<dt><ispanname="f6" id="F6"></span><a href="#" onClick="javascript:add('F6',-10)">-10</a><a href="#" onClick="javascript:add('F6',-1)">-1</a><a href="#" onClick="javascript:add('F6',1)">+1</a><a href="#" onClick="javascript:add('F6',10)">+10</a></dt>
								<dt><label for="forsadd-F7">Ionenantrieb</label>&nbsp;<dt><span name="F7" id="F7"></span><a href="#" onClick="javascript:add('F7',-10)">-10</a><a href="#" onClick="javascript:add('F7',-1)">-1</a><a href="#" onClick="javascript:add('F7',1)">+1</a><a href="#" onClick="javascript:add('F7',10)">+10</a></dt>
								<dt><label for="forsadd-F8">Kernantrieb</label>&nbsp;<dt><span name="F8" id="F8"></span><a href="#" onClick="javascript:add('F8',-10)">-10</a><a href="#" onClick="javascript:add('F8',-1)">-1</a><a href="#" onClick="javascript:add('F8',1)">+1</a><a href="#" onClick="javascript:add('F8',10)">+10</a></dt>
								<dt><label for="forsadd-F9">Ingenieurswissenschaft</label>&nbsp;<dt><span name="F9" id="F9"></span><a href="#" onClick="javascript:add('F9',-10)">-10</a><a href="#" onClick="javascript:add('F9',-1)">-1</a><a href="#" onClick="javascript:add('F9',1)">+1</a><a href="#" onClick="javascript:add('F9',10)">+10</a></dt>
								<dt><label for="forsadd-F11">Laderaumerweiterung</label>&nbsp;<dt><span name="F11" id="F11"></span><a href="#" onClick="javascript:add('F11',-10)">-10</a><a href="#" onClick="javascript:add('F11',-1)">-1</a><a href="#" onClick="javascript:add('F11',1)">+1</a><a href="#" onClick="javascript:add('F11',10)">+10</a></dt>
							</dl>
						</td>
					</tr>
					<tr>
						<td>
							<dl>
								<dt><label for="fleetadd-S0">Kleiner Transporter</label>&nbsp;<dt><span name="S0" id="S0"></span><a href="#" onClick="javascript:add('S0',-100)">-100</a><a href="#" onClick="javascript:add('S0',-10)">-10</a><a href="#" onClick="javascript:add('S0',-1)">-1</a><a href="#" onClick="javascript:add('S0',1)">+1</a><a href="#" onClick="javascript:add('S0',10)">+10</a><a href="#" onClick="javascript:add('S0',100)">+100</a></dt>
								<dt><label for="fleetadd-S1">Großer Transporter</label>&nbsp;<dt><ispanname="f1" id="S1"></span><a href="#" onClick="javascript:add('S1',-100)">-100</a><a href="#" onClick="javascript:add('S1',-10)">-10</a><a href="#" onClick="javascript:add('S1',-1)">-1</a><a href="#" onClick="javascript:add('S1',1)">+1</a><a href="#" onClick="javascript:add('S1',10)">+10</a><a href="#" onClick="javascript:add('S1',100)">+100</a></dt>
								<dt><label for="fleetadd-S2">Transcube</label>&nbsp;<dt><span name="S2" id="S2"></span><a href="#" onClick="javascript:add('S2',-100)">-100</a><a href="#" onClick="javascript:add('S2',-10)">-10</a><a href="#" onClick="javascript:add('S2',-1)">-1</a><a href="#" onClick="javascript:add('S2',1)">+1</a><a href="#" onClick="javascript:add('S2',10)">+10</a><a href="#" onClick="javascript:add('S2',100)">+100</a></dt>
								<dt><label for="fleetadd-S3">Sammler</label>&nbsp;<dt><span name="S3" id="S3"></span><a href="#" onClick="javascript:add('S3',-100)">-100</a><a href="#" onClick="javascript:add('S3',-10)">-10</a><a href="#" onClick="javascript:add('S3',-1)">-1</a><a href="#" onClick="javascript:add('S3',1)">+1</a><a href="#" onClick="javascript:add('S3',10)">+10</a><a href="#" onClick="javascript:add('S3',100)">+100</a></dt>
								<dt><label for="fleetadd-S5">Spionagesonde</label>&nbsp;<dt><span name="S5" id="S5"></span><a href="#" onClick="javascript:add('S5',-100)">-100</a><a href="#" onClick="javascript:add('S5',-10)">-10</a><a href="#" onClick="javascript:add('S5',-1)">-1</a><a href="#" onClick="javascript:add('S5',1)">+1</a><a href="#" onClick="javascript:add('S5',10)">+10</a><a href="#" onClick="javascript:add('S5',100)">+100</a></dt>
								<dt><label for="fleetadd-S6">Besiedelungsschiff</label>&nbsp;<dt><span name="S6" id="S6"></span><a href="#" onClick="javascript:add('S6',-100)">-100</a><a href="#" onClick="javascript:add('S6',-10)">-10</a><a href="#" onClick="javascript:add('S6',-1)">-1</a><a href="#" onClick="javascript:add('S6',1)">+1</a><a href="#" onClick="javascript:add('S6',10)">+10</a><a href="#" onClick="javascript:add('S6',100)">+100</a></dt>
								<dt><label for="fleetadd-S7">Kampfkapsel</label>&nbsp;<dt><span name="S7" id="S7"></span><a href="#" onClick="javascript:add('S7',-100)">-100</a><a href="#" onClick="javascript:add('S7',-10)">-10</a><a href="#" onClick="javascript:add('S7',-1)">-1</a><a href="#" onClick="javascript:add('S7',1)">+1</a><a href="#" onClick="javascript:add('S7',10)">+10</a><a href="#" onClick="javascript:add('S7',100)">+100</a></dt>
								<dt><label for="fleetadd-S8">Leichter Jäger</label>&nbsp;<dt><span name="S8" id="S8"></span><a href="#" onClick="javascript:add('S8',-100)">-100</a><a href="#" onClick="javascript:add('S8',-10)">-10</a><a href="#" onClick="javascript:add('S8',-1)">-1</a><a href="#" onClick="javascript:add('S8',1)">+1</a><a href="#" onClick="javascript:add('S8',10)">+10</a><a href="#" onClick="javascript:add('S8',100)">+100</a></dt>
								<dt><label for="fleetadd-S9">Schwerer Jäger</label>&nbsp;<dt><span name="S9" id="S9"></span><a href="#" onClick="javascript:add('S9',-100)">-100</a><a href="#" onClick="javascript:add('S9',-10)">-10</a><a href="#" onClick="javascript:add('S9',-1)">-1</a><a href="#" onClick="javascript:add('S9',1)">+1</a><a href="#" onClick="javascript:add('S9',10)">+10</a><a href="#" onClick="javascript:add('S9',100)">+100</a></dt>
								<dt><label for="fleetadd-S10">Leichte Fregatte</label>&nbsp;<dt><span name="S10" id="S10"></span><a href="#" onClick="javascript:add('S10',-100)">-100</a><a href="#" onClick="javascript:add('S10',-10)">-10</a><a href="#" onClick="javascript:add('S10',-1)">-1</a><a href="#" onClick="javascript:add('S10',1)">+1</a><a href="#" onClick="javascript:add('S10',10)">+10</a><a href="#" onClick="javascript:add('S10',100)">+100</a></dt>
								<dt><label for="fleetadd-S11">Schwere Fregatte</label>&nbsp;<dt><span name="S11" id="S11"></span><a href="#" onClick="javascript:add('S11',-100)">-100</a><a href="#" onClick="javascript:add('S11',-10)">-10</a><a href="#" onClick="javascript:add('S11',-1)">-1</a><a href="#" onClick="javascript:add('S11',1)">+1</a><a href="#" onClick="javascript:add('S11',10)">+10</a><a href="#" onClick="javascript:add('S11',100)">+100</a></dt>
								<dt><label for="fleetadd-S12">Leichter Kreuzer</label>&nbsp;<dt><span name="S12" id="S12"></span><a href="#" onClick="javascript:add('S12',-100)">-100</a><a href="#" onClick="javascript:add('S12',-10)">-10</a><a href="#" onClick="javascript:add('S12',-1)">-1</a><a href="#" onClick="javascript:add('S12',1)">+1</a><a href="#" onClick="javascript:add('S12',10)">+10</a><a href="#" onClick="javascript:add('S12',100)">+100</a></dt>
								<dt><label for="fleetadd-S13">Schwerer Kreuzer</label>&nbsp;<dt><span name="S13" id="S13"></span><a href="#" onClick="javascript:add('S13',-100)">-100</a><a href="#" onClick="javascript:add('S13',-10)">-10</a><a href="#" onClick="javascript:add('S13',-1)">-1</a><a href="#" onClick="javascript:add('S13',1)">+1</a><a href="#" onClick="javascript:add('S13',10)">+10</a><a href="#" onClick="javascript:add('S13',100)">+100</a></dt>
								<dt><label for="fleetadd-S14">Schlachtschiff</label>&nbsp;<dt><span name="S14" id="S14"></span><a href="#" onClick="javascript:add('S14',-100)">-100</a><a href="#" onClick="javascript:add('S14',-10)">-10</a><a href="#" onClick="javascript:add('S14',-1)">-1</a><a href="#" onClick="javascript:add('S14',1)">+1</a><a href="#" onClick="javascript:add('S14',10)">+10</a><a href="#" onClick="javascript:add('S14',100)">+100</a></dt>
								<dt><label for="fleetadd-S15">Zerstörer</label>&nbsp;<dt><span name="S15" id="S15"></span><a href="#" onClick="javascript:add('S15',-100)">-100</a><a href="#" onClick="javascript:add('S15',-10)">-10</a><a href="#" onClick="javascript:add('S15',-1)">-1</a><a href="#" onClick="javascript:add('S15',1)">+1</a><a href="#" onClick="javascript:add('S15',10)">+10</a><a href="#" onClick="javascript:add('S15',100)">+100</a></dt>
								<dt><label for="fleetadd-S16">Warcube</label>&nbsp;<dt><span name="S16" id="S16"></span><a href="#" onClick="javascript:add('S16',-100)">-100</a><a href="#" onClick="javascript:add('S16',-10)">-10</a><a href="#" onClick="javascript:add('S16',-1)">-1</a><a href="#" onClick="javascript:add('S16',1)">+1</a><a href="#" onClick="javascript:add('S16',10)">+10</a><a href="#" onClick="javascript:add('S16',100)">+100</a></dt>
							</dl>
						</td>
					
						<td>
							<dl>
								<dt><label for="vertadd-V0">Einfaches Lasergeschütz</label>&nbsp;<dt><span name="V0" id="V0"></span><a href="#" onClick="javascript:add('V0',-100)">-100</a><a href="#" onClick="javascript:add('V0',-10)">-10</a><a href="#" onClick="javascript:add('V0',-1)">-1</a><a href="#" onClick="javascript:add('V0',1)">+1</a><a href="#" onClick="javascript:add('V0',10)">+10</a><a href="#" onClick="javascript:add('V0',100)">+100</a></dt>
								<dt><label for="vertadd-V1">Gatling</label>&nbsp;<dt><span name="V1" id="V1"></span><a href="#" onClick="javascript:add('V1',-100)">-100</a><a href="#" onClick="javascript:add('V1',-10)">-10</a><a href="#" onClick="javascript:add('V1',-1)">-1</a><a href="#" onClick="javascript:add('V1',1)">+1</a><a href="#" onClick="javascript:add('V1',10)">+10</a><a href="#" onClick="javascript:add('V1',100)">+100</a></dt>
								<dt><label for="vertadd-V2">Mehrfachraketenwerfer</label>&nbsp;<dt><span name="V2" id="V2"></span><a href="#" onClick="javascript:add('V2',-100)">-100</a><a href="#" onClick="javascript:add('V2',-10)">-10</a><a href="#" onClick="javascript:add('V2',-1)">-1</a><a href="#" onClick="javascript:add('V2',1)">+1</a><a href="#" onClick="javascript:add('V2',10)">+10</a><a href="#" onClick="javascript:add('V2',100)">+100</a></dt>
								<dt><label for="vertadd-V6">Schweres Lasergeschütz</label>&nbsp;<dt><span name="V6" id="V6"></span><a href="#" onClick="javascript:add('V6',-100)">-100</a><a href="#" onClick="javascript:add('V6',-10)">-10</a><a href="#" onClick="javascript:add('V6',-1)">-1</a><a href="#" onClick="javascript:add('V6',1)">+1</a><a href="#" onClick="javascript:add('V6',10)">+10</a><a href="#" onClick="javascript:add('V6',100)">+100</a></dt>
								<dt><label for="vertadd-V3">EMP</label>&nbsp;<dt><span name="V3" id="V3"></span><a href="#" onClick="javascript:add('V3',-100)">-100</a><a href="#" onClick="javascript:add('V3',-10)">-10</a><a href="#" onClick="javascript:add('V3',-1)">-1</a><a href="#" onClick="javascript:add('V3',1)">+1</a><a href="#" onClick="javascript:add('V3',10)">+10</a><a href="#" onClick="javascript:add('V3',100)">+100</a></dt>
								<dt><label for="vertadd-V4">Ionenkanone</label>&nbsp;<dt><span name="V4" id="V4"></span><a href="#" onClick="javascript:add('V4',-100)">-100</a><a href="#" onClick="javascript:add('V4',-10)">-10</a><a href="#" onClick="javascript:add('V4',-1)">-1</a><a href="#" onClick="javascript:add('V4',1)">+1</a><a href="#" onClick="javascript:add('V4',10)">+10</a><a href="#" onClick="javascript:add('V4',100)">+100</a></dt>
								<dt><label for="vertadd-V5">Radonkanone</label>&nbsp;<dt><span name="V5" id="V5"></span><a href="#" onClick="javascript:add('V5',-100)">-100</a><a href="#" onClick="javascript:add('V5',-10)">-10</a><a href="#" onClick="javascript:add('V5',-1)">-1</a><a href="#" onClick="javascript:add('V5',1)">+1</a><a href="#" onClick="javascript:add('V5',10)">+10</a><a href="#" onClick="javascript:add('V5',100)">+100</a></dt>
							</dl>
						</td>	
					</tr>
					<tr>
						<td>
							<dl>
								<dt><label for="robadd-R01">Bauroboter</label>&nbsp;<dt><span name="R01" id="R01"></span><a href="#" onClick="javascript:add('R01',-100)">-100</a><a href="#" onClick="javascript:add('R01',-10)">-10</a><a href="#" onClick="javascript:add('R01',-1)">-1</a><a href="#" onClick="javascript:add('R01',1)">+1</a><a href="#" onClick="javascript:add('R01',10)">+10</a><a href="#" onClick="javascript:add('R01',100)">+100</a></dt>
								<dt><label for="robadd-R02">Carbonroboter</label>&nbsp;<dt><span name="R02" id="R02"></span><a href="#" onClick="javascript:add('R02',-100)">-100</a><a href="#" onClick="javascript:add('R02',-10)">-10</a><a href="#" onClick="javascript:add('R02',-1)">-1</a><a href="#" onClick="javascript:add('R02',1)">+1</a><a href="#" onClick="javascript:add('R02',10)">+10</a><a href="#" onClick="javascript:add('R02',100)">+100</a></dt>
								<dt><label for="robadd-R03">Aluminiumroboter</label>&nbsp;<dt><span name="R03" id="R03"></span><a href="#" onClick="javascript:add('R03',-100)">-100</a><a href="#" onClick="javascript:add('R03',-10)">-10</a><a href="#" onClick="javascript:add('R03',-1)">-1</a><a href="#" onClick="javascript:add('R03',1)">+1</a><a href="#" onClick="javascript:add('R03',10)">+10</a><a href="#" onClick="javascript:add('R03',100)">+100</a></dt>
								<dt><label for="robadd-R04">Wolramroboter</label>&nbsp;<dt><span name="R04" id="R04"></span><a href="#" onClick="javascript:add('R04',-100)">-100</a><a href="#" onClick="javascript:add('R04',-10)">-10</a><a href="#" onClick="javascript:add('R04',-1)">-1</a><a href="#" onClick="javascript:add('R04',1)">+1</a><a href="#" onClick="javascript:add('R04',10)">+10</a><a href="#" onClick="javascript:add('R04',100)">+100</a></dt>
								<dt><label for="robadd-R05">Radiumroboter</label>&nbsp;<dt><span name="R05" id="R05"></span><a href="#" onClick="javascript:add('R05',-100)">-100</a><a href="#" onClick="javascript:add('R05',-10)">-10</a><a href="#" onClick="javascript:add('R05',-1)">-1</a><a href="#" onClick="javascript:add('R05',1)">+1</a><a href="#" onClick="javascript:add('R05',10)">+10</a><a href="#" onClick="javascript:add('R05',100)">+100</a></dt>
								<dt><label for="robadd-R06">Tritiumroboter</label>&nbsp;<dt><span name="R06" id="R06"></span><a href="#" onClick="javascript:add('R06',-100)">-100</a><a href="#" onClick="javascript:add('R06',-10)">-10</a><a href="#" onClick="javascript:add('R06',-1)">-1</a><a href="#" onClick="javascript:add('R06',1)">+1</a><a href="#" onClick="javascript:add('R06',10)">+10</a><a href="#" onClick="javascript:add('R06',100)">+100</a></dt>
							</dl>
						</td>
					</tr>
			</tbody>
			
			<tfoot>
					<tr><td style="text-align: center" colspan="20"><div><button type="submit">ersetzen</button></div></td></tr>
			</tfoot>
	</table>	
	</form>
<?php
	admin_gui::html_foot();
?>