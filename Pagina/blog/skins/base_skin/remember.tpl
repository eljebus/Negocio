<script type="text/javascript">
var regex = /^[\.A-z0-9_\-\+]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/;
var regex2 = /((http(s?):\/\/)|(www\.))([\w\.]+)([\/\w+\.-?]+)/;

function CNreadCookie()
{
    var CNname = CNGetCookie('CNname');
    var CNmail = CNGetCookie('CNmail');

    if(CNname != null){ document.comment.name.value = CNname; }
    if(CNmail != null){ document.comment.mail.value = CNmail; }
}

function CNforget()
{
    var expDate = new Date();

    CNSetCookie('name', '', expDate);
    CNSetCookie('CNpass', '', expDate);
    CNSetCookie('CNname', '', expDate);
    CNSetCookie('CNmail', '', expDate);

    document.comment.name.value = '';
    document.comment.mail.value = '';

    alert("All Your personal information collected by CuteNews has been deleted!\nEnjoy your anonymity.");
}

function CNSubmitComment()
{
    var name = document.comment.name.value;
    var mail = document.comment.mail.value;
    var cbox = document.comment.comments.value;

    if (name == "")
    {
        alert('You must enter name.');
        return false;
    }
    else if ( mail != "" && !mail.match(regex) && !mail.match(regex2) )
    {
        alert ('This is not a valid e-mail or web site name');
        return false
    }
    else if (cbox == "")
    {
        alert('Sorry but the comment cannot be blank');
        return false;
    }

    if (document.comment.CNremember.checked)
    {
        var expDays = 365;
        var expDate = new Date();
        expDate.setTime(expDate.getTime() +  (24 * 60 * 60 * 1000 * expDays));

        CNSetCookie('CNname', document.comment.name.value, expDate);
        CNSetCookie('CNmail', document.comment.mail.value, expDate);
    }
    return true;
}

function CNRememberPass(pass, name, mail)
{
    var expDays = 365;
    var expDate = new Date();
    expDate.setTime(expDate.getTime() +  (24 * 60 * 60 * 1000 * expDays));
    CNSetCookie('CNpass', pass, expDate);
    CNSetCookie('CNname', name, expDate);
    CNSetCookie('CNmail', mail, expDate);
}

function CNGetCookieVal (offset)
{
    var endstr = document.cookie.indexOf (";", offset);
    if (endstr == -1)
    endstr = document.cookie.length;
    return decodeURIComponent(document.cookie.substring(offset, endstr));
}

function CNGetCookie (name)
{
    var arg = name + "=";
    var alen = arg.length;
    var clen = document.cookie.length;
    var i = 0;
    while (i < clen)
    {
        var j = i + alen;
        if (document.cookie.substring(i, j) == arg) return CNGetCookieVal (j);
        i = document.cookie.indexOf(" ", i) + 1;
        if (i == 0) break;
    }
    return null;
}

function CNSetCookie (name, value)
{
    var argv = CNSetCookie.arguments;
    var argc = CNSetCookie.arguments.length;
    var expires = (argc > 2) ? argv[2] : null;
    var path = (argc > 3) ? argv[3] : null;
    var domain = (argc > 4) ? argv[4] : null;
    var secure = (argc > 5) ? argv[5] : false;
    document.cookie = name + "=" + encodeURIComponent (value) +
    ((expires == null) ? "" : ("; expires=" + expires.toGMTString())) +
    ((path == null) ? "" : ("; path=" + path)) +
    ((domain == null) ? "" : ("; domain=" + domain)) +
    ((secure == true) ? "; secure" : "");
}

function FillMemberName(member_name, member_email)
{
    document.comment.name.value = member_name;
    document.comment.mail.value = member_email;

    document.comment.name.disabled = 'disabled';
    document.comment.mail.disabled = 'disabled';
}

</script>
<script type="text/javascript">CNreadCookie();</script>