<script language="javascript">
    function popupedit(id)
    {
        window.open('{$PHP_SELF}?mod=editusers&action=edituser&id='+id, 'User', 'toolbar=0,location=0,status=0,menubar=0,scrollbars=0,resizable=0,width=360,height=210');
    }
    function confirmdelete(id)
    {
        var agree = confirm("Are you sure you want to delete this user ?");
        if (agree) document.location = "{$PHP_SELF}?mod=editusers&action=dodeleteuser&id=" + id;
    }
    function ckeck_uncheck_all()
    {
        var frm = document.usersform;
        for (var i = 0; i < frm.elements.length;i++)
        {
            var elmnt = frm.elements[i];
            if (elmnt.type=='checkbox')
            {
                if (frm.master_box.checked == true) elmnt.checked=false;
                else elmnt.checked=true;
            }
        }
        if (frm.master_box.checked == true) frm.master_box.checked = false;
        else frm.master_box.checked = true;
    }
</script>
<form method=post action="{$PHP_SELF}" name="usersform">

    <input type="hidden" id="csrf_code" name="csrf_code" value="{$CSRF}" />

    <table border=0 cellpadding=3 cellspacing=0 width=100%>
        <tr>
            <td width=654 colspan="6">
                <!-- Start add edit users table + info + help -->
                <table border="0" width="657"  cellspacing="0" cellpadding="0">

                    <tr>
                        <td valign="bottom" width="311" valign="top" height="1"> <b>Add User</b> </td>
                        <td width="5" valign="top"  rowspan="3" height="81"> &nbsp; </td>
                        <td valign="bottom" width="330" height="1"><b>User Levels</b></td>
                    </tr>

                    <tr>
                        <td width="311" rowspan="2" valign="top" height="60" >

                            <!-- Add User Table -->
                            <table class="panel" cellspacing="0" cellpadding="0" width="100%">
                                <tr>
                                    <td><label for="regusername">Username</label></td>
                                    <td><input size=21 type=text id="regusername" name=regusername></td>
                                </tr>
                                <tr>
                                    <td><label for="regpassword">Password</label></td>
                                    <td><input size=21 type=text id="regpassword" name=regpassword></td>
                                </tr>
                                <tr>
                                    <td><label for="regnickname">Nickname</label></td>
                                    <td><input size=21 type=text id="regnickname" name=regnickname></td>
                                </tr>
                                <tr>
                                    <td><label for="regemail">Email</label></td>
                                    <td><input size=21 type=text id="regemail" name=regemail></td>
                                </tr>
                                <tr>
                                    <td><label for="reglevel">Access Level</label></td>
                                    <td><select id="reglevel" name=reglevel>
                                        <option value=4>4 (commenter)</option>
                                        <option selected value=3>3 (journalist)</option>
                                        <option value=2>2 (editor)</option>
                                        <option value=1>1 (administrator)</option>
                                    </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td height="35">
                                        <input type=submit value="Add User">
                                        <input type=hidden name=action value=adduser>
                                        <input type=hidden name=mod value=editusers>
                                    </td>
                                </tr>
                            </table>
                            <!-- End Add User Table -->

                        </td>
                        <td width="330" height="1" valign="top" >

                            <!-- User Levels Table -->
                            <table class="panel" cellspacing="3" cellpadding="0" width="100%">
                                <tr>
                                    <td valign="top">
                                        &nbsp;Administrator : have full access and privilegies<br/>
                                        &nbsp;Editor : can add news and edit others posts<br/>
                                        &nbsp;Journalist : can only add news (must be approved)<br/>
                                        &nbsp;Commenter : only post comments
                                    </td>
                                </tr>
                            </table>
                            <!-- End User Levels Table -->

                        </td>
                    </tr>
                    <tr>
                        <td width="330" valign="top" align=center height="70"><br>

                            <!-- HELP -->
                            <table height="25" cellspacing="2" cellpadding="0">
                                <tr>
                                    <td width="25" align=middle><img border="0" src="skins/images/help_small.gif" width="25" height="25"></td>
                                    <td >&nbsp;<a onClick="Help('users')" href="#">Understanding user levels</a>&nbsp;</td>
                                </tr>
                            </table>
                            <!-- END HELP -->

                        </td>
                    </tr>
                </table>
                <!-- END add edit users table + info + help -->

        </tr>
        <tr>
            <td width=650 colspan="6"> <img height=20 border=0 src="skins/images/blank.gif" width=1><br><b>Edit Users</b></td>
        </tr>

        <tr bgcolor="#F7F6F4">
            <td width=130>Username</td>
            <td width=197>Registration date</td>
            <td width=2> &nbsp; </td>
            <td width=83 align="center">Written news</td>
            <td width=132 align="center">Access Level</td>
            <td width=40 align="center">Edit</td>
            <td width=32 align="center"><input type="checkbox" name="master_box" onclick="ckeck_uncheck_all()" value="Y" /></td>
        </tr>

        {foreach from=userlist}
            <tr {$userlist.bg} title="{$userlist.title}'&#039;s {{last login was on}}: {$userlist.last_login}">
                <td> &nbsp;{$userlist.title}</td>
                <td>{$userlist.date}</td>
                <td>&nbsp;</td>
                <td align="center">&nbsp;&nbsp;{$userlist.count}</td>
                <td align="center"> &nbsp;{$userlist.user_level}</td>
                <td align="center"> <a onclick="popupedit('{$userlist.title}'); return(false)" href="#">[edit]</a> </td>
                <td align="center"><input type="checkbox" name="userdel[{$userlist.title}]" value="Y" /></td>
            </tr>
        {/foreach}
        <tr><td colspan="7" align="right"><button onclick="document.usersform.submit()">Delete</button></td> </tr>
    </table>
</form>