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

</td>
</tr>
</table>
</td>
</tr>
</table>


<div class="news"  style="width:400px; position:absolute; top:200px; left:200px;">
<h2>T-B-W &ndash; News</h2>
<?php
        $news_array = array();
        if(is_file(global_setting("DB_NEWS")) && filesize(global_setting("DB_NEWS")) > 0 && is_readable(global_setting("DB_NEWS")))
                $news_array = array_reverse(unserialize(gzuncompress(file_get_contents(global_setting("DB_NEWS")))));
?>
<br/>
<ul>
<?php
        foreach($news_array as $news)
        {
                echo '<li class="entry">';

                if(!is_array($news) || !isset($news['text_parsed']))
                        continue;

                $title = 'Kein Titel';
                if(isset($news['title']) && trim($news['title']) != '')
                        $title = trim($news['title']);

                $author = '';
                if(isset($news['author']) && trim($news['author']) != '')
                        $author = trim($news['author']);

                print '<div class="topic">'.utf8_htmlentities($title).'</div>';

                if ( $author != '' )
                                print '<div class="author"> von '.utf8_htmlentities($author).'</div>';
                if(isset($news['time']))
                {
                        print '<div class="time">'.date('d.m.Y - H:i:s', $news['time']).'</div>';
                }

                print('<div class="content">'.str_replace("\n", "\n\t", $news['text_parsed']).'</div>');

                echo "</li>";
        }
?>
</ul>
</div>


</body>
</html>
