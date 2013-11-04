<div style="margin: 0 0 0 64px;">
    <form action="update_cutenews.php" method="post">
        <input type="hidden" name="action" value="do_update"/>
        <input type="hidden" name="last_version" value={$last_version_name}/>
        <p style="background: #FFFFE0">Have a new version: <span style="color:red">{$last_version}</span></p>
        <p>In case of fatal update errors please download the latest version with github and rewrite all files by FTP.</p>
        <p><input type="submit" value="Try to update" /></p>
    </form>
</div>