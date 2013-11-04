<h2>Configure your site</h2>
<form action="{$PHP_SELF}" method="POST">

    <input type="hidden" name="action" value="finish">

    <p>Please fill the required information correct for the script to work properly</p>
    <table width="640" style="border: 1px solid #f0f0f0; border-radius: 4px; background: #F7F6F4;">

        <tr>
            <td width="150" align="right">Sitename: </td>
            <td><input type="text" style="width: 200px;" id="site" name="site" value="http://{site}"/> Note: without end slash /</td>
        </tr>
        <tr>
            <td width="150" align="right">Administrator Username: </td>
            <td><input type="text" style="width: 200px;" id="user" name="user" value="admin" /></td>
        </tr>
        <tr>
            <td width="150" align="right">Password: </td>
            <td><input type="password" style="width: 100px;" id="password" name="password" /></td>
        </tr>
        <tr>
            <td width="150" align="right">Retype: </td>
            <td><input type="password" style="width: 100px;" id="retype" name="retype" /></td>
        </tr>
        <tr>
            <td width="150" align="right">Administrator email: </td>
            <td><input type="text" style="width: 200px;" id="email" name="email" /></td>
        </tr>
    </table>

    <p>More additional information (optional)</p>
    <table width="640" style="border: 1px solid #f4f4f4; border-radius: 4px; background: #FAF9F8;">

        <tr>
            <td width="150" align="right">Nick name: </td>
            <td><input type="text" style="width: 200px;" name="nick" /></td>
        </tr>

        <tr>
            <td width="150" align="right">Install &amp;</td>
            <td><input type="submit" value="Go!" /></td>
        </tr>

    </table>

</form>