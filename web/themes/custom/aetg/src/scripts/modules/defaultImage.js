/**
 * @file
 * Default Image behaviors.
 */

(function($, window, Drupal) {
  'use strict';

  /**
   * Add a placeholder if image space is empty.
   */
  Drupal.behaviors.addImagePlaceholder = {
    attach: function(context) {
      addImagePlaceholder('.page-node-type-recurso .node--view-mode-full .node__content > .layout--onecol:first-child .layout__region--content', '.block-field-blocknoderecursofield-media');
      addImagePlaceholder('.node--type-recurso.node--view-mode-teaser .node__content > .layout--onecol:first-child .layout__region--content', '.block-field-blocknoderecursofield-media', true);

      addImagePlaceholder('.page-node-type-news .node--view-mode-header .node__content > .layout--onecol:first-child > .layout__region--content', '.block-field-blocknodenewsfield-media');
      addImagePlaceholder('.node--type-news.node--view-mode-teaser .node__content > .layout--onecol:first-child > .layout__region--content', '.block-field-blocknodenewsfield-media', true);

      /**
       *
       * @param container
       * @param imageSelector
       * @param need_linked
       */
      function addImagePlaceholder(container, imageSelector, need_linked) {
        $(container).once('addImagePlaceholder').each(function() {
          if ($(this).find(imageSelector).length === 0) {
            let node = $(this).closest('article');
            if (node.length && node.is('[about]') && need_linked) {
              let href = node.attr('about');
              $(this).append('<a href="' + href + '"><div class="default-image-placeholder"></div></a>');
            }
            else {
              $(this).append('<div class="default-image-placeholder"></div>');
            }
          }
        });
      }
    }
  };
})(jQuery, window, Drupal);
