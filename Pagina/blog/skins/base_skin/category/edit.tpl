<form action="{$PHP_SELF}?mod=categories" method="post">
    <input type=hidden name="action" value=doedit>
    <input type=hidden name="catid" value={$catid}>
    <input type=hidden name="csrf_code" value={$CSRF}>
    <table border="0" >
        <tr>
            <td width="100">Name</td>
            <td><input value="{$cat_arr[1]}" type=text name=cat_name></td>
        </tr>
        <tr>
            <td>Icon</td>
            <td><input value="{$cat_arr[2]}" type=text name=cat_icon></td>
        </tr>

        <tr>
            <td>Category Access</td>
            <td>
                <select name="cat_access">
                    <option {$if_all_access} value="0" selected>Everyone Can Write</option>
                    <option {$if_2_access} value="2">Only Editors and Admin</option>
                    <option {$if_1_access} value="1">Only Admin</option>
                </select>
            </td>
        </tr>

        <tr>
            <td></td>
            <td ><br><input type=submit value="Save Changes"</td>
        </tr>
    </table>
</form>