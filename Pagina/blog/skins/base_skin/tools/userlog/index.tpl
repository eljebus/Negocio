{if $message}<p style="color: red;"><b>{$message}</b></p>{/if}
<form action="{$PHP_SELF}" method="get">

    <input type="hidden" name="mod" value="tools">
    <input type="hidden" name="action" value="userlog">

    <div style="background: #F7F6F4; width: 400px; display: block; border-radius: 4px; padding: 8px; margin: 0 0 16px 0;">

        <table width="400" cellpadding="0" cellspacing="0">
            <tr align="center">
                <td align="left">&nbsp;</td>
                <td width="64">Year</td>
                <td width="48">Month</td>
                <td width="48">Day</td>
                <td width="48">Hour</td>
            </tr>
            <tr align="center">
                <td align="right">Start period</td>
                <td><input style="width: 48px; text-align: center;" name="year_s" value="{$year_s}"></td>
                <td><input style="width: 48px; text-align: center;" name="month_s" value="{$month_s}"></td>
                <td><input style="width: 32px; text-align: center;" name="day_s" value="{$day_s}"></td>
                <td><input style="width: 32px; text-align: center;" name="hour_s" value="{$hour_s}"></td>
            </tr>
            <tr align="center">
                <td align="right">End period</td>
                <td><input style="width: 48px; text-align: center;" name="year_e" value="{$year_e}"></td>
                <td><input style="width: 48px; text-align: center;" name="month_e" value="{$month_e}"></td>
                <td><input style="width: 32px; text-align: center;" name="day_e" value="{$day_e}"></td>
                <td><input style="width: 32px; text-align: center;" name="hour_e" value="{$hour_e}"></td>
            </tr>
            <tr>
                <td align="right">Per page</td>
                <td colspan="4">&nbsp;&nbsp;<input style="width: 32px; text-align: center;" name="per" value="{$per}"></td>
            </tr>
        </table>
        <div style="text-align: right;"><input type="submit" value=" search " ></div>
    </div>
</form>

<p>Total found <b>{$count}</b> items</p>
<table width="100%" cellpadding="4" cellspacing="0">
    <tr><td><b>User</b></td> <td><b>Action</b></td> <td width="150"><b>Date / Time</b></td>  <td><b>IP</b></td> </tr>
    {if $count} {foreach from=logs}<tr bgcolor="{$logs.bg}"><td>{$logs.user}</td> <td>{$logs.action}</td> <td>{$logs.time}</td> <td>{$logs.ip}</td></tr>{/foreach} {/if}
    {if !$count} <tr><td colspan="4">No entries for this date range</td></tr> {/if}
</table>

<br/><b>Pages:</b> {foreach from=pages}{$pages.LB}<a href="{$pages.link}">{$pages.id} </a>{$pages.RB}{/foreach}<br/>


