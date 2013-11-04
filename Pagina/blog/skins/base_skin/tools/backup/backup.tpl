<script type="text/javascript">
    function confirmdelete(id)
    {
        var agree=confirm("Do you really want to permanently delete this backup ?");
        if (agree)
        document.location="index.php?mod=tools&action=dodeletebackup&backup="+id+"&csrf_code={$CSRF}";
    }

    function confirmrestore(id)
    {
        var agree=confirm("Do you really want to restore your news from this backup ?\nAll current news and archives will be overwritten.");
        if (agree)
        document.location="index.php?mod=tools&action=dorestorebackup&backup="+id+"&csrf_code={$CSRF}";
    }
</script>

<h3>Create Backup</h3>
<form method=post action="{$PHP_SELF}">
    <input type=hidden name="csrf_code" value="{$CSRF}" />
    <input type=hidden name=action value=dobackup>
    <input type=hidden name=mod value=tools>
    <table border=0 cellpadding=0 cellspacing=0 class="panel" width="390" >
    <tr> <td height="25" width="366">Name of the Backup: <input type=text name="backup">&nbsp; <input type=submit value=" Proceed "></td> </tr>
    </table>
</form>

<div><b>Available Backups</b></div>
<table width=100% cellspacing=0 cellpadding=4>
<tr bgcolor=#F7F6F4>
    <td width=2%>&nbsp;</td>
    <td width=40%>name</td>
    <td width=19% align="center">active news</td>
    <td width=19% align='center'>archives</td>
    <td width=20%>action</td>
</tr>
{$inclusion}
</table>
