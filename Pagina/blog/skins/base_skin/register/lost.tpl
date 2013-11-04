<form method=post action="{$PHP_SELF}">
<input type=hidden name=action value=validate>
<input type=hidden name=mod value=lostpass>
<table border=0 cellpadding=0 cellspacing=0 width="654" height="59" >
    <tr>
        <td width="18" height="11">&nbsp;</td>
        <td width="71" height="11" align="left">Username</td>
        <td width="203" height="11" align="left"><input type=text name=user seize=20>&nbsp;</td>
        <td width="350" height="26" align="left" rowspan="2" valign="middle">If the username and email match in our users database,<br> and email with furher instructions will be sent to you.</td>
    </tr>
    <tr>
        <td width="18" valign="top" height="15">&nbsp;</td>
        <td width="71" height="15" align="left">Email</td>
        <td width="203" height="15" align="left"><input type=text name=email size="20">&nbsp;</td>
    </tr>
    <tr>
        <td width="18" valign="top" height="15">&nbsp;</td>
        <td width="628" height="15" align="left" colspan="3">&nbsp;</td>
    </tr>
    <tr>
        <td width="18" valign="top" height="15">&nbsp;</td>
        <td width="628" height="15" align="left" colspan="3"><input type=submit value="Send me the Confirmation"></td>
    </tr>
    <tr>
        <td width="18" height="27"></td>
        <td width="632" height="27" colspan="3"></td>
    </tr>
</table>
</form>