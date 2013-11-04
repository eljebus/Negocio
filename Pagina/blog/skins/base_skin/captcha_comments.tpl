<table cellspacing="0" cellpadding="0" border="0">
<tr>
    {if $gduse}
        <td width="64"> <div>Captcha</div> </td>
        <td>
            <div><iframe src="captcha.php" style="border: 0" scrolling="no" width="200" height="70"></iframe></div>
            <div> <input type="text" name="captcha" value="" /></div>
        </td>
    {/if}
    {if !$gduse}
        <td width="64">
            <div>Captcha</div>
            <div><a href='#' onclick="document.getElementById('comments_captcha').src='{$config_http_script_dir}/captcha.php?r=' + Math.random(); return(false);" style="border-bottom:  1px dashed blue;">Refresh</a></div>
        </td>
        <td>
            <div> <img id="comments_captcha" src="{$config_http_script_dir}/captcha.php" alt="" /> </div>
            <div> <input type="text" name="captcha" value="" /></div>
        </td>
    {/if}
</tr>
</table>