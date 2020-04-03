import * as dom from './dom.js';

function getValue(link, title) {
  return JSON.stringify({ link, title });
}

function createSnippet(link, title) {
  const linkInput = dom.node('input', {
    type: 'text',
    value: link,
  });
  const titleInput = dom.node('input', {
    type: 'text',
    value: title,
  });
  const hiddenInput = dom.node('input', {
    type: 'hidden',
    name: 'recommendations[]',
    value: getValue(link, title),
  });
  const removeButton = dom.node(
    'button',
    { type: 'button', class: 'button' },
    dom.text('Удалить'),
  );

  const item = dom.node('div', { class: 'recommendations-group' }, [
    dom.node('div', { class: 'recommendations-field' }, linkInput),
    dom.node('div', { class: 'recommendations-field' }, titleInput),
    dom.node(
      'div',
      { class: 'recommendations-field' },
      removeButton,
    ),
    hiddenInput,
  ]);

  function update() {
    hiddenInput.value = getValue(linkInput.value, titleInput.value);
  }

  const listeners = [
    dom.onChange(linkInput, update),
    dom.onChange(titleInput, update),
    dom.onClick(removeButton, remove),
  ];

  function remove() {
    listeners.forEach((l) => l());
    item.remove();
  }

  return item;
}

(function () {
  const box = dom.find('#recommendations-box');
  const list = dom.find('#recommendations-list', box);

  const form = dom.find('#recommendations-form', box);
  const link = dom.find('#recommendation-link-add', form);
  const title = dom.find('#recommendation-title-add', form);
  const button = dom.find('#recommendation-add', form);

  dom.onClick(button, () => {
    if (!link.value) return;

    const snippet = createSnippet(link.value, title.value);
    dom.append(snippet, list);
    link.value = '';
    title.value = '';
  });

  (window.__postRecommendations || []).forEach((data) => {
    const { link, title } = JSON.parse(data);
    const snippet = createSnippet(link, title);
    list.append(snippet);
  });
})();
