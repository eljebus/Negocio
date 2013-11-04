<div style="clear: both; margin: 0 8px 0 0" xmlns="http://www.w3.org/1999/html">

    <div style="margin: 16px 0 32px 0;">

        <h3 style="font-size: 17px; margin: 0 0 10px 0;">Dashboard</h3>
        {foreach from=fs}<div class="hover" style="border-bottom: 1px dashed #80C0C0; padding: 3px 0 3px 0;"><div style="clear: left; float: left;">{$fs.0}</div><div style="text-align: right;">{$fs.1}</div></div>{/foreach}

        {if $free}
        <div style="margin: 16px 0 0 0; float: left; width: 745px; height: 20px; border: 1px solid gray;"><div style="float: left; width: {$free}%; background: #0080FF; height: 16px; color: white; text-align: center; padding: 2px;">Used {$free}%</div></div>
        <div style="clear: left;"></div>
        {/if}

        <div style="margin: 20px 0 0 0; text-align: right;">
            <a href="{$PHP_SELF}?mod=main&amp;action=permissions">Run permissions check</a>
            {if $data_folder_exists}| <a href="migrate_to_latest.php">Migrate to the latest</a>{/if}
        </div>

    </div>

</div>

<div>
    <a href="example1.php" target="blank">Example 1</a> &middot;
    <a href="example2.php" target="blank">Example 2</a> &middot;
    <a href="{$config_http_script_dir}/README.html">Readme</a>
</div>