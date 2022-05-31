/* eslint-disable */
import smoothscroll from 'smoothscroll-polyfill';

export default function polyfills() {
  smoothscroll.polyfill();

    //
    // matches
    //
    (function (e) {
      var matches = e.matches || e.matchesSelector || e.webkitMatchesSelector || e.mozMatchesSelector || e.msMatchesSelector || e.oMatchesSelector;
      !matches ? (e.matches = e.matchesSelector = function matches(selector) {
        var matches = document.querySelectorAll(selector);
        var th = this;
        return Array.prototype.some.call(matches, function (e) {
          return e === th;
        });
      }) : (e.matches = e.matchesSelector = matches);
    })(Element.prototype);

    //
    // closest
    //
    (function (ELEMENT) {
      ELEMENT.matches = ELEMENT.matches || ELEMENT.mozMatchesSelector || ELEMENT.msMatchesSelector || ELEMENT.oMatchesSelector || ELEMENT.webkitMatchesSelector;
      ELEMENT.closest = ELEMENT.closest || function closest(selector) {
        if (!this) return null;
        if (this.matches(selector)) return this;
        if (!this.parentElement) { return null }
        else return this.parentElement.closest(selector)
      };
    }(Element.prototype));
}
