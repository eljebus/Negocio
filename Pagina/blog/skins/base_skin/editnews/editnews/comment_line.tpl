<tr>
    <td>&nbsp;</td>
    <td {$bg}><a title="edit this comment, ip:{$comment_arr3}" href="{$PHP_SELF}?mod=editcomments&action=editcomment&newsid={$id}&comid={$comment_arr0}&source={$source}" onclick="window.open('{$PHP_SELF}?mod=editcomments&action=editcomment&newsid={$id}&comid={$comment_arr0}&source={$source}', 'Comments', 'HEIGHT=270,resizable=yes,scrollbars=yes,WIDTH=400');return false;">{$comment_arr1}</a>, {$comm_excerpt}</td>
    <td {$bg}><a title="edit this comment ip:{$comment_arr3}" href="{$PHP_SELF}?mod=editcomments&action=editcomment&newsid={$id}&comid={$comment_arr0}&source={$source}" onclick="window.open('{$PHP_SELF}?mod=editcomments&action=editcomment&newsid={$id}&comid={$comment_arr0}&source=$source', 'Comments', 'HEIGHT=270,resizable=yes,scrollbars=yes,WIDTH=400');return false;">{$comtime}</a> </td>
    <td width="1" {$bg}> <input type=checkbox name="delcomid[{$comment_arr0}]" value=1> </td>
</tr>