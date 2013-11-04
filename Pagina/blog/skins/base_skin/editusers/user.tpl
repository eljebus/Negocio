<html><head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
    <title>Edit Users</title>
    <style type="text/css">
    <!--
        select, option, textarea, input
        {
            border-right: #808080 1px solid;
            border-top: #808080 1px solid;
            border-bottom: #808080 1px solid;
            border-left: #808080 1px solid;
            color: #000000;
            font-size: 11px;
            font-family: Verdana, Arial, serif;
            background-color: #ffffff;
        }
            TD {text-decoration: none; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt;}
            BODY {text-decoration: none; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 3pt;}
            .header { font-size : 16px; font-weight: bold; color: #808080; font-family: verdana; text-decoration: none; }
    -->
    </style>
    </head>
    <body>
        <form action="{$PHP_SELF}" method=post>
            <input type="hidden" name="csrf_code" value="{$CSRF}" />
            <table width="828" cellspacing="0" cellpadding="0">
                <tr>
                <td width="826" height="21" colspan="2"><div class=header>{$user_arr[2]} <span>({$user_arr[4]})</span></div>
                </tr>

                <tr>
                    <td width="126" height="20" bgcolor="#f7f6f4">written news</td>
                    <td height="20" bgcolor="#f7f6f4" width="698">{$user_arr[6]}</td>
                </tr>

                <tr>
                    <td width="126" height="20" bgcolor="#f7f6f4">last login date</td>
                    <td height="20" bgcolor="#f7f6f4" width="698">{$last_login}</td>
                </tr>

                <tr>
                    <td width="126" height="20">registration date</td>
                    <td height="20" width="698">{$user_date} </td>
                </tr>

                <tr>
                    <td width="126" height="20" bgcolor="#f7f6f4">Email</td>
                    <td height="20" bgcolor="#f7f6f4" width="698"><input size="20" name="editemail" value="{$user_arr[5]}" ></td>
                </tr>

                <tr>
                    <td width="126" height="20">New Password</td>
                    <td height="20" width="698"><input size="20" name="editpassword" ></td>
                </tr>

                <tr>
                    <td width="126" height="20" bgcolor="#f7f6f4">Access Level</td>
                    <td height="20" bgcolor="#f7f6f4" width="698">
                        <select name=editlevel>
                        {foreach from=edit_level}
                            <option value={$edit_level.id} {$edit_level.s}>{$edit_level.id} ({$edit_level.type})</option>
                        {/foreach}
                        </select>
                </tr>
                <tr>
                    <td width="826" height="7" colspan="2">
                        <br />
                        <input type=submit value="Save Changes">
                        <input type=button value="Cancel" onClick="window.close();">
                        <input type=hidden name=id value={$id}>
                        <input type=hidden name=mod value=editusers>
                        <input type=hidden name=action value=doedituser>
                    </td>
                </tr>
            </table>
        </form>
    </body>
</html>