{if $errors}
<table width="750" class="std-table">
    <tr><th>Message</th> <th>File</th> <th>Permission</th> </tr>
    {foreach from=errors}
        <tr>
            <td class="cn">{$errors.msg}</td>
            <td>{$errors.file}</td>
            <td class="cn">{$errors.perm}</td>
        </tr>
    {/foreach}
</table>
{/if}