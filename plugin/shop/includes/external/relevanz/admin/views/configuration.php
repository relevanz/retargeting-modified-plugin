<?php
/* -----------------------------------------------------------
Copyright (c) 2020 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/

echo '
<form id="relevanz-configuration-form" action="'.$data['action'].'"
      method="post" enctype="multipart/form-data">';
if ((CSRF_TOKEN_SYSTEM == 'true') && isset($_SESSION['CSRFToken']) && isset($_SESSION['CSRFName'])) {
    echo '
    <input type="hidden" name="'.$_SESSION['CSRFName'].'" value="'.$_SESSION['CSRFToken'].'">';
}
echo '
<table><tbody>
    <tr>
        <td class="label"><label for="conf_apikey">'.RELEVANZ_LABEL_APIKEY.'</label></td>
        <td class="info has-tooltip">
            <span class="tooltip">
                '.RELEVANZ_LABEL_APIKEY_TOOLTIP.'<br><br>
                <a class="register-now" href="'.RELEVANZ_LABEL_APIKEY_TOOLTIP_LINK_HREF.'" target="_blank">'.RELEVANZ_LABEL_APIKEY_TOOLTIP_LINK_TEXT.'</a>
            </span>
        </td>
        <td class="input">
            <input type="text" id="conf_apikey" name="conf[apikey]" value="'.$data['credentials']->getApiKey().'" required />
        </td>
    </tr>';

if ($data['credentials']->isComplete()) {
    echo '
    <tr>
        <td class="label"><label for="conf_apikey">'.RELEVANZ_LABEL_CUSTOMERID.'</label></td>
        <td class="info"></td>
        <td class="input">
            <input type="text" value="'.$data['credentials']->getUserId().'" readonly="">
        </td>
    </tr>
    <tr>
        <td class="label"><label for="conf_apikey">'.RELEVANZ_LABEL_EXPORTURL.'</label></td>
        <td class="info has-tooltip">
            <span class="tooltip">'.RELEVANZ_LABEL_EXPORTURL_TOOLTIP.'</span>
        </td>
        <td class="input">
            <input type="text" value="'.$data['urlExport'].'" readonly="">
        </td>
    </tr>
    <tr>
        <td class="label"><label for="conf_additional_html">'.RELEVANZ_LABEL_ADDITIONAL_HTML.'</label></td>
        <td class="info has-tooltip">
            <span class="tooltip">'.RELEVANZ_LABEL_ADDITIONAL_HTML_TOOLTIP.'</span>
        </td>
        <td class="input">
            <textarea id="conf_additional_html" name="conf[additional_html]" rows="6" style="width:100%;font-family:monospace;font-size:12px;">'.htmlspecialchars((string)$data['additionalHtml']).'</textarea>
        </td>
    </tr>';
}

echo '
</tbody></table>
<div class="button-bar">
    <input class="button" type="submit" value="'.BUTTON_SAVE.'">
</div>
</form>';

?>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        $('input[type="text"][readonly]').click(function () {
            var t = $(this)[0];
            t.focus();
            t.select();
        });
        $('td.has-tooltip').click(function () {
            $.dialog({
                title: '',
                content: $(this).find('.tooltip').html(),
                columnClass: 'jalert-width fix-close-icon',
                animation: 'none',
            });
        });
    });
</script>
