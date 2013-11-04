<div  style="margin: 0 0 0 62px;">

    <p><b>Note:</b> put %some_name_key in replacement.</p>
    <p>The .htaccess file will be changed.</p>
    <p>The Rewrite module requires <a href="http://www.php.net/manual/en/book.mbstring.php" target="_blank">mbstring<a/>.</p>


    <form action="{$PHP_SELF}" method="post">

        <input type="hidden" name="mod" value="tools" />
        <input type="hidden" name="action" value="rewrite" />
        <input type="hidden" name="subaction" value="save" />

        <table cellpadding="4" cellspacing="0">

            <tr> <td>&nbsp;</td> <td colspan="2">{$saved_ok}</td></tr>
            <tr bgcolor="#ffe0c0">
                <td><b>URL Template name</b></td>
                <td><b>Template Value</b></td>
                <td><b>Use layout (your *.php) <sup><a href="#">?</a></sup></b></td>
            </tr>

            <tr>
                <td><b>.htaccess path</b></td>
                <td colspan="2">
                    <div><input type="text" name="conf_htaccess" style="width: 412px; border: 1px solid #cccccc; color: #446688; font-weight: bold;" value="{$conf_rw_htaccess}"/></div>
                    <div><input type="checkbox" checked="checked" name="update_htaccess" value="Y"/> Try to update .htaccess file</div>
                </td>
            </tr>

            <tr>
                 <td>Read Full Story</td>
                 <td><input type="text" name="conf_readmore" style="width: 200px; border: 1px solid #cccccc;" value="{$conf_rw_readmore}"/></td>
                 <td><input type="text" name="conf_readmore_layout" style="width: 200px; border: 1px solid #cccccc;" value="{$conf_rw_readmore_layout}"/></td>
            </tr>

            <tr>
                <td>Archive ID</td>
                <td><input type="text" name="conf_archread" style="width: 200px;"  value="{$conf_rw_archread}"/></td>
                <td><input type="text" name="conf_archread_layout" style="width: 200px; border: 1px solid #cccccc;" value="{$conf_rw_archread_layout}"/></td>
            </tr>

            <tr>
                <td>Comment</td>
                <td><input type="text" name="conf_readcomm" style="width: 200px;"  value="{$conf_rw_readcomm}"/></td>
                <td><input type="text" name="conf_readcomm_layout" style="width: 200px; border: 1px solid #cccccc;" value="{$conf_rw_readcomm_layout}"/></td>
            </tr>

            <tr>
                <td>News pagination</td>
                <td><input type="text" name="conf_newspage" style="width: 200px;"  value="{$conf_rw_newspage}"/></td>
                <td><input type="text" name="conf_newspage_layout" style="width: 200px; border: 1px solid #cccccc;" value="{$conf_rw_newspage_layout}"/></td>
            </tr>

            <tr>
                <td>Comment pagination</td>
                <td><input type="text" name="conf_commpage" style="width: 200px;"  value="{$conf_rw_commpage}"/></td>
                <td><input type="text" name="conf_commpage_layout" style="width: 200px; border: 1px solid #cccccc;" value="{$conf_rw_commpage_layout}"/></td>
            </tr>

            <tr>
                <td>Read Full Story archive</td>
                <td><input type="text" name="conf_archreadmore" style="width: 200px;"  value="{$conf_rw_archreadmore}"/></td>
                <td><input type="text" name="conf_archreadmore_layout" style="width: 200px; border: 1px solid #cccccc;" value="{$conf_rw_archreadmore_layout}"/></td>
            </tr>

            <tr>
                <td>Archive comment</td>
                <td><input type="text" name="conf_archreadcomm" style="width: 200px;"  value="{$conf_rw_archreadcomm}"/></td>
                <td><input type="text" name="conf_archreadcomm_layout" style="width: 200px; border: 1px solid #cccccc;" value="{$conf_rw_archreadcomm_layout}"/></td>
            </tr>

            <tr>
                <td>Archive comment pagination</td>
                <td><input type="text" name="conf_archcommpage" style="width: 200px;"  value="{$conf_rw_archcommpage}"/></td>
                <td><input type="text" name="conf_archcommpage_layout" style="width: 200px; border: 1px solid #cccccc;" value="{$conf_rw_archcommpage_layout}"/></td>
            </tr>

            <tr>
                <td>&nbsp;</td>
                <td><input type="submit" value="Submit changes" /></td>
            </tr>

        </table>

    </form>

    <div><b>DO NOT</b> use "?" symbol anywhere.</div>
</div>