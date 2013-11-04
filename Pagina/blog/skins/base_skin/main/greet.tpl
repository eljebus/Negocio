<table border=0 cellpadding=0 cellspacing=0 width=754>
    <tr>
        <td style="font-size: 14px;">
        {if !$enter_without_login}
            <script type="text/javascript">
                datetoday = new Date();
                timenow=datetoday.getTime();
                datetoday.setTime(timenow);
                thehour = datetoday.getHours();
                if (thehour < 9 )      display = "Morning";
                else if (thehour < 12) display = "Day";
                else if (thehour < 17) display = "Afternoon";
                else if (thehour < 20) display = "Evening";
                else display = "Night";

                var greeting = ("Good " + display);
                document.write(greeting);
            </script> {member}{greet}
            <br /><br />
        {/if}
        </td>
    </tr>
    {warn}
</table>
