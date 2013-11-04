<form method=post action="{$PHP_SELF}">
    <p>{{Are you sure you want to delete the template}}<b>{$do_template}</b>?</p>
    <input type=submit value=" {{Yes, Delete This Template}} "> &nbsp;
    <input onclick="document.location='{$PHP_SELF}?mod=options&action=templates';" type=button value="{{Cancel}}">
    <input type=hidden name=mod value=options>
    <input type=hidden name=action value=templates>
    <input type=hidden name=subaction value=dodelete>
    <input type=hidden name=do_template value="{$do_template}">
</form>