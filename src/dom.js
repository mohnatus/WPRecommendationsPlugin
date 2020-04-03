export function text(text) {
  return document.createTextNode(text);
}
export function node(tag, attrs, children = []) {
  const el = document.createElement(tag);
  if (attrs) {
    Object.keys(attrs).forEach((name) => {
      el.setAttribute(name, attrs[name]);
    });
  }
  if (children) {
    if (!Array.isArray(children)) {
      children = [children];
    }
    children.forEach((node) => {
      el.appendChild(node);
    });
  }
  return el;
}
export function find(selector, parent = document.body) {
  return parent.querySelector(selector);
}
export function findAll(selector, parent = document.body) {
  return parent.querySelectorAll(selector);
}
export function prepend(node, target = document.body) {
  if (typeof node === 'string') node = text(node);
  target.insertAdjacentElement('afterbegin', node);
}
export function append(node, target = document.body) {
  if (typeof node === 'string') node = text(node);
  target.insertAdjacentElement('beforeend', node);
}
export function onEvent(eventName, el, cb) {
  el.addEventListener(eventName, cb);
  return () => {
    el.removeEventListener(eventName, cb);
  };
}
export function onClick(el, cb) {
  return onEvent('click', el, cb);
}
export function onChange(el, cb) {
  return onEvent('change', el, cb);
}
