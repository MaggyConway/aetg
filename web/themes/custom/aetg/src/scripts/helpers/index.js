const select = function select(elem) {
  return document.querySelector(elem);
};

const selectAll = function selectAll(elem) {
  return Array.from(document.querySelectorAll(elem));
};

export { select, selectAll };
