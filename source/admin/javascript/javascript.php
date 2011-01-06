<?php
	require_once( '../include/config_inc.php' );
	require_once( TBW_ROOT.'admin/include.php' );
	
?>
	<script type="text/javascript">
		var root = '<?php echo h_root; ?>';
		var user = null;
		var planet = null;
		var session_name = '<?php echo urlencode(session_name()); ?>';
		var session_id = '<?php echo urlencode(session_id()); ?>';

		function getLevels() {
			//alert(root + "/admin/javascript/ajax.php");
			user = document.getElementById('username-input').value;
			planet = document.getElementById('planet-input').value;
			$.ajax({
			  type: "GET",
			  url: root + "/admin/javascript/ajax.php",
			  data: "action=level&user=" + user + "&planet=" + planet + "&" + session_name + "=" + session_id,
			  dataType: "xml",
			  success: handleLoadResponse
			});
		}

		function handleLoadResponse(data) {
			//var items = data.responseXML.getElementsByTagName('item');
			$(data).find('item').each(setLevels);
			setInfo(data);
		}

		function setInfo(data) {
			var info = $('info',data);
			$('#result').html(info.text());
			//$('#result').html(data);
		}


		function setLevels(index,node) {
			//for(var i=0; i < items.length; i++) {
				var id = node.getAttribute('id');
				$('#' + id).html('(' + node.getAttribute('wert') + ')');
			//}
		}

		function add(id,value) {
			$.ajax({
			  type: "GET",
			  url: root + "/admin/javascript/ajax.php",
			  data: "action=add&id=" + id + "&value=" + value + "&user=" + user + "&planet=" + planet + "&" + session_name + "=" + session_id,
			  dataType: "xml",
			  success: handleAddResponse
			});
		}

		function handleAddResponse(data) {
			setInfo(data);
			getLevels();
		}
	</script>