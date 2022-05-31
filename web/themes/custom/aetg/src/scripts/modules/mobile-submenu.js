(function($, Drupal) {
  Drupal.behaviors.mobileSubmenu = {
    attach: function(context, settings) {
      if ($(window).width() < 1049) {
        $('.menu-item--expanded', context)
          .once()
          .on('click', function(e) {
            $('.menu-item--expanded')
              .not($(this))
              .removeClass('show');
            $(this).toggleClass('show');
          });
      }
    },
  };
})(jQuery, Drupal);
