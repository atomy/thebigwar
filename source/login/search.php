<?php
	require_once( '../include/config_inc.php' );
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
<html>
<body>
<center>
<table border="0" cellpadding="5" cellspacing="5">
<tr>
<th>
<fieldset>
<legend>Spieler suchen</legend>
<form action="get_search.php?<?php=htmlentities(session_name().'='.urlencode(session_id()))?>" method="post">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Spielername:&nbsp;<input type="text" name="search_name" id="search-user"/>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" value="Suchen" />
	<br /><br />
<script type="text/javascript">
        // Autocompletion
        activate_users_list(document.getElementById('search-user'));
</script>
</fieldset>
<br />
<fieldset>
<legend>Allianz suchen</legend>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Allianzkürzel:&nbsp;<input type="text" name="search_alli" id="search-alliance"/>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" value="Suchen" />
	<br /><br />
<script type="text/javascript">
        // Autocompletion
        activate_alliances_list(document.getElementById('search-alliance'));
</script>
</form>
</fieldset>
</th>
</tr>
</table>
</center>
</body>
</html>



<?php
	login_gui::html_foot();
?>
