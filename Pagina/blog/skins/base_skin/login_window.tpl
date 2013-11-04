<form  name="login" id="login_form" action='{$PHP_SELF}' method="post">

    <input type="hidden" id="csrf_code" name="csrf_code" value="{$CSRF}">

    <input type="hidden" name="action" value="dologin">
    <table width="100%" border=0 cellpadding=1 cellspacing=0>
        <tr>
            <td width='80'>Username: </td>
            <td width='160'><input tabindex=1 type="text" name="username" id="login_username" value="{$lastusername}" style="width: 150px;"></td>
            <td>&nbsp;{ALLOW_REG}<a href="register.php">(register)</a>{/ALLOW_REG}</td>
        </tr>
        <tr>
            <td>Password: </td>
            <td><input tabindex="1" type="password" name="password" id="login_password" style='width: 150px'></td>
            <td>&nbsp;<a href='register.php?action=lostpass'>(lost password)</a> </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td style='text-align:left'>
                <input tabindex=1 accesskey='s' type=submit style="width:150px; background-color: #F3F3F3;" value='      Login...      '><br/>
            </td>
            <td style='text-align:left'><label for=rememberme title='Remember me for 30 days, Do not use on Public-Terminals!'>
                <input id=rememberme type=checkbox value=yes style="border:0px;" name=rememberme>Remember Me</label>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td align="center" colspan=2 style='text-align:left;'>{$result}</td>
        </tr>

</table>
</form>
