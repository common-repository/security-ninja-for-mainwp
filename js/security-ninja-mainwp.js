/* globals jQuery:true, ajaxurl:true, secninja_mainwp:true */
function show_white_label_popup() {
  console.log('show_white_label_popup function is being executed.');

  // Create the popup container
  var popup = jQuery('<div id="mainwp-popup" class="ui modal"></div>');

  // Add headline with prefix
  var headline = jQuery('<h3 class="ui dividing header">' + secninja_mainwp.texts.headline + '</h3>');
  // 
  var subline = jQuery('<p style="padding: 10px;">'+ secninja_mainwp.texts.subline +'</p>');
  popup.append(headline);
  popup.append(subline);

  // Create the form
  var form = jQuery('<form method="POST" action="" class="ui form" id="mainwp-whitelabel-form"></form>');

  // Add hidden form fields
  form.append('<input type="hidden" name="site_id" id="backup_site_id" value="">');
  form.append('<input type="hidden" name="backup_site_full_size" id="backup_site_full_size" value="">');
  form.append('<input type="hidden" name="backup_site_db_size" id="backup_site_db_size" value="">');

  // Add radio button groups with padding
  var enableGroup = jQuery('<div class="ui grid field" style="padding-bottom: 10px;"><label class="six wide column middle aligned">'+ secninja_mainwp.texts.enableWhiteLabel +'</label><div class="ten wide column"><div class="ui radio checkbox"><input type="radio" name="wl_active" value="1" checked><label>'+ secninja_mainwp.texts.enableWhiteLabel +'</label></div></div></div>');
  var disableGroup = jQuery('<div class="ui grid field"><label class="six wide column middle aligned"></label><div class="ten wide column"><div class="ui radio checkbox"><input type="radio" name="wl_active" value="0"><label>'+ secninja_mainwp.texts.disableWhiteLabel +'</label></div></div></div>');
  form.append(enableGroup);
  form.append(disableGroup);

  // Add input fields with specific ids
  form.append(createInputField('wl_newname', secninja_mainwp.texts.pluginName, secninja_mainwp.texts.enterPluginName));
  form.append(createInputField('wl_newdesc', secninja_mainwp.texts.pluginDescription, secninja_mainwp.texts.enterPluginDescription));
  form.append(createInputField('wl_newauthor', secninja_mainwp.texts.authorName, secninja_mainwp.texts.enterAuthorName));
  form.append(createInputField('wl_newurl', secninja_mainwp.texts.authorURL, secninja_mainwp.texts.enterAuthorURL));
  form.append(createInputField('wl_newiconurl', secninja_mainwp.texts.pluginIconURL, secninja_mainwp.texts.enterPluginIconURL));
  form.append(createInputField('wl_newmenuiconurl', secninja_mainwp.texts.pluginMenuIconURL, secninja_mainwp.texts.enterPluginMenuIconURL));

  // Add message container with dynamic style
  var message = jQuery('<div id="message-container" style="margin-top: 20px; padding: 10px; display: none;"></div>');
  form.append(message);

  // Append form to popup
  popup.append(form);

  // Buttons for actions
  var closeButton = jQuery('<button class="ui button">'+ secninja_mainwp.texts.close+'</button>').click(function() {
      popup.modal('hide');
  });
  var sendButton = jQuery('<button class="ui green button" id="mainwp-submit-button">'+ secninja_mainwp.texts.sendToSelectedSites +'</button>').click(function() {
      if (validateForm()) {
          sendToSelectedSites();
      }
  });
  var actionsDiv = jQuery('<div class="actions"></div>').append(closeButton).append(sendButton);
  popup.append(actionsDiv);

  // Append popup to body
  jQuery('body').append(popup);

  // Initialize Semantic UI modal
  popup.modal({ closable: true }).modal('show');
  jQuery('.ui.checkbox').checkbox();

  // Enable or disable input fields based on radio button selection
  jQuery('input[name="wl_active"]').on('change', function() {
      var enableValue = jQuery(this).val();
      if (enableValue === '0') { // Disable
          jQuery('#plugin-name, #plugin-description, #author-name, #author-url, #plugin-icon-url, #plugin-menu-icon-url').closest('.field').hide().find('input').prop('disabled', true).removeAttr('required');
          message.text(secninja_mainwp.texts.warningDisable).css("background-color", "#ffe6e6").css("color", "#cc0000").show();
      } else { // Enable
          jQuery('#plugin-name, #plugin-description, #author-name, #author-url, #plugin-icon-url, #plugin-menu-icon-url').closest('.field').show().find('input').prop('disabled', false).attr('required', 'required');
          message.hide();
      }
  }).trigger('change'); // Trigger change to set initial state

  function createInputField(id, label, placeholder) {
      return jQuery('<div class="ui grid field"><label class="six wide column middle aligned">' + label + ':</label><div class="ten wide column"><input type="text" id="' + id + '" name="' + id + '" placeholder="' + placeholder + '" required></div></div>');
  }

  function validateForm() {
      var isValid = true;
      jQuery('input[required]').each(function() {
          if (!jQuery(this).val().trim()) {
              isValid = false;
              message.text(secninja_mainwp.texts.pleaseFillInAllRequiredFields).css("background-color", "#ffe6e6").css("color", "#cc0000").show();
              return false;
          }
      });
      return isValid;
  }

  function sendToSelectedSites() {
      var whiteLabelSettings = {
          wl_active: jQuery('input[name="wl_active"]:checked').val(),
          wl_newname: jQuery('#wl_newname').val(),
          wl_newdesc: jQuery('#wl_newdesc').val(),
          wl_newauthor: jQuery('#wl_newauthor').val(),
          wl_newurl: jQuery('#wl_newurl').val(),
          wl_newiconurl: jQuery('#wl_newiconurl').val(),
          wl_newmenuiconurl: jQuery('#wl_newmenuiconurl').val()
      };

      var selectedIds = jQuery.map(jQuery('#mainwp-manage-sites-body-table .check-column INPUT:checkbox:checked'), function (el) {
        return parseInt(jQuery(el).val(), 10);
    });
      jQuery.ajax({
          type: 'POST',
          url: ajaxurl,
          data: {
              '_ajax_nonce': secninja_mainwp.nonce_secnin,
              'action': 'secnin_run_update_white_label_module',
              'site_ids': selectedIds,
              'white_label_settings': whiteLabelSettings
          },
          dataType: "json",
          success: function (response) {
              console.log(response);
              popup.modal('hide');
              message.text(secninja_mainwp.texts.settingsUpdatedSuccessfully).css("background-color", "#e6ffe6").css("color", "#00cc00").show();
          },
          error: function (response) {
              console.log(response);
              message.text(secninja_mainwp.texts.anErrorOccurred).css("background-color", "#ffe6e6").css("color", "#cc0000").show();
          }
      });
  }
}














