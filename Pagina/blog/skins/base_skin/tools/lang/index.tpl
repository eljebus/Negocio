<form action="{$PHP_SELF}" method="post" >
    <input type="hidden" name="csrf_code" value="{$CSRF}" />
    <input type="hidden" name="mod" value="tools" />
    <input type="hidden" name="action" value="language" />
    <table width="650" cellspacing="0" cellpadding="4">

        <tr bgcolor="#FFFFE0"><td width="128">Lang var</td> <td>Lang value</td></tr>

        {foreach from=lang}
            <tr>
                <td style="align: right;">{$lang.2}</td>
                <td><input type="text" style="width: 500px;" name="language[{$lang.0}]" value="{$lang.1}"></td>
            </tr>
        {/foreach}

        <tr><td>&nbsp;</td> <td><input type="submit" value="Submit" /></td></tr>

    </table>
</form>