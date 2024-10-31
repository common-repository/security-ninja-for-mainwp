"use strict";function show_white_label_popup(){console.log("show_white_label_popup function is being executed.");var e=jQuery('<div id="mainwp-popup" class="ui modal"></div>'),n=jQuery('<h3 class="ui dividing header">'+secninja_mainwp.texts.headline+"</h3>"),a=jQuery('<p style="padding: 10px;">'+secninja_mainwp.texts.subline+"</p>");e.append(n),e.append(a);var i=jQuery('<form method="POST" action="" class="ui form" id="mainwp-whitelabel-form"></form>');i.append('<input type="hidden" name="site_id" id="backup_site_id" value="">'),i.append('<input type="hidden" name="backup_site_full_size" id="backup_site_full_size" value="">'),i.append('<input type="hidden" name="backup_site_db_size" id="backup_site_db_size" value="">');var t=jQuery('<div class="ui grid field" style="padding-bottom: 10px;"><label class="six wide column middle aligned">'+secninja_mainwp.texts.enableWhiteLabel+'</label><div class="ten wide column"><div class="ui radio checkbox"><input type="radio" name="wl_active" value="1" checked><label>'+secninja_mainwp.texts.enableWhiteLabel+"</label></div></div></div>"),s=jQuery('<div class="ui grid field"><label class="six wide column middle aligned"></label><div class="ten wide column"><div class="ui radio checkbox"><input type="radio" name="wl_active" value="0"><label>'+secninja_mainwp.texts.disableWhiteLabel+"</label></div></div></div>");i.append(t),i.append(s),i.append(o("wl_newname",secninja_mainwp.texts.pluginName,secninja_mainwp.texts.enterPluginName)),i.append(o("wl_newdesc",secninja_mainwp.texts.pluginDescription,secninja_mainwp.texts.enterPluginDescription)),i.append(o("wl_newauthor",secninja_mainwp.texts.authorName,secninja_mainwp.texts.enterAuthorName)),i.append(o("wl_newurl",secninja_mainwp.texts.authorURL,secninja_mainwp.texts.enterAuthorURL)),i.append(o("wl_newiconurl",secninja_mainwp.texts.pluginIconURL,secninja_mainwp.texts.enterPluginIconURL)),i.append(o("wl_newmenuiconurl",secninja_mainwp.texts.pluginMenuIconURL,secninja_mainwp.texts.enterPluginMenuIconURL));var c=jQuery('<div id="message-container" style="margin-top: 20px; padding: 10px; display: none;"></div>');i.append(c),e.append(i);var l=jQuery('<button class="ui button">'+secninja_mainwp.texts.close+"</button>").click((function(){e.modal("hide")})),u=jQuery('<button class="ui green button" id="mainwp-submit-button">'+secninja_mainwp.texts.sendToSelectedSites+"</button>").click((function(){var n;n=!0,jQuery("input[required]").each((function(){if(!jQuery(this).val().trim())return n=!1,c.text(secninja_mainwp.texts.pleaseFillInAllRequiredFields).css("background-color","#ffe6e6").css("color","#cc0000").show(),!1})),n&&function(){var n={wl_active:jQuery('input[name="wl_active"]:checked').val(),wl_newname:jQuery("#wl_newname").val(),wl_newdesc:jQuery("#wl_newdesc").val(),wl_newauthor:jQuery("#wl_newauthor").val(),wl_newurl:jQuery("#wl_newurl").val(),wl_newiconurl:jQuery("#wl_newiconurl").val(),wl_newmenuiconurl:jQuery("#wl_newmenuiconurl").val()},a=jQuery.map(jQuery("#mainwp-manage-sites-body-table .check-column INPUT:checkbox:checked"),(function(e){return parseInt(jQuery(e).val(),10)}));jQuery.ajax({type:"POST",url:ajaxurl,data:{_ajax_nonce:secninja_mainwp.nonce_secnin,action:"secnin_run_update_white_label_module",site_ids:a,white_label_settings:n},dataType:"json",success:function(n){console.log(n),e.modal("hide"),c.text(secninja_mainwp.texts.settingsUpdatedSuccessfully).css("background-color","#e6ffe6").css("color","#00cc00").show()},error:function(e){console.log(e),c.text(secninja_mainwp.texts.anErrorOccurred).css("background-color","#ffe6e6").css("color","#cc0000").show()}})}()})),r=jQuery('<div class="actions"></div>').append(l).append(u);function o(e,n,a){return jQuery('<div class="ui grid field"><label class="six wide column middle aligned">'+n+':</label><div class="ten wide column"><input type="text" id="'+e+'" name="'+e+'" placeholder="'+a+'" required></div></div>')}e.append(r),jQuery("body").append(e),e.modal({closable:!0}).modal("show"),jQuery(".ui.checkbox").checkbox(),jQuery('input[name="wl_active"]').on("change",(function(){"0"===jQuery(this).val()?(jQuery("#plugin-name, #plugin-description, #author-name, #author-url, #plugin-icon-url, #plugin-menu-icon-url").closest(".field").hide().find("input").prop("disabled",!0).removeAttr("required"),c.text(secninja_mainwp.texts.warningDisable).css("background-color","#ffe6e6").css("color","#cc0000").show()):(jQuery("#plugin-name, #plugin-description, #author-name, #author-url, #plugin-icon-url, #plugin-menu-icon-url").closest(".field").show().find("input").prop("disabled",!1).attr("required","required"),c.hide())})).trigger("change")}jQuery(document).ready((function(){jQuery(document).on("click","#mainwp-do-sites-bulk-actions",(function(){var e=jQuery("#mainwp-sites-bulk-actions-menu").dropdown("get value");if(""==e)return!1;if("secnin_runtests"==e||"..."==e){if(mainwpVars.bulkManageSitesTaskRunning)return;var n=secninja_mainwp.texts.runAllSecurityTests;return mainwp_confirm(n,_callback=function(){var e=jQuery.map(jQuery("#mainwp-manage-sites-body-table .check-column INPUT:checkbox:checked"),(function(e){return parseInt(jQuery(e).val(),10)}));jQuery.ajax({type:"POST",url:ajaxurl,data:{_ajax_nonce:secninja_mainwp.nonce_secnin,action:"secnin_run_remote_security_tests",site_ids:e},dataType:"json",success:function(e){console.log(e)},error:function(e){console.log(e)}})}),!1}if("secnin_runmalware"==e||"..."==e){if(mainwpVars.bulkManageSitesTaskRunning)return;n=secninja_mainwp.texts.runMalwareScansOnSelectedSites;return mainwp_confirm(n,_callback=function(){var e=jQuery.map(jQuery("#mainwp-manage-sites-body-table .check-column INPUT:checkbox:checked"),(function(e){return parseInt(jQuery(e).val(),10)}));jQuery.ajax({type:"POST",url:ajaxurl,data:{_ajax_nonce:secninja_mainwp.nonce_secnin,action:"secnin_run_remote_malwarescan",site_ids:e},dataType:"json",success:function(e){console.log(e)},error:function(e){console.log(e)}})}),!1}if("secnin_update_white_label"==e||"..."==e){if(mainwpVars.bulkManageSitesTaskRunning)return;if("secnin_update_white_label"==e)return show_white_label_popup(),!1;var a=jQuery.map(jQuery("#mainwp-manage-sites-body-table .check-column INPUT:checkbox:checked"),(function(e){return parseInt(jQuery(e).val(),10)}));return jQuery.ajax({type:"POST",url:ajaxurl,data:{_ajax_nonce:secninja_mainwp.nonce_secnin,action:"secnin_run_update_white_label_module",site_ids:a,white_label_settings:whiteLabelSettings},dataType:"json",success:function(e){console.log(e),jQuery("#mainwp-popup").remove()},error:function(e){console.log(e)}}),!1}})),jQuery("#mainwp-site-mode-wrap .collapsible").click((function(){jQuery(this).next(".section-content").slideToggle("fast")})),jQuery("#security-ninja-events").DataTable({processing:!0,serverSide:!0,autoWidth:!0,stateSave:!0,search:!0,ajax:{url:ajaxurl,type:"POST",data:function(e){return jQuery.extend({},e,{action:"secnin_get_latest_events",_ajax_nonce:secninja_mainwp.nonce_secnin})},dataSrc:function(e){return e.data}},order:[[0,"desc"]],select:{style:"multi"},columns:[{data:"timestamp"},{data:"site_id"},{data:"ip"},{data:"action"},{data:"description"}],pageLength:25,rowCallback:function(e,n){jQuery(e).addClass("wp-list-table widefat fixed striped table-view-list")},pagingType:"full_numbers",scrollX:!0,lengthMenu:[[10,25,50,100,250,500],[10,25,50,100,250,500]],language:{emptyTable:secninja_mainwp.texts.no_events}})}));
//# sourceMappingURL=security-ninja-mainwp-min.js.map