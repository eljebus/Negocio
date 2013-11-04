{foreach from=the_entry}

    <tr bgcolor="{$the_entry.bg}" class="hover">
        <td align="center"> {$the_entry.order} </td>

        <td> <a title='Edit: {$the_entry.title}' href="{$PHP_SELF}?mod=editnews&amp;action=editnews&amp;id={$the_entry.id}&amp;source={$the_entry.source}">{$the_entry.title}</a> {$the_entry.pros}</td>

        <td align='center'>{$the_entry.comments}</td>
        <td align='center'>{$the_entry.category}</td>
        <td  align="center">{$the_entry.itemdate}</td>
        <td>{$the_entry.user}</td>
        <td align=center><input name="selected_news[]" value="{$the_entry.id}" style="border:0;" type='checkbox'></td>
    </tr>

{/foreach}
