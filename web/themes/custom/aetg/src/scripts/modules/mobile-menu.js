(function ($, Drupal) {
  Drupal.behaviors.desktopMenu= {
    attach: function (context, settings) {
      if ($(window).width() < 1049) {
        $('#block-aetg-account-menu .user-icon').on('click', function (e) {
          $('#block-aetg-account-menu').toggleClass('opened');
        });

        $(document).mouseup( function(e) {
          const accountMenu = $('#block-aetg-account-menu');
          if ( !accountMenu.is(e.target) && accountMenu.has(e.target).length === 0 ) {
            accountMenu.removeClass('opened');
          }
        });
      }

      $('.mobile-menu', context).once().on('click', function () {
        $('header').toggleClass('open');
        $('.mobile-menu').toggleClass('open');
        $('html, body').animate({ scrollTop: 0 }, 200);
        $('header.open .region-header #block-aetg-main-menu > .menu').animate(
          { scrollTop: 0 },
          200,
        );
        $('body').toggleClass('no-scroll');

        // if ($(window).width() < 1049 && $('.menu--main > ul > .custom').length == 0) {
        //   $('.menu--main > ul').append( '<li class="menu-item custom"></li>' );
        //   $('.menu--main > ul > .custom').append( $('.menu--account') );
        // }

        if ($('.user-has-toolbar').length > 0) {
          $(
            '.user-has-toolbar header.open .region-header #block-aetg-main-menu > .menu',
          ).css('top', $('header').height() + $('#toolbar-bar').height() + 'px');

          if (
            $('body').hasClass('toolbar-horizontal') &&
            $('body').hasClass('toolbar-tray-open')
          ) {
            let bothToolbarsHeight = $('#toolbar-bar').height();
            bothToolbarsHeight += $('.toolbar-tray-horizontal').height();

            $(
              '.user-has-toolbar header.open .region-header #block-aetg-main-menu > .menu',
            ).css('top', $('header').height() + bothToolbarsHeight + 'px');
          }
        } else {
          $(
            'header.open .region-header #block-aetg-main-menu > .menu',
          ).css('top', $('header').height());
        }          
      });
    }
  };
})(jQuery, Drupal);
