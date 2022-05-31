(function ($, Drupal) {
  Drupal.behaviors.profileTabs = {
    attach: function attach(context, settings) {
      $('.dashboard-header-buttons').once('dashboardHeaderButtons').each(function(context){
        if ($(this).length > 0) {
          $('.profile--view-mode--dashboard-header > .layout--twocol-section > .layout__region--second').append($('.dashboard-header-buttons'));
        }
      });

      $('.view-header-buttons').once('viewHeaderButtons').each(function(context){
        $('.view-header-buttons').slice(1).remove();

        if ($(this).length > 0) {
          $('.view-header .views-field-nothing > .field-content').append($('.view-header-buttons'));
        }
      });

      $('.parent-tabs').once('parentTabs').each(function() {

        if ($(this).length > 0) {
          let profileTabs = $('<div/>', {
            id: 'profile--tabs'
          });

          $(this).prepend(profileTabs);

          $(this).find('#profile--tabs').prepend($(this).find('> .layout__region:first > .block > h2'));

          if ($(context).hasClass('view-display-id-page_1')) {
            $(this).find('#profile--tabs > h2:last').addClass('selected');
            $(this).find('> .layout__region > .block:last').addClass('selected');
          } else {
            $(this).find('#profile--tabs > h2:first').addClass('selected');
            $(this).find('> .layout__region > .block:first').addClass('selected');
          }

          $(this).find('#profile--tabs > h2').click(function (event) {
            $(this).closest('#profile--tabs').find(' > h2').removeClass('selected');
            $(this).addClass('selected');

            $(this).closest('.parent-tabs').find(' > .layout__region > .block').removeClass('selected');

            $(this).closest('.parent-tabs').find(' > .layout__region > .block').eq($(this).index()).addClass('selected');
          });
        }
      });

		// if ($('.view-profiles .view-content').length > 0) {
		// 	let spaceValue = 50;
		// 	// console.log();
		// 	let tabContentHeight = $('.view-profiles .block-entity-viewprofile:first > .profile').height();
		// 	$('.view-profiles .block-entity-viewprofile:first').css('padding-bottom', tabContentHeight + 15 + spaceValue + 'px');

		// 	$('.view-profiles .block-entity-viewprofile > h2').on('click', function(event) {
		// 		event.preventDefault();

		// 		$('.view-profiles .block-entity-viewprofile').removeClass('selected');
		// 		$('.view-profiles .block-entity-viewprofile').css('padding-bottom', 'unset');

		// 		$(this).parent().addClass('selected');

		// 		// 13 - border
		// 		tabContentHeight = $(this).parent().find('> .profile').height();
		// 		$(this).parent().css('padding-bottom', tabContentHeight + 13 + spaceValue + 'px');

		// 	});
		// }

    }
  };
})(jQuery, Drupal);
