<form method=post action={$PHP_SELF}>
    <input type=hidden name=mod value=options>
    <input type=hidden name=action value=templates>
    <input type=hidden name=subaction value=donew>
    <table border=0 cellpadding=0 cellspacing=0 width=100%>
        <tr>
            <td>Create new template based on:
                <select name=base_template> {foreach from=templates_list} <option value="{$templates_list.}">{$templates_list.}</option> {/foreach} </select>
                with name
                <input type=text name=template_name>&nbsp;<input type=submit value="{{Create Template}}">
            </td>
        </tr>
    </table>
</form>