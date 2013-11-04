<html>
<head>
    <title>Insert Image</title>
    <style type="text/css">
    <!--
        select, option, textarea, input
        {
            border: #808080 1px solid;
            color: #000000;
            font-size: 11px;
            font-family: Verdana, Arial, serif;
            background-color: #ffffff
        }
        body, td {text-decoration: none; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt;}
        a:active,a:visited,a:link {font-size : 10px; color: #808080; font-family: verdana; text-decoration: none;}
        a:hover {font-size : 10px; color: darkblue; font-weight:bold; text-decoration: none; }
        .panel  { border: 1px dotted silver; background-color: #F7F6F4;}
    -->
    </style>
</head>

<body bgcolor="white">
<script language="javascript" type="text/javascript">

    function insertimage(selectedImage)
    {
        {WYSYWIG}
            window.opener.CKEDITOR.tools.callFunction("{$CKEditorFuncNum}", '{$config_http_script_dir}/uploads/' + selectedImage);
            window.close();
        {/WYSYWIG}
        {-WYSYWIG}
            var area = '{$area}';

            alternativeText = document.forms['properties'].alternativeText.value;
            imageAlign = document.forms['properties'].imageAlign.value;
            imageBorder = document.forms['properties'].imageBorder.value;

            var appends = '';
            var imageWidth = document.forms['properties'].imageWidth.value;
            var imageHeight = document.forms['properties'].imageHeight.value;
            if (imageWidth) appends += ' width=' + imageWidth;
            if (imageHeight) appends += ' height=' + imageWidth;

            finalImage = " <img " + appends + " border='" + imageBorder + "' align='" + imageAlign +"' alt='" + alternativeText + "' src='{$config_http_script_dir}/uploads/" + selectedImage +"'>";
            opener.document.getElementById(area).value += finalImage;
            window.close();
        {/-WYSYWIG}
    }

    function PopupPic(sPicURL)
    {
        window.open('{$PHP_SELF}?mod=images&action=preview&image=' + sPicURL, '', 'resizable=1,HEIGHT=200,WIDTH=200');
    }

    window.resizeTo(410, 550);
    self.focus();

</script>