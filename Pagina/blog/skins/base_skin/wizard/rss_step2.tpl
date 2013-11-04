<form method=POST action="index.php">
    <input type=hidden name=mod value=wizards>
    <input type=hidden name=action value=dosaverss>
    <table border=0 width=100% cellspacing="0" cellpadding="3">
        <tr>
        <td height="21" width=100% bgcolor=#F7F6F4>&nbsp;
            <b>URL of the page where you include your news</b>
            <br>&nbsp;
            <i>example: http://mysite.com/news.php</i>
            <br>&nbsp;
            <i>or: {$config_http_script_dir}/example2.php</i>
        </td>
        <td height="21" bgcolor=#F7F6F4
            colspan=2>
            <input name="rss_news_include_url" value="{$rss_news_include_url}" type=text
                   size=30>
            <tr>
                <td height="21">
                    <br>&nbsp;Title of the RSS feed
                <td height="21" colspan=2>
                    <br>
                    <input name="rss_title" value="{$rss_title}" size=30>
            </tr>
            <tr>
                <td height="21" bgcolor=#F7F6F4>
                    <br>&nbsp;Character Encoding (default:
                    <i>UTF-8</i>)
                <td height="21" colspan=2 bgcolor=#F7F6F4>
                    <br>
                    <input name="rss_encoding" value="{$rss_encoding}" size=20>
            </tr>
            <tr>
                <td height="21">
                    <br>&nbsp;Language (default:
                    <i>en-us</i>)
                <td height="21">
                    <br>
                    <input name="rss_language" value="{$rss_language}" size=5>
            </tr>
            <tr>
                <td height="1" colspan="2" colspan=3>
                    <br />
                    <br>
                    <input type=submit style="font-weight:bold; font-size:110%;" value="Save Configurations and Proceed >>"
                           accesskey="s">&nbsp;
                    <input style="font-size:90%;" onClick="document.location='{$PHP_SELF}?mod=wizards&action=customizerss';"
                           type=button value='Skip to Customization >>'>
            </tr>
        </td>
        </tr>
    </table>
</form>
