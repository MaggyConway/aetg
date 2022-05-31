(function($, Drupal) {
  Drupal.behaviors.fixLayout = {
    attach: function(context, settings) {
      if ($('table').length > 0) {
        if (
          !$('table')
            .parent()
            .hasClass('table-container')
        ) {
          $('table')
            .parent()
            .addClass('table-container');
        }
      }

      setTimeout(function() {
        $('body').css('padding-top', $('#toolbar-bar').height());

        if ($('body').hasClass('toolbar-horizontal') && $('body').hasClass('toolbar-tray-open')) {
          let bothToolbarsHeight = $('#toolbar-bar').height();
          bothToolbarsHeight += $('.toolbar-tray-horizontal').height();

          $('body').css('padding-top', bothToolbarsHeight);
        }
      }, 500);

      if ($('.dashboard-header-buttons').length > 0 && $(window).width() < 481) {
        $('.profile--view-mode--dashboard-header').css(
          'padding-bottom',
          $('.dashboard-header-buttons').height() + 20 + 'px',
        );
      }

      if ($('.form-type-radio').length > 0) {
        $('.form-type-radio input').on('change', function(event) {
          $('.form-type-radio').removeClass('active');
          $(this)
            .parent()
            .addClass('active');
        });
      }

      if ($('.form-type-checkbox').find('.description').length > 0) {

        $('.form-type-checkbox > input:checked').parent().addClass('active');

        $('.form-type-checkbox').find('.description').once().on('click', function(e) {
            $(this).parent().toggleClass('active');

            // console.log($(this));

            $(this).prev('input').prop('checked') == false
              ? $(this).prev('input').prop('checked', true)
              : $(this).prev('input').prop('checked', false);
          });
      } else if ($('.form-type-checkbox').find('.option').length > 0) {
        $('.form-type-checkbox').find('.option').once().on('click', function(e) {
            $(this).prev('input').attr('checked') === false 
                  ? $(this).prev('input').attr('checked', true) 
                  : $(this).prev('input').attr('checked', false);
          });
      }

      if ($('.field-multiple-table .delta-order').length > 0) {
        $('.field-multiple-table')
          .find('.delta-order')
          .attr('colspan', '2');
      }

      if ($('.documents-parent > .layout__region > .block').length == 0) {
        $('.documents-parent').hide();
      }

      if (location.pathname == '/contacto') {
        $('.block-system-breadcrumb-block').addClass('bcrumbs_on_contacto');
      }

      $('#sliding-popup > button').click(function(e) {
        $('#sliding-popup').toggleClass('opened');
      });

      if (location.pathname == '/hazte-socio/confirmation') {
        $('.block-system-breadcrumb-block').css('margin-top', 0);
      }
    },
  };
})(jQuery, Drupal);
