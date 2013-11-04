<script type='text/javascript'>

    function AddRowsToTable()
    {
        var tbl = document.getElementById('tblSample');
        var lastRow = tbl.rows.length;

        // if there's no header row in the table, then iteration = lastRow + 1
        var iteration = lastRow+1;
        var row = tbl.insertRow(lastRow);

        var cellRight = row.insertCell(0);
        var el = document.createElement('input');
        el.setAttribute('type', 'file');
        el.setAttribute('name', 'image_' + iteration);
        el.setAttribute('size', '30');
        el.setAttribute('value', iteration);
        cellRight.appendChild(el);

        document.getElementById('images_number').value = iteration;
    }
    function RemoveRowFromTable()
    {
        var tbl = document.getElementById('tblSample');
        var lastRow = tbl.rows.length;
        if (lastRow > 1)
        {
            tbl.deleteRow(lastRow - 1);
            document.getElementById('images_number').value =  document.getElementById('images_number').value - 1;
        }
    }

</script>
<form name="form" id="form" action="{$PHP_SELF}?mod=images" method="post" enctype="multipart/form-data">

    <input type="hidden" name="csrf_code" value="{$CSRF}" />
    <table border=0 cellpadding=0 cellspacing=0  width=100%>

        <td height="33">
            <b>Upload Image</b>
            <table border=0 cellspacing=0 class="panel" cellpadding=8>
                <tr>
                    <td height=25>

                        <table  border="0" cellspacing="0" cellpadding="0" id="tblSample">
                            <tr id="row">
                                <td width="1" colspan="2"><input type="file" size="30" name="image_1"></td>
                            </tr>
                        </table>

                        <table border="0" cellspacing="0" cellpadding="0" style="margin-top:5px;">
                            <tr>
                                <td>
                                    <INPUT TYPE="submit" name="submit" VALUE="Upload" style="font-weight:bold;"> &nbsp;
                                    <input type=button value='-' style="font-weight:bold; width:22px;" title='Remove last file input box' onClick="RemoveRowFromTable();return false;">
                                    <input type=button value='+' style="font-weight:bold; width:22px;" title='Add another file input box' onClick="AddRowsToTable();return false;"> &nbsp;
                                    <input style="border: 0; background-color:#F7F6F4;" type=checkbox name=overwrite id=overwrite value=1><label title='Overwrite file(s) if exist' for=overwrite> Overwrite</label>
                                </td>
                            </tr>
                        </table>
                        {$img_result}
            </table>

            <input type=hidden name='wysiwyg' value='{$wysiwyg}' />
            <input type=hidden name='CKEditorFuncNum' value='{$CKEditorFuncNum}' />
            <input type=hidden name='subaction' value=upload />
            <input type=hidden name='area' value='{$area}' />
            <input type=hidden name='action' value='{$action}' />
            <input type=hidden name='images_number' id='images_number' value='1' />
</form>

{QUICK}
    <form name=properties>
        <input type=hidden name=CKEditorFuncNum value='{$CKEditorFuncNum}'>
        <table style='margin-top:10px;' border=0 cellpadding=0 cellspacing=0  width=100%>

            <td height=33>

                <b>Image Properties</b>
                <table border=0 cellpadding=0 cellspacing=0 class="panel" style='padding:5px'width=290px; >

                    <tr>
                        <td width=80>Alt. Text: </td>
                        <td><input tabindex=1 type=text name=alternativeText style="width:150px;"></td>
                    </tr>

                    <tr>
                        <td>Image Align</td>
                        <td>
                            <select name='imageAlign' style='width:150'>
                                <option value=none>None</option>
                                <option value=left>Left</option>
                                <option value=right>Right</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td>Border</td>
                        <td><input type=text value='0' name=imageBorder style="width:35px"> pixels</td>
                    </tr>
                    <tr>
                        <td>Width</td>
                        <td><input type=text value='' name=imageWidth style="width:35px"> pixels</td>
                    </tr>
                    <tr>
                        <td>Height</td>
                        <td><input type=text value='' name=imageHeight style="width:35px"> pixels</td>
                    </tr>

                </table>
        </table>
    </form>
{/QUICK}
<br/>
<tr><td><b>Uploaded Images</b></tr>
<tr><td height=1>
    <form action='{$PHP_SELF}?mod=images' METHOD='POST'>
        <input type='hidden' name='csrf_code' value='{$CSRF}' />
        <table width='100%' cellspacing=0 cellpadding=0>