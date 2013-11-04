<form method=post action="{$PHP_SELF}">
    <input type=hidden name="csrf_code" value="{$CSRF}" />
    <input type=hidden name=action value=add>
    <input type=hidden name=mod value=ipban>
    <table border=0 cellpadding=0 cellspacing=0 width="100%">
        <tr>
            <td width=100% height="33">
                <p><b>Block IP or Nickname</b></p>
                <table border=0 cellpadding=0 cellspacing=0 width=100%  class="panel" cellpadding="7" >
                    <tr>
                        <td width=150 align="right" height="25">IP Address / Nick name:&nbsp;</td>
                        <td height="25"> <input type=text name="add_ip"> <input type=submit value="Block IP or nick / Refresh"> example: <i>129.32.31.44</i> or <i>129.32.*.*</i> </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</form>

<div><b>Blocked IP Addresses / Names</b></div>
<table cellspacing=0 cellpadding=3 width="100%">

    <tr  bgcolor=#F7F6F4>
        <td width=15></td>
        <td width=260><b>IP</b></td>
        <td width=200 align="center"><b>times been blocked</b></td>
        <td width=120 align="center"><b>expire</b></td>
        <td width=140 align="center"><b>unblock</td>
    </tr>

    {foreach from=iplist}
    <tr {$iplist.bg}>
        <td> &nbsp; </td>
        <td> <a href="http://www.ripe.net/perl/whois?searchtext={$iplist.ip}" target=_blank title="Get more information about this ip">{$iplist.ip}</a> </td>
        <td align="center"> {$iplist.times} </td>
        <td align="center"> {$iplist.expire} </td>
        <td align="center"> <a href="{$PHP_SELF}?mod=ipban&action=remove&remove_ip={$iplist.ip}">[unblock]</a></td>
    </tr>
    {/foreach}

</table>