jQuery(document).ready(function () {

  jQuery(document).on('click', '#mainwp-do-sites-bulk-actions', function () {
    // console.log('mainwp-do-sites-bulk-actions');
    var action = jQuery("#mainwp-sites-bulk-actions-menu").dropdown("get value");
    if (action == '')
      return false;




    // *** Run security tests
    if (action == 'secnin_runtests' || action == '...') {
      if (mainwpVars.bulkManageSitesTaskRunning) {
        return;
      }

      var confirmMsg = secninja_mainwp.texts.runAllSecurityTests;

      mainwp_confirm(confirmMsg, _callback = function () {

        var selectedIds = jQuery.map(jQuery('#mainwp-manage-sites-body-table .check-column INPUT:checkbox:checked'), function (el) {
          return parseInt(jQuery(el).val(), 10);
        });


        jQuery.ajax({
          type: 'POST',
          url: ajaxurl,
          data: {
            '_ajax_nonce': secninja_mainwp.nonce_secnin,
            'action': 'secnin_run_remote_security_tests',
            'site_ids': selectedIds
          },
          dataType: "json",
          success: function (response) {
            console.log(response);

          },
          error: function (response) {
            console.log(response);
          }
        });

      });
      return false; 

    }


    // *** Run malware scan @todo - in plugin?
    if (action == 'secnin_runmalware' || action == '...') {
      if (mainwpVars.bulkManageSitesTaskRunning) {
        return;
      }

      var confirmMsg = secninja_mainwp.texts.runMalwareScansOnSelectedSites; 

      mainwp_confirm(confirmMsg, _callback = function () {

        var selectedIds = jQuery.map(jQuery('#mainwp-manage-sites-body-table .check-column INPUT:checkbox:checked'), function (el) {
          return parseInt(jQuery(el).val(), 10);
        });



        jQuery.ajax({
          type: 'POST',
          url: ajaxurl,
          data: {
            '_ajax_nonce': secninja_mainwp.nonce_secnin,
            'action': 'secnin_run_remote_malwarescan',
            'site_ids': selectedIds
          },
          dataType: "json",
          success: function (response) {
            console.log(response);

          },
          error: function (response) {
            console.log(response);
          }
        });

      });
      return false; 

    }



    

    // *** Configure White Label
if (action == 'secnin_update_white_label' || action == '...') {
  if (mainwpVars.bulkManageSitesTaskRunning) {
    return;
  }

  if (action == 'secnin_update_white_label') {
    show_white_label_popup();
    return false;
  }

    var selectedIds = jQuery.map(jQuery('#mainwp-manage-sites-body-table .check-column INPUT:checkbox:checked'), function (el) {
      return parseInt(jQuery(el).val(), 10);
    });

    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
          '_ajax_nonce': secninja_mainwp.nonce_secnin,
          'action': 'secnin_run_update_white_label_module',
          'site_ids': selectedIds,
          'white_label_settings': whiteLabelSettings
      },
      dataType: "json",
      success: function (response) {
          console.log(response);
          jQuery('#mainwp-popup').remove();
      },
      error: function (response) {
          console.log(response);
      }
    });


  return false;
}








  });

  

  // Collapsible sections
  jQuery('#mainwp-site-mode-wrap .collapsible').click(function () {
    jQuery(this).next('.section-content').slideToggle('fast');
  });

  jQuery('#security-ninja-events').DataTable({

    "processing": true,
    "serverSide": true,
    "autoWidth": true,
    "stateSave": true,
    "search": true,
    "ajax": {
      "url": ajaxurl,
      "type": "POST",
      "data": function (d) {
        return jQuery.extend({}, d, {
          'action': 'secnin_get_latest_events',
          '_ajax_nonce': secninja_mainwp.nonce_secnin,
        });
      },
      "dataSrc": function (json) {
        return json.data;
      }
    },
    "order": [[0, 'desc']],
    "select": {
      style: 'multi'
    },
    "columns": [
      { "data": "timestamp" },
      { "data": "site_id" },
      { "data": "ip" },
      // { "data": "user_agent" },
      { "data": "action" },
      { "data": "description" }
    ],
    "pageLength": 25,
    "rowCallback": function (row, data) {
      jQuery(row).addClass('wp-list-table widefat fixed striped table-view-list');
    },
    "pagingType": "full_numbers",
    "scrollX": true,
    "lengthMenu": [[10, 25, 50, 100, 250, 500], [10, 25, 50, 100, 250, 500]],
    "language": {
      "emptyTable": secninja_mainwp.texts.no_events,
  },

  });


});


