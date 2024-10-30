(function ($) {

  var LTTC = {

    /**
     * Bind change event to the bulk actions select
     */
    init: function () {
      if (typeof lttc_data !== 'object') {
        return;
      }

      for (var taxonomy_name in lttc_data) {
        if (!lttc_data.hasOwnProperty(taxonomy_name)) {
          continue;
        }

        var options = lttc_data[taxonomy_name];
        var $instance = $('select[name="' + options.name + '"]');

        if ($instance.length) {
          $instance.select2(options.select2_options);
        }
      }
    }

  };

  $(document).ready(function () {
    LTTC.init();
  });

})(jQuery);
