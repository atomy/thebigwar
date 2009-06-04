<?php
	require('include.php');

	function repl_nl($nls)
	{
		$len = strlen($nls);
		if($len == 1)
			return "<br />\n\t\t";
		elseif($len == 2)
			return "\n\t</p>\n\t<p>\n\t\t";
		elseif($len > 2)
			return "\n\t</p>\n".str_repeat('<br />', $len-2)."\n\t<p>\n\t\t";
	}

	$SHOW_META_DESCRIPTION = true;
	gui::html_head();
?>



<h2><abbr title="The Big War" xml:lang="en">T-B-W</abbr> &ndash; News</h2>
<?php
	$news_array = array();
	if(is_file(global_setting("DB_NEWS")) && filesize(global_setting("DB_NEWS")) > 0 && is_readable(global_setting("DB_NEWS")))
		$news_array = array_reverse(unserialize(gzuncompress(file_get_contents(global_setting("DB_NEWS")))));
?>
<ul class="newsbox">
<?php
	foreach($news_array as $news)
	{
		if(!is_array($news) || !isset($news['text_parsed']))
			continue;

		$title = 'Kein Titel';
		if(isset($news['title']) && trim($news['title']) != '')
			$title = trim($news['title']);

		$author = '';
		if(isset($news['author']) && trim($news['author']) != '')
			$author = trim($news['author']);
?>
			<?
#			=utf8_htmlentities($title)
			?>
			<?
#			=($author != '') ? ' <span class="author">('.utf8_htmlentities($author).')</span>' : ''
			?>
<?php
#		if(isset($news['time']))
#		{
?>
		<?
#		=date('Y-m-d, H:i:s', $news['time'])
		?>
    		<li>
	        	<?php echo utf8_htmlentities($title)?><br>
		</li>

<?php
#		}

#	print("\t".str_replace("\n", "\n\t", $news['text_parsed']));
?>
<?php
	}
?>
</ul>
<?php
	gui::html_foot();
?>
