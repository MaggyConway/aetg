import polyfills from './polyfills';
import './modules/mobile-menu';
import './modules/defaultImage';
import './modules/fix-layout';
import './modules/mobile-submenu';


(() => {
  polyfills();
})();


// You can use dynamic import if you want
// For example
//
// document.addEventListener('click', async () => {
//   const { selectAll } = await import('./helpers')
//   console.log(selectAll('a'))
// })
