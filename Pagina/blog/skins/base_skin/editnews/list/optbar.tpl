<table class=panel border=0 cellpadding=0 cellspacing=0 width=99% >
<tr>
    <td title="Show More Options">
        Showing <b>{$entries_showed}</b> articles from total <b>{$all_count_news}</b>; {$cat_msg} {$source_msg}
    </td>
    <td align="right"><a href="javascript:ShowOrHide('options','');">show options&nbsp;</a> </td>
</tr>
<tr>
    <td colspan="2">
        <div id='options' style='display:none;z-index:1;'>
            <form action="{$PHP_SELF}?mod=editnews&action=list" method="POST" name="options_bar">
                <input type=hidden name="csrf_code" value="{$CSRF}" />
                <table width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="border-top: 1px dashed silver" width="100%" align="right" colspan="3">
                        <p align="center">&nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td width="286" align="right">Source&nbsp;</td>
                        <td width="180">
                            <select name="source" size="1">
                                <option value="">- Active News -</option>
                                <option {$postponed_selected} value="postponed">- Postponed News -</option>
                                <option {$unapproved_selected} value="unapproved">- Unapproved News -</option>
                                {$opt_source}
                            </select>
                        </td>
                        <td width="182">&nbsp;</td>
                    </tr>
                    <tr>
                        <td width="284" align="right" > Category&nbsp; </td>
                        <td width="178" >
                            <select name="category"><option selected value="">- All -</option>
                                {$opt_catlist}
                            </select>
                        </td>
                        <td height="1" width="180">&nbsp;</td>
                    </tr>
                    {if $opt_author}
                    <tr>
                        <td width="284" align="right">Author&nbsp;</td>
                        <td width="178" >
                            <select name=author size="1">
                                <option value="">- Any -</option>
                                {$opt_author}
                            </select>
                        </td>
                        <td height="1" width="180" >&nbsp;</td>
                    </tr>
                    {/if}
                    <tr>
                        <td width="284" align="right" > News per page&nbsp; </td>
                        <td width="178"> <input style="text-align: center" name="news_per_page" value="{$news_per_page}" type=text size=3> <input type=submit value="{{Show}}"> </td>
                        <td width="180" >&nbsp;</td>
                    </tr>
                </table>
            </form>
        </div>
    </td>
</tr>
</table>