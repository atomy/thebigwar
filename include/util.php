<?php
	function IsGameOperator($name) {
		if(strtolower($name)=="stoffel" || strtolower($name)=="atomy") {
			return true;
		}
		return false;
	}
?>