{if $entries_showed}
    <script type="text/javascript">
    <!--
    function ckeck_uncheck_all()
    {
        var frm = document.editnews;
        for (var i=0;i<frm.elements.length;i++)
        {
            var elmnt = frm.elements[i];
            if (elmnt.type=='checkbox')
            {
                if(frm.master_box.checked == true) elmnt.checked=false;
                else elmnt.checked=true;
            }
        }
        if (frm.master_box.checked == true) frm.master_box.checked = false;
        else frm.master_box.checked = true;
    }
    -->
    </script>
    <br />
    <form method="post" name="editnews">
        <input type=hidden name="csrf_code" value="{$CSRF}" />
        <table border=0 cellpadding=2 cellspacing=0 width=100%>
            <tr>
                <td align="center" width="32">Ord</td>
                <td>Title{$title_ord}</td>
                <td width="85" align="center">Comments</td>
                <td width="70" align="center">Category</td>
                <td width="85" align="center">Date{$date_ord}</td>
                <td width="100">Author</td>
                <td width="32" align="center"><input style="border: 0; background: transparent;" type=checkbox name=master_box title="Check All" onclick="javascript:ckeck_uncheck_all();"> </td>
            </tr>
            {$entries}
        </table>

        <!-- Pagination and actions -->
        <table width="100%">
            <tr>
                <td>{$npp_nav}</td>
                <td align="right">
                    With selected:
                    <select name="action">
                        <option value="">-- Choose Action --</option>
                        <option title="delete all selected news" value="mass_delete">Delete</option>
                        {$do_action}
                    </select>
                    <input type=hidden name=source value="{$source}">
                    <input type=hidden name=mod value="massactions">
                    <input type=submit value=Go>
                </td>
            </tr>
        </table>
    </form>
{/if}

{if !$entries_showed}
    <table border=0 cellpadding=0 cellspacing=0 width=100% >
        <tr>
            <td colspan=6>
                <p style="border: solid black 1px; margin: 22px; padding: 4px;" align=center>
                    - No news were found matching your criteria -<br>
                    <a href="#" onclick="getElementById('options').style.display='';">[options]</a>
                </p>
            </td>
        </tr>
    </table>
{/if}