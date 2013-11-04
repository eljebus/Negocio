<h3>The first launch</h3>
<p>You've launched Cutenews in trial mode. For the full mode please enter your username, password and the domain where the copy of Cutenews has been installed. Now everyone can enter the admin panel. <strong style="color: red;">Please make all settings as soon as possible.</strong>.</p>
<p><strong>After installation</strong>, set up in Options &gt; System Configuration site name (General &gt; Full URL to CuteNews directory), notification email (Notifications &gt; Email(s)), and other parameters</p>
<p style="text-align: right;">&rarr; <strong><a href="migrate_to_latest.php">Run migration script</strong></p>

<h1 style="font-size: 18px; margin: 0; padding: 12px 0 0 0;">Create admin user</h1>
<form action="{$PHP_SELF}" method="POST">
    <table border="0" cellpadding="0" cellspacing="0" width="500">
        <input type="hidden" name="section" value="main_area" />
        <tr><td>Name</td> <td><input type="text" name="admin_name" value="admin" /></td> </tr>
        <tr><td>Password</td> <td><input type="text" name="admin_passwd" value="your_password" /></td> </tr>
        <tr><td>Email</td> <td><input type="text" name="admin_email" value="youremail@example.com" /></td> </tr>
        <tr><td>&nbsp;</td><td><input type="submit" name="submit" value="Submit" /></td></tr>
    </table>
    <br/>
</form>

<hr style="border: 2px dashed #A0A0A0; border-top: none;"/>
<br/>