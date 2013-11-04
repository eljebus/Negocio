<form action="{$PHP_SELF}" method="post" >

    <input type="hidden" name="csrf_code" value="{$CSRF}" />
    <input type="hidden" name="mod" value="massactions" />
    <input type="hidden" name="action" value="dochangedate" />
    {if $source}<input type="hidden" name="source" value="{$source}" />{/if}
    <h4 style="font-size: 15px; margin: 0; padding: 0;">{{Set new date for selected news}}</h4>
    <br/>
    {foreach from=the_selected_news}
        <div><input style="text-align: center;" type="text" name="dates[{$the_selected_news.id}]" value="{$the_selected_news.date}" /> {$the_selected_news.title}</div>
    {/foreach}
    <br/>
    <input type="submit" value=" Do Change Date " />
</form>