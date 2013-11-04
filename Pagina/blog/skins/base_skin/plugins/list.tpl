<div style="font: 15px/2em; color: red;">{$error}</div>
<br/>
<table cellpadding="3" cellspacing="0" width="100%">

<tr bgcolor="#c0e0f0">
    <td><b>{{Plugin name}}</b></td>
    <td><b>{{Description}}</b></td>
    <td><b>{{Actions}}</b></td>
</tr>

{foreach from=list}

    <tr>
        <td> {$list.name} </td>
        <td> {$list.desc} </td>
        <td> <a onclick="return confirm('{{Uninstall?}}');" href="{$PHP_SELF}?mod=tools&amp;action=plugins&amp;do=uninstall&amp;name={$list.name}&amp;csrf_code={$CSRF}">Uninstall</a> </td>
    </tr>

{/foreach}
</table>

<br/>
<h3>Add new plugin file</h3>
<form action="{$PHP_SELF}" method="POST" enctype="multipart/form-data">

    <input type="hidden" name="mod" value="tools" />
    <input type="hidden" name="action" value="plugins" />
    <input type="hidden" name="do" value="upload" />
    <input type="hidden" name="csrf_code" value="{$CSRF}" />

    <table>
        <tr><td>{{Upload by url}}</td> <td><input style="width: 480px;" type="text" name="urlpath" value="{$urlpath}" /> * Must be extension .plg</td></tr>
        <tr><td>{{Upload by file}}</td> <td><input style="border: 0" type="file" name="file" value="{{Upload new plugin}}" /></td></tr>
        <tr><td>&nbsp;</td> <td><input type="submit" value="{{Submit plugin}}" /></td> </tr>
    </table>
</form>