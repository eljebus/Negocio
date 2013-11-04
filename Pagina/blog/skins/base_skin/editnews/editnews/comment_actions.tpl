<tr>
    <td colspan="2">&nbsp;</td>
    <td align="right">delete all?</td>
    <td><input type=checkbox name=delcomid[all] value=1></td>
</tr>

<tr>
    <td colspan="2">&nbsp;</td>
    <td colspan="2"><p align="right"><input type="submit" value="Delete Selected"></td>
</tr>
</table>

<input type="hidden" name="newsid" value="{$id}">
<input type="hidden" name="deletecomment" value="yes">
<input type="hidden" name="action" value="doeditcomment">
<input type="hidden" name="mod" value="editcomments">
<input type="hidden" name="source" value="{$source}">

</form>
