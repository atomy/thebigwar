<?php
    require_once( '../include/config_inc.php' );
    require( TBW_ROOT.'admin/include.php' );

    if(!$admin_array['permissions'][14])
        die('No access.');

    $news_array = array();
    if(is_file(global_setting("DB_NEWS")) && filesize(global_setting("DB_NEWS")) > 0 && is_readable(global_setting("DB_NEWS")))
        $news_array = array_values(unserialize(gzuncompress(file_get_contents(global_setting("DB_NEWS")))));

    if(isset($_POST['news']))
    {
        foreach($_POST['news'] as $i=>$news)
        {
            if(isset($_POST['delete']) && $_POST['delete'] && isset($news_array[$i]))
            {
                protocol("14.3", (isset($news_array[$i]['title']) ? $news_array[$i]['title'] : ''));
                unset($news_array[$i]);
                continue;
            }
            if(!isset($news[1]))
                continue;
            if(!isset($news_array[$i]))
            {
                $news_array[$i] = array();
                protocol("14.1", (isset($news[0]) ? $news[0] : ''));
            }
            else protocol("14.2", (isset($news_array[$i]['title']) ? $news_array[$i]['title'] : ''), (isset($news[0]) ? $news[0] : ''));
            if(!isset($news_array[$i]['time']))
                $news_array[$i]['time'] = time();
            if(!isset($news_array[$i]['author']))
                $news_array[$i]['author'] = $_SESSION['admin_username'];
            if(isset($news[0]))
                $news_array[$i]['title'] = $news[0];
            $news_array[$i]['text'] = $news[1];
            $news_array[$i]['text_parsed'] = parse_html($news_array[$i]['text']);
        }
        $news_array = array_values($news_array);
        $fh = fopen(global_setting("DB_NEWS"), 'w');
        if($fh)
        {
            flock($fh, LOCK_EX);
            fwrite($fh, gzcompress(serialize($news_array)));
            flock($fh, LOCK_UN);
            fclose($fh);
        }
    }

    admin_gui::html_head();
?>
<form action="news.php" method="post">
    <fieldset>
        <legend>Neuigkeit hinzufügen</legend>
        <dl>
            <dt><label for="heading-<?=count($news_array)?>-input">Überschrift</label></dt>
            <dd><input type="text" name="news[<?=count($news_array)?>][0]" id="heading-<?=count($news_array)?>-input" /></dd>

            <dt><label for="text-<?=count($news_array)?>-textarea">Text</label></dt>
            <dd><textarea name="news[<?=count($news_array)?>][1]" id="text-<?=count($news_array)?>-textarea" rows="15" cols="50"></textarea></dd>
        </dl>
        <ul>
            <li><button type="submit">Speichern</button></li>
        </ul>
    </fieldset>
</form>
<?php
    $news_keys = array_reverse(array_keys($news_array));
    $news_array = array_reverse($news_array);
    foreach($news_array as $i2=>$news)
    {
        $i = $news_keys[$i2];

        if(!isset($news['text']))
            $news['text'] = '';
?>
<form action="news.php" method="post">
    <fieldset>
<?php
        if(isset($news['time']) || (isset($news['author']) && trim($news['author']) != ''))
        {
            echo "\t\t<legend>";
            if(isset($news['time']))
            {
                echo date('Y-m-d, H:i:s', $news['time']);
                if(isset($news['author']) && trim($news['author']) != '')
                    echo '; ';
            }
            if(isset($news['author']) && trim($news['author']) != '')
                echo trim($news['author']);
            echo "</legend>\n";
        }
?>
        <dl>
            <dt><label for="heading-<?=$i?>-input">Überschrift</label></dt>
            <dd><input type="text" name="news[<?=$i?>][0]" id="heading-<?=$i?>-input"<?=isset($news['title']) ? ' value="'.utf8_htmlentities($news['title']).'"' : ''?> /></dd>

            <dt><label for="text-<?=$i?>-textarea">Text</label></dt>
            <dd><textarea name="news[<?=$i?>][1]" id="text-<?=$i?>-textarea" rows="15" cols="50"><?=preg_replace("/[\r\n\t]/e", '"&#".ord("$0").";"', utf8_htmlentities($news['text']))?></textarea></dd>
        </dl>
        <ul>
            <li><button type="submit">Speichern</button></li>
            <li><button type="submit" name="delete" value="1">Löschen</button></li>
        </ul>
    </fieldset>
</form>
<?php
    }

    admin_gui::html_foot();
?>