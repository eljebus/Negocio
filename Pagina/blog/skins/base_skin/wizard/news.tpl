<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <form method="post" action="{$PHP_SELF}">
        <tr>
            <td>
                <table cellspacing="0" cellpadding="3" width="100%" style="border: 0; border-collapse: collapse;">
                    <tr>
                        <td width="639" colspan="2">Welcome to the News Integration Wizard. This tool will help you to integrate
                            the news that you have published using CuteNews, into your existing Webpage.
                            <br>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#F7F6F4" style="padding:3px; border-bottom:1px solid gray;" colspan="2">
                            <b>Quick Customization...</b>
                        </td>
                    </tr>
                    <tr>
                        <td width="356">
                            <b><br>Number of Active News to Display:</b>
                        </td>
                        <td width="277" rowspan="2" valign="top" align="center">
                            <br>
                            <input style="text-align: center" name="w_number" size="11">
                        </td>
                    </tr>
                    <tr>
                        <td width="356" style="padding-left:10px;  ">
                            <p align="justify">
                                <i>if the active news are less then the specified number to show, the rest
                                    of the news will be fetched from the archives (if any)</i>
                        </td>
                    </tr>
                    <tr>
                        <td width="356">
                            <b><br>Template to Use When Displaying News:</b>
                        </td>
                        <td width="277" rowspan="2" valign="top" align="center">
                            <br>{$templates_html}</td>
                    </tr>
                    <tr>
                        <td width="356" style="padding-left:10px;  ">
                            <p align="justify">
                                <i>using different templates you can customize the look of your news, comments etc.</i>
                        </td>
                    </tr>
                    <tr>
                        <td width="356">
                            <b>
                                <br>Categories to Show News From:</b>
                        </td>
                        <td width="277" rowspan="2" valign="top" align="center">
                            <br>{$cat_html}</td>
                    </tr>
                    <tr>
                        <td width="356" style="padding-left:10px;  ">
                            <p align="justify">
                                <i>you can specify only from which categories news will be displayed, hold
                                    CTRL to select multiple categories (if any)
                                    <br>&nbsp;</i>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#F7F6F4" style="padding:3px; border-bottom:1px solid gray; "
                            width="639" colspan="2">
                            <b>
                                <font size="2">Advanced Settings...</font>
                            </b>
                        </td>
                    </tr>
                    <tr>
                        <td width="356">
                            <b>
                                <br>Start 'Displaying' From...</b>
                        </td>
                        <td width="277" rowspan="2" align="center" valign="top">
                            <br>
                            <input name="w_start_from" size="11" style="text-align: center">
                        </td>
                    </tr>
                    <tr>
                        <td width="356" style="padding-left:10px;  ">
                            <i>if Set, the displaying of the news will be started from the specified
                                number (eg. if set to 2 - the first 2 news will be skipped and the rest
                                shown)</i>
                        </td>
                    </tr>
                    <tr>
                        <td width="356">
                            <b>
                                <br>Reverse News Order:</b>
                        </td>
                        <td width="277" rowspan="2" align="center" valign="top">
                            <br>&nbsp;
                            <input type=checkbox value="yes" name="w_reverse">
                        </td>
                    </tr>
                    <tr>
                        <td width="356" style="padding-left:10px;  ">
                            <i>if Yes, the order of which the news are shown will be reversed</i>
                        </td>
                    </tr>
                    <tr>
                        <td width="356">
                            <b>
                                <br>Show Only Active News:</b>
                        </td>
                        <td width="277" rowspan="2" align="center" valign="top">
                            <br>
                            <input type=checkbox value="yes" name="w_only_active">
                        </td>
                    </tr>
                    <tr>
                        <td width="356" style="padding-left:10px;  ">
                            <i>if Yes, even if the number of news you requested to be shown is bigger
                                than all active news, no news from the archives will be shown</i>
                        </td>
                    </tr>
                    <tr>
                        <td width="356">
                            <b>
                                <br>Static Include:</b>
                        </td>
                        <td width="277" rowspan="2" align="center" valign="top">
                            <br>
                            <input type=checkbox value="yes" name="w_static">
                        </td>
                    </tr>
                    <tr>
                        <td width="356" style="padding-left:10px;">
                            <i>if Yes, the news will be displayed but will not show the full story and
                                comment pages when requested. useful for
                                <a href=# onclick="javascript:Help('multiple_includes')">multiple includes</a>.</i>
                        </td>
                    </tr>
                    <tr>
                        <td width="639" colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td width="639" colspan="2" style="border-top:1px solid gray; padding-top:10px;">
                            <center>
                                <input type=submit style="font-weight:bold;" value="Proceed to Integration >>">
                            </center>&nbsp;</td>
                    </tr>
                </table>
                <input type=hidden name=mod value=wizards>
                <input type=hidden name=action value=news_step2>
            </td>
        </tr>
    </form>
</table>