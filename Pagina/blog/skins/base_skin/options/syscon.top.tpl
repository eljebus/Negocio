<script type="text/javascript">

    function ChangeOption(selectedOption)
    {
        document.getElementById('general').style.display = "none";
        document.getElementById('news').style.display = "none";
        document.getElementById('comments').style.display = "none";
        document.getElementById('notifications').style.display = "none";
        document.getElementById('social').style.display = "none";
        document.getElementById('button1').style.backgroundColor = "";
        document.getElementById('button2').style.backgroundColor = "";
        document.getElementById('button3').style.backgroundColor = "";
        document.getElementById('button4').style.backgroundColor = "";
        document.getElementById('button5').style.backgroundColor = "";
        document.getElementById('currentid').value = selectedOption;

        var SelectedButton = 'button1';
        if(selectedOption == 'general')         { document.getElementById('general').style.display = "";        SelectedButton = 'button1'; }
        if(selectedOption == 'news')            { document.getElementById('news').style.display = "";           SelectedButton = 'button2'; }
        if(selectedOption == 'comments')        { document.getElementById('comments').style.display = "";       SelectedButton = 'button3'; }
        if(selectedOption == 'notifications')   { document.getElementById('notifications').style.display = "";  SelectedButton = 'button4'; }
        if(selectedOption == 'social')          { document.getElementById('social').style.display = "";         SelectedButton = 'button5'; }

        document.getElementById(SelectedButton).style.backgroundColor = "#EBE8E2";
    }

</script>

<form action="{$PHP_SELF}" method=post>
<table border=0 cellpadding=0 cellspacing=0 width=100%>
<input type="hidden" name="csrf_code" value="{$csrf_code}"/>
<tr style="position:relative" valign=top>
    <td style="padding-bottom:30px;" colspan="3"  >
        <table style="text-align:center;  padding:0; margin:0;" width="100%" cellpadding="0" cellspacing="0">
            <tr style="border:1px solid black; vertical-align:middle;" >
                <td id="button1" style="background-color:#EBE8E2; border:1px solid black; border-radius: .7em .7em .0em .0em" width="20%"><a style="display:block; font-size:150%; font-weight:bold; height:100%; padding-top:10px;" href="javascript:ChangeOption('general');">General</a>
                <td id="button2" style="border:1px solid black; border-radius: .7em .7em .0em .0em" width="20%"><a style="display:block; font-size:150%; font-weight:bold; height:100%; padding-top:10px;" href="javascript:ChangeOption('news');">News</a>
                <td id="button3" style="border:1px solid black; border-radius: .7em .7em .0em .0em" width="20%"><a style="display:block; font-size:150%; font-weight:bold; height:100%; padding-top:10px;" href="javascript:ChangeOption('comments');">Comments</a>
                <td id="button4" style="border:1px solid black; border-radius: .7em .7em .0em .0em" width="20%"><a style="display:block; font-size:150%; font-weight:bold; height:100%; padding-top:10px;" href="javascript:ChangeOption('notifications');">Notifications</a>
                <td id="button5" style="border:1px solid black; border-radius: .7em .7em .0em .0em" width="20%"><a style="display:block; font-size:150%; font-weight:bold; height:100%; padding-top:10px;" href="javascript:ChangeOption('social');">Social</a>
                {$add_fields}
            </tr>
        </table>
    </td>
</tr>