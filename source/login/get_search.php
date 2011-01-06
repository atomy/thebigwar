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

<?php
 $name = $_POST["search_name"];
 $alli = $_POST["search_alli"];

if ( $name != "" )
{
    if ( $alli != "")
    {
        $error = 'Es kann nur nach einem Spieler oder einer Allianz gesucht werden!';
        $speed = '2';
        $url_s = strip_tags("search.php?");
        
    }
    else
    {
        $info = 'Einen Moment bitte...die Suchanfrage wird bearbeitet.';
        $error = '';
        $speed = '0';
        $url_s = strip_tags("help/playerinfo.php?player=$name&");
    }
}
else
{
    if ( $alli == "")
    {
        $error = 'Bitte eine Suche eingeben!';
        $speed = '2';
        $url_s = strip_tags("search.php?");
    }
    else
    {
        $info = 'Einen Moment bitte...Suchanfrage wird bearbeitet.';
        $error = '';
        $speed = '0';
        $url_s = strip_tags("help/allianceinfo.php?alliance=$alli&");
    }
}
?>
<html>
<head>
<meta http-equiv="refresh" content="<?php echo $speed; ?>; URL=http://<?php=htmlentities($_SERVER['HTTP_HOST'].h_root)?>/login/<?php echo $url_s; ?><?php=htmlentities(session_name().'='.urlencode(session_id()))?>">
</head>
<body>
<?php
    echo '<div class="successful">';
    echo '<p></p>';
    echo $info;
    echo '</div><p></p>';
    echo '<div class="error">';
    echo '<p></p>';
    echo $error;
    echo '</div><p></p>';
?>
</body>
</html>

<?php
    login_gui::html_foot();
?>