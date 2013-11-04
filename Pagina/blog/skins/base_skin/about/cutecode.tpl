<html>
<head>
    <title>CuteCode</title>
    <style type="text/css">
    <!--
        body, td { text-decoration: none; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt; }
        a:active,a:visited,a:link {color: #446488; text-decoration: none; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt;}
        a:hover {font-size : 8pt; color: #000000; font-family: verdana; text-decoration: none; }
        table.spanned span { color: #808080; }
        table.spanned td { height: 21px; }
    -->
    </style>
    </head>
    <body bgcolor=#FFFFFF>
        <script type="text/javascript">
        <!--
        function insertcode(type,var1,var2)
        {
            if (var1 != null)
            {
                if(var2 == null) var2='  ';
                switch(type)
                {
                    case 'link':        code = '<a href="' + var1 + '">' + var2 + '</a>'; break;
                    case 'image':       code = '<img src="' + var1 + '" alt="" style="border: none;" />'; break;
                    case 'bold':        code = '<strong>' + var1 + '</strong>'; break;
                    case 'italic':      code = '<em>' + var1 + '</em>'; break;
                    case 'underline':   code = '<span style="text-decoration: underline;">' +var1+ '</span>'; break;
                    case 'color':       code = '<span style="color: ' + var1 + '">' +var2+ '</span>'; break;
                    case 'size':        code = '<span style="font-size: ' + var1 + 'pt">' +var2+ '</span>'; break;
                    case 'font':        code = '<span style="font-family: ' + var1 + '">' +var2+ '</span>'; break;
                    case 'align':       code = '<div style="text-align: ' + var1 + '">' +var2+ '</div>'; break;
                    case 'quote':       code = '[quote]' +var1+ '[/quote]'; break;
                    case 'list':

                        code = '<ul>\\n<li>Text1</li>\\n<li>Text2</li>\\n<li>Text3</li>\\n</ul>';
                        alert('Sample List will be inserted into the textarea');
                        break;

                    default:
                }

                code = ' ' + code + ' ';
                opener.document.addnews.{$target}.value  += code;
                if(document.my.ifClose.checked == true)
                {
                    opener.document.addnews.{$target}.focus();
                    window.close();
                    opener.document.addnews.{$target}.focus();
                }
            }
        }
        //-->
        </script>


        <h3><b>QuickTags</b></h3>
        <table border=0 width=320 cellspacing="0" cellpadding="0" class="spanned">

            <tr bgcolor=#F7F6F4>
                <td height=16 width="344"> <a href="javascript:insertcode('link', prompt('Enter the complete URL of the hyperlink', 'http://'), prompt('Enter the title of the webpage', '') )">Insert Link</a> </td>
                <td height=16> [link=<span>URL</span>]<span>Text</span>[/link]</td>
            </tr>

            <tr>
                <td height=16 width="344"><a href="javascript:insertcode('image', prompt('Enter URL of the Image:', 'http://') )">Insert Image</a></td>
                <td height=16>[image=<span>URL</span>]</td>
            </tr>

            <tr bgcolor=#F7F6F4>
                <td height=16 width="344"><a href="javascript:insertcode('quote', prompt('Text to Quote:', '') )">Insert Quote</a></td>
                <td height=16>[quote=<span>Name</span>]<span>Text</span>[/quote]</td>
            </tr>

            <tr>
                <td height=16 width="344"><a href="javascript:insertcode('list', 'none' )">Insert List</a></td>
                <td height=16>[list]<span>[*]Text1[*]Text2</span>[/list]</td>
            </tr>

            <tr bgcolor=#F7F6F4>
                <td height=16 width="344"><a href="javascript:insertcode('bold', prompt('Enter Text you want to be BOLD', '') )">Bold Text</a></td>
                <td height=16>[b]<span>Text</span>[/b]</td>
            </tr>

            <tr>
                <td height=16 width="344"><a href="javascript:insertcode('italic', prompt('Enter Text you want to be Italic', '') )">Italic Text</a></td>
                <td height=16>[i]<span>Text</span>[/i]</td>
            </tr>

            <tr bgcolor=#F7F6F4>
                <td height=16 width="344"><a href="javascript:insertcode('underline', prompt('Enter Text you want to be Underlined', '') )">Underlined Text</a></td>
                <td height=16>[u]<span>Text</span>[/u]</td>
            </tr>

            <tr>
                <td height=16 width="344"><a href="javascript:insertcode('color', prompt('Enter color of the text (blue, red, green, fuchsia)',''), prompt('Enter the text to be in this color','') )">Text Color</a></td>
                <td height=16>[color=<span>COLOR</span>]<span>Text</span>[/color]</td>
            </tr>

            <tr bgcolor=#F7F6F4>
                <td height=16 width="344"><a href="javascript:insertcode('size', prompt('Enter size of the text (in points format)',''), prompt('Enter the text to be in this size','') )">Text Size</a></td>
                <td height=16>[size=<span>SIZE</span>]<span>Text</span>[/size]</td>
            </tr>

            <tr>
                <td height=16 width="344"><a href="javascript:insertcode('font', prompt('Enter font of the text (verdana, arial, times, courier)',''), prompt('Enter the text to be in this font','') )">Text Font</a></td>
                <td height=16>[font=<span>FONT</span>]<span>Text</span>[/font]</td>
            </tr>

            <tr bgcolor=#F7F6F4>
                <td height=16 width="344"><a href="javascript:insertcode('align', prompt('Enter align of the text (right, left, center, justify)',''), prompt('Enter the text to be in this align','') )">Text Align</a></td>
                <td height=16>[align=<span>ALIGN</span>]<span>Text</span>[/align]</td>
            </tr>

        </table>
        <p>Close this window after I insert code</p>
    </body>
</html>