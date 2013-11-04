{$error_messages}
{if $preview_hmtl}<div style="margin: 0 0 0 75px;">{$preview_hmtl}</div>{/if}

<form method=post name=addnews action="{$PHP_SELF}">

    <input type=hidden name="mod" value="addnews">
    <input type=hidden name="action" value="addnews">
    <input type=hidden name="subaction" value="doaddnews">
    <input type=hidden name="csrf_code" value="{$CSRF}" />

    <table border=0 cellpadding=0 cellspacing=0 width="750" >

    <tr>
        <td width="75" align="right">Title&nbsp;</td>
        <td colspan="2"><input type="text" style="width: 565px;" value="{$title}" name="title" tabindex=1></td>
    </tr>

    {$Hook_AdditionalFieldsTop}

    {if $xfields}
        <tr>
            <td align="right" valign="top">#&nbsp;</td>
            <td>
                <a href="#" onclick="DoDiv('add_flds_collapse'); return false;">More fields...</a>
                <div id="add_flds_collapse" style="display: none;">
                    <p><table class="panel">
                    {foreach from=xfields}
                        <tr>
                            <td>{$xfields.1}</td>
                            <td><input tabindex=2 type=text size="42" value="{$xfields.3}" name="{$xfields.0}" >&nbsp;&nbsp;&nbsp;<span style="font-size:7pt">{$xfields.2}</span></td>
                        </tr>
                    {/foreach}
                </table></p>
                </div>
            </td>
        </tr>
    {/if}

    {if $UseAvatar}
    <tr>
        <td width="75" align="right">Avatar URL&nbsp;</td>
        <td width="575" colspan="2"><input tabindex=2 type=text size="42" value="{$member_db8}" name="manual_avatar" >
        width: <input tabindex=2 type="text" name="_avatar_width" size="3" value="{$_avatar_width}">
        height: <input tabindex=2 type="text" size="3" name="_avatar_height" value="{$_avatar_height}">&nbsp;&nbsp;&nbsp;<span style="font-size:7pt">(optional)</span></td>
    </tr>
    {/if}

    <tr id='singlecat'>
        <td width="75" align="right">Category&nbsp;</td>
        <td colspan="2">
        {if $cat_lines}
            <select id='selecsinglecat' name=category tabindex=3> <option value=""> --- </option> {$cat_html} </select>
            <a href="javascript:ShowOrHide('multicat','singlecat');" onClick="javascript:document.getElementById('selecsinglecat').name='';">(multiple categories)</a>
        {/if}
        {if !$cat_lines}<span style="color: gray;">{{No category}}</span>{/if}
        </td>
    </tr>

    <tr style="display:none;" id='multicat'>
        <td width="75" align="right">Category&nbsp;</td>
        <td>
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="panel"> {$multi_cat_html} </table>
        </td>
        <td>&nbsp;</td>
    </tr>

    <!-- Short story -->
    <tr>
        <td width="75" valign="top" align="right"><br/>Short Story&nbsp;</td>
        <td>
            <textarea style="width: 565px" rows="12" cols="74" id="short_story" name="short_story" tabindex=4>{$short_story}</textarea>
        </td>
        <td width="108" valign="top" style='background: url(skins/images/baloon.gif) no-repeat top left' align="center">
            <p><a href=# onclick="window.open('{$PHP_SELF}?&mod=images&action=quick&area={$short_story_id}', '_Addimage', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=360');return false;" target="_Addimage"><br />[insert image]</a></p>
            <p>{$insertsmiles}</p>
            {$Hook_AdditionalFieldsShort}
        </td>
    </tr>

    <!-- Full story -->
    <tr id='full-story' style="display:none;">

        <td width="75" valign="top" align="right"><br />Full Story&nbsp;<br /><span style="font-size:7pt">(optional)</span>&nbsp;</td>

        <td> <textarea rows="12" cols="74" id="full_story" name="full_story" tabindex=5 style="width:565px;">{$full_story}</textarea> </td>

        <td width="108" valign="top" style='background: url(skins/images/baloon.gif) no-repeat top left' align="center">
            <p><a href=# onclick="window.open('{$PHP_SELF}?&mod=images&action=quick&area={$full_story_id}', '_Addimage', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=360');return false;" target="_Addimage"><br />[insert image]</a></p>
            <p>{$insertsmiles_full}</p>
            {$Hook_AdditionalFieldsFull}
        </td>
    </tr>

    {$Hook_AdditionalFieldsBottom}

    <tr>
        <td>&nbsp;</td>
        <td>
             <table border=0 cellspacing=0 cellpadding=0 width=100%>
             <tr>
               <td width=50%>
                   <input type=submit style='font-weight:bold' title="Post the New Article" value="     Add News     " accesskey="s">
                   <button title="Preview the New Article" name="preview" value="preview" accesskey="p">Preview</button>
               </td>
               <td width=50% align=right> <input style='width:110px;'type=button onClick="ShowOrHide('full-story',''); setTimeout('increaseTextareaBug()',310);" value="Toggle Full-Story"> <input style='width:90px;' type=button onClick="ShowOrHide('options','');" value="Article Options"> </td>
             </tr>
            </table>
        </td>
        <td>&nbsp;</td>
    </tr>

    <tr id='options' style="display:none;">
        <td align="center" valign="top" style="padding: 18px 0 0 0">Options</td>
        <td colspan="2" style="padding: 15px 0 0 0">

            <label for='html'>
            <input id='html' style="border:0; background-color:transparent" type=checkbox value="yes" name="if_use_html" {if $Using_HTML}checked{/if}> Use HTML in this article</label>
            <br/><br/>

            <label for='active'><input checked id='active' style="border:0; background-color: transparent" type=radio value="active" name="postpone_draft">
            <b>Normal</b>, add article as active</label>
            <br />

            <label for='draft'><input id='draft' style="border:0; background-color: transparent" type=radio value="draft" name="postpone_draft">
            <b>Draft</b>, add article as unapproved</label>
            <br />

            <label for='postponed'><input id='postponed' style="border:0; background-color: transparent" type=radio value="postpone" name="postpone_draft">
                <b>Postponed</b>, add article as unapproved
                <select name="from_date_day">{$dated}</select>
                <select name="from_date_month">{$datem}</select>
                <select name="from_date_year">{$datey}</select>
                @ <input value='{$dateh}' style="text-align: center;" name="from_date_hour" size=3 type=text title='24 Hour format [hh]'  /> :
                  <input value="{$datei}" style="text-align: center;" name="from_date_minutes" size=3 type=text title='Minutes [mm]' />

            </label>

        </td>

    </tr>
    </table>
</form>