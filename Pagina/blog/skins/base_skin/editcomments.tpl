<html>
<head>
    <title>Edit user</title>
    <style type="text/css">
    <!--
       SELECT, option, textarea, input
       {
           border: #808080 1px dotted;
           color: #000000;
           font-size: 11px;
           font-family: Verdana, Tahoma, serif;
           background: #ffffff;
       }
       TD {text-decoration: none; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt;}
       BODY {text-decoration: none; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 3pt;}
       .header { font-size : 16px; font-weight: bold; color: #808080; font-family: verdana, tahoma, serif; text-decoration: none; }
    -->
    </style>
    </head>
    <body bgcolor="#FFFFFF">
        <form method=post action="{$PHP_SELF}">
            <input type=hidden name="csrf_code" value="{$CSRF}" />
            <input type=hidden name=mod value=editcomments>
            <input type=hidden name=newsid value={$newsid}>
            <input type=hidden name=comid value={$comid}>
            <input type=hidden name=source value={$source}>
            <input type=hidden name=action value=doeditcomment>
            <div class="header">Edit Comment</div>
            <table width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td height=20 valign=middle width="102" bgcolor="#F9F8F7"> Poster </td>
                    <td height=20 valign=middle width="1002" bgcolor="#F9F8F7"> <input type=text name=poster value="{$single_arr[1]}"> </td>
                </tr>

                <tr>
                    <td height=20 valign=middle valign="top" width="102"> Email</td>
                    <td height=20 valign=middle width="1002"> <input type=text name=mail value="{$single_arr[2]}"> </td>
                </tr>

                <tr>
                    <td height=20 valign=middle valign="top" width="102" bgcolor="#F9F8F7"> IP </td>
                    <td height=20 valign=middle width="1002" bgcolor="#F9F8F7">
                        <a href="http://www.ripe.net/perl/whois?searchtext={$single_arr[3]}" target=_blank title="Get more information about this ip">{$single_arr[3]}</a>  &nbsp;
                        <a href="{$PHP_SELF}?mod=ipban&action=quickadd&add_ip={$single_arr[3]}">[ban this ip]</a>
                    </td>
                </tr>

                <tr>
                    <td height=20 valign=middle valign="top" width="102">Date</td>
                    <td height=20 valign=middle width="1002"> {$comdate} </td>
                </tr>
                <tr>
                    <td height=20 valign=middle  width="102" bgcolor="#F9F8F7"> Comments&nbsp; </td>
                    <td  height=20 valign=middle width="1002" bgcolor="#F9F8F7">  <textarea rows="8" name="comment" cols="45">{$single_arr[4]}</textarea> </td>
                </tr>
                <tr>
                    <td  valign="top" colspan="2">
                        <input type=submit value="Save Changes" accesskey="s">&nbsp; <input type=button value="Cancel" onclick="window.close();" accesskey="c">
                    </td>
                </tr>
            </table>
        </form>
    </body>
</html>