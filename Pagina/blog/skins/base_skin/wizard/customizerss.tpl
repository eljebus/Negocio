<script type="text/javascript">
    function generateCode()
    {
        sbox = document.getElementById('categories');
        var categoryString = '';
        var firstDone = 0;

        for (var i = 0; i < sbox.length; i++)
        {
            if (sbox[i].selected)
            {
                if (firstDone == 1)  categoryString = categoryString + ',';
                categoryString = categoryString + sbox[i].value;
                firstDone = 1;
            }
        }

        var number = document.getElementById('number').value;
        var string = '{$config_http_script_dir}/rss.php';

        if (document.getElementById('allcategories').checked || categoryString == '')
        {
            if (number != '')  string += '?number=' + number;
        }
        else
        {
            string += '?category=' + categoryString;
            if (number != '') string += '&number=' + number;
        }

        var htmlcode = '<a title="RSS Feed" href="' + string + '">' + '\n<img src="{$config_http_script_dir}/skins/images/rss_icon.gif" border=0/>\n</a>';
        document.getElementById('result').value = htmlcode;

    }
</script>
<table cellspacing="0" cellpadding="5" width="647" style="border-collapse: collapse; border: 0">
    <tr>
        <td width="647" colspan="3">After You have configured your RSS options, the RSS feed is ready to be
            used.
            <br>
            <br>URL Address of your RSS:
            <b>
                <a href="{$config_http_script_dir}/rss.php">{$config_http_script_dir}/rss.php</a>
                <br>&nbsp;</b>
        </td>
    </tr>
    <tr>
        <td bgcolor="#F7F6F4" style="border-bottom:1px solid gray;" width="647"
            colspan="3">
            <b>
                <font size="2">&nbsp;Customizing your RSS feed:</font>
            </b>
        </td>
    </tr>
    <tr>
        <td width="647" colspan="3">&nbsp;</td>
    </tr>
    <tr>
        <td width="58">&nbsp;</td>
        <td width="393">Number of articles to be shown in the RSS (default:10):</td>
        <td width="196">
            <input id=number size=5 type="text" size="20">
        </td>
    </tr>
    <tr>
        <td width="58">&nbsp;</td>
        <td width="393">Show articles only from these categories:</td>
        <td width="196">{$cat_options}</td>
    </tr>
    <tr>
        <td colspan="3" style="padding:40px;">After you have selected your preferred settings, click the 'Generate HTML
            Code' button and you are ready to insert this code into your page. The
            generated code will be of a linked RSS image that will be pointing to your
            RSS feed (rss.php).</td>
    </tr>
    <tr>
        <td width="647" colspan="3">
            <p align="center">
                <input type=button value="Generate HTML Code" onClick="generateCode();"
                       style="font-weight: bold; font-size:120%;">
                <br>
                <br>
                <textarea id=result rows="5" cols="100"></textarea>
        </td>
    </tr>
    <tr>
        <td width="647" colspan="3">&nbsp;</td>
    </tr>
</table>