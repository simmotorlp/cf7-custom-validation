jQuery(document).ready(function($) {
  // Toggle input availability when the feature is enabled/disabled.
  $('.cf7cv-enable-toggle input').on('change', function() {
    var $table = $('.cf7cv-fields-table');
    if ($(this).is(':checked')) {
      $table.removeClass('cf7cv-disabled');
      $table.find('input').prop('disabled', false);
    } else {
      $table.addClass('cf7cv-disabled');
      $table.find('input').prop('disabled', true);
    }
  });

  // Initialize state on load.
  $('.cf7cv-enable-toggle input').trigger('change');
});
