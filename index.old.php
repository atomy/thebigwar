<?php
	require_once( 'include/config_inc.php' );
	require( TBW_ROOT.'include.php' );

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

<br/>
<?php
        if ( defined( 'TBW_EXT_NEWSURL' ) )
        {
        ?> 
       
<iframe name="newsbox" id="newsbox" height="1500" scrolling="no" frameborder="0" style="width:500px; position:absolute; top:200px; left:200px;" src="<?php print TBW_EXT_NEWSURL;?>" >
  <p>Ihr Browser kann leider keine eingebetteten Frames anzeigen:
  Sie k&ouml;nnen die eingebettete Seite &uuml;ber den folgenden Verweis
  aufrufen: <a href="<?php print TBW_EXT_NEWSURL;?>">News</a></p>
</iframe>
        <?php 
		}
		?>
</div>

<div class="donate" style="position:absolute; top:450px; left:5px;">
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
<input type="hidden" name="cmd" value="_donations">
<input type="hidden" name="business" value="spenden@thebigwar.org">
<input type="hidden" name="lc" value="DE">
<input type="hidden" name="item_name" value="thebigwar.org">
<input type="hidden" name="currency_code" value="EUR">
<input type="hidden" name="bn" value="PP-DonationsBF:btn_donate_LG.gif:NonHostedGuest">
<input type="image" src="https://www.paypal.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="Jetzt einfach, schnell und sicher online bezahlen – mit PayPal.">
<img alt="" border="0" src="https://www.paypal.com/de_DE/i/scr/pixel.gif" width="1" height="1">
</form>
</div>

<div class="gdynamite"  style="position:absolute; top:490px; left:10px;">
<a href="http://bgs.gdynamite.de/charts_vote_1066.html" target="_blank"><img src="http://voting.gdynamite.de/images/gd_animbutton.gif" border="0"></a>
</div>
<div class="gnews" style="position:absolute; top:530px; left:10px;">
<a href=http://www.galaxy-news.de/charts/?op=vote&game_id=3353 target="_blank"><img src="images/vote.gif" style="border:0;" alt="Die besten Browsergames in den Galaxy-News MMOG-Charts!"></a>
</div>
<div class="spamtrap" style="position:absolute; left:100px; top:1980px; color:#000000">
Spammers send mail to spbox2@eum-gilde.de - DO NOT SEND MAILS THERE
</div>
</body>
</html>
