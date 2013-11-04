
<form action="{$PHP_SELF}" method="post" enctype="multipart/form-data">

    <input type="hidden" name="mod" value="tools" />
    <input type="hidden" name="action" value="report" />
    <input type="hidden" name="do" value="report" />

    <table>
        <tr>
            <td width="140" valign="top">Your registration/private<br/> key for bugreport*:</td>
            <td>
                <div><input type="text" style="width: 320px;" name="key" value="{$key}" /></div>
                <div style="color: gray;">* Give your keys only to trusted individuals</div>
                <br/>
            </td>
        </tr>
        <tr>
            <td>Report title:</td>
            <td><input type="text" style="width: 320px;" name="title" value="Bug report @ {$time}" /></td>
        </tr>
        <tr>
            <td valign="top">Report description:</td>
            <td><textarea style="width: 320px;" rows="8" cols="40" name="desc"></textarea></td>
        </tr>
        <tr>
            <td valign="top">Screenshot:</td>
            <td><input type="file" name="scrshot" /></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>
                <div><input type="submit" value="  submit report " ></div>
            </td>
        </tr>
        <tr><td>&nbsp;</td>
            <td><p><b>Note:</b> We will not send any confidential information.<br/> The file is saved to a report just for you and you have<br/> the right not to send him anywhere.</p></td></tr>
    </table>


</form>



