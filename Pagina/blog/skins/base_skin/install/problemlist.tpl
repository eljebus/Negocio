{if $fail}

<br/><br/>
<h2 style="color: red;">Some problem occurred by migration script</h2>
<table>
    <tr bgcolor="#FFFFC0">
        <th>Problem</th>
        <th>Source</th>
        <th>Destination</th>
    </tr>

    {foreach from=fail}

        <tr>
            <td>{$fail.0}</td>
            <td>{$fail.1}</td>
            <td>{$fail.1}</td>
        </tr>

    {/foreach}

</table>

{/if}