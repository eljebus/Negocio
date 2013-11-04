<form method=get action="{$PHP_SELF}">

    <input type=hidden name=action value=templates>
    <input type=hidden name=mod value=options>
    <table border=0 cellpadding=0 cellspacing=0>
        <tr>
            <td width=373 height="75">
                <b>Manage Templates</b>

                <table border=0 cellpadding=0 cellspacing=0 width=347 class="panel">
                        <tr>
                            <td width=126 height="23">Editing Template</td>
                            <td width=225 height="23">: <b>{$do_template}</b></td>
                        </tr>
                        <tr>
                            <td width=126 height="27">Switch to Template</td>
                            <td width=225 height="27">: <select name=do_template>{$SELECT_template}</select> <input type=submit value=Go></td>
                        </tr>
                        <tr>
                            <td width=351 height="25" colspan="2"> <a href="{$PHP_SELF}?mod=options&subaction=new&action=templates">[create new template]</a> {$show_delete_link} </td>
                        </tr>
                </table>
            </td>
            <td width=268 height="75" align="center">
                <!-- HELP -->
                <table cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="40" align=middle><img border="0" src="skins/images/help_small.gif" /></td>
                        <td>
                            <div>1 <a onclick="Help('templates'); return false;" href="#">Understanding Templates</a></div>
                            <div>2 <a onclick="Help('tplvars'); return false;" href="#">Template variables</a></div>
                        </td>
                    </tr>
                </table>
                <!-- END HELP -->
            </td>
        </tr>
    </table>
</form>

<br>
<div><b style="font-size: 17px;">Edit Template Parts</b> {$save}</div>
<br>

<form method=post action="{$PHP_SELF}">

    <input type=hidden name=mod value=options>
    <input type=hidden name=action value=dosavetemplates>
    <input type=hidden name=do_template value="{$do_template}">

    {foreach from=Template_Form}
        <div><a style="font-size: 16px;" href="#" onclick="DoDiv('{$Template_Form.name}'); return false;">{$Template_Form.title}</a></div>
        <div id="{$Template_Form.name}" style="margin: 0 0 10px 0;display: none;"><textarea style="border-radius: 4px; font-size: 14px; border: 1px solid #aaaaaa; width: 740px; height: 250px;" rows="9" cols="98" name="edit_{$Template_Form.name}">{$Template_Form.part}</textarea></div>
    {/foreach}

    <br/>
    <div><input type=submit value="   Save Changes   " accesskey="s"></div>
</form>
