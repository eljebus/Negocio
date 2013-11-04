<p>Put {name} to the template file for active news / full story in order to see the additional field</p>

<form action="{$PHP_SELF}" method="POST">

    <input type="hidden" name="mod" value="tools">
    <input type="hidden" name="action" value="xfields">
    <input type="hidden" name="do" value="submit">
    <input type="hidden" name="csrf_code" value="{$CSRF}" />

    <table width="100%"" cellspacing="0" cellpadding="4">
        <tr bgcolor="#FFFFE0"><td width="48" align="center">Remove</td> <td width="48" align="center">Required</td> <td>Replaces with {name}</td> <td>Name for admin panel</td></tr>
        {foreach from=xfields}
            <tr>
                <td align="center"><input type="checkbox" name="remove[{$xfields.0}]" value="Y"></td>
                <td align="center"><input type="checkbox" {$xfields.2} name="optional[{$xfields.0}]" value="Y"></td>
                <td><input type="hidden" name="name[{$xfields.0}]" value="{$xfields.0}">{{$xfields.0}}</td>
                <td><input type="text" style="width: 350px;" name="vis[{$xfields.0}]" value="{$xfields.1}" maxlength="50"></td>
            </tr>
        {/foreach}
        <tr>
            <td align="right" colspan="2">Add a field</td>
            <td><input type="text" style="width: 150px;" name="add_name" value="" maxlength="32"></td>
            <td><input type="text" style="width: 350px;" name="add_vis" value=""> <input type="submit" value="Submit"></td>
        </tr>
    </table>

</form>
