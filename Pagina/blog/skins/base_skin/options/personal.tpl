<form method=POST action="{$PHP_SELF}" name=personal>
    
    <input type=hidden name=mod value=options>
    <input type=hidden name=action value=dosavepersonal>
    <input type=hidden name="csrf_code" value="{$CSRF}" />
    
    <table border=0 width="100%" cellspacing="0" cellpadding="4">
        <tr>
            <td width="180" bgcolor=#F7F6F4>&nbsp;Username</td>
            <td bgcolor=#F7F6F4 >{$member_db[2]}
        </tr>

        <tr>
            <td>&nbsp;New Password</td>
            <td> <input type="password" name=editpassword> </td>
        </tr>
        <tr>
            <td>&nbsp;Confirm Password</td>
            <td> <input type="password" name=confirmpassword >&nbsp;&nbsp;&nbsp;Confirm new password</td>
        </tr>

        <tr>
            <td bgcolor=#F7F6F4>&nbsp;Nickname</td>
            <td bgcolor=#F7F6F4 > <input type=text name=editnickname value="{$member_db[4]}"> </td>
        </tr>

        <tr>
            <td>&nbsp;Email</td>
            <td> <input type=text name=editmail value="{$member_db[5]}">&nbsp;&nbsp;&nbsp;<input type=checkbox name=edithidemail {$ifchecked}>&nbsp;Hide my e-mail from visitors
        </tr>

        {NOTCOMMENTER}
        <tr>
            <td bgcolor=#F7F6F4>&nbsp;Default Avatar URL</td>
            <td bgcolor=#F7F6F4 > <input style="width: 250px;" type=text name=change_avatar value="{$member_db[8]}">&nbsp;&nbsp;&nbsp;&nbsp;will appear on 'Add/Edit News' page </td>
        </tr>
        {/NOTCOMMENTER}

        <tr>
            <td {$bg}>&nbsp;Access Level</td>
            <td {$bg} >{$access_level}</td>

        {NOTCOMMENTER}
        </tr>
        <tr>
            <td bgcolor=#F7F6F4>&nbsp;Written news</td>
            <td bgcolor=#F7F6F4 >{$member_db[6]}</td>
        </tr>
        {/NOTCOMMENTER}

        <tr>
            <td>&nbsp;Registration date </td>
            <td> {$registrationdate} </td>
        </tr>

        <tr>
            <td><br /><input type=submit value="Save Changes" accesskey="s">
        </tr>

    </table>
</form>