import { Plugin } from '@ckeditor/ckeditor5-core';
import { ButtonView, createDropdown, addToolbarToDropdown } from '@ckeditor/ckeditor5-ui';

// Toolbar dropdown "t3sVars": inserts {{key}} placeholders (items come from EXT:t3s_vars listener)
export class T3sVars extends Plugin {
  static get pluginName() {
    return 'T3sVars';
  }

  init() {
    const editor = this.editor;
    const config = editor.config.get('t3sVars') || {};
    const items = config.items || [];

    editor.ui.componentFactory.add('t3sVars', (locale) => {
      const dropdown = createDropdown(locale);
      dropdown.buttonView.set({
        label: config.label || 'Variables',
        tooltip: config.tooltip || 'Insert variable',
        withText: true,
      });
      dropdown.bind('isEnabled').to(editor, 'isReadOnly', (isReadOnly) => !isReadOnly && items.length > 0);

      const buttons = items.map((item) => {
        const button = new ButtonView(locale);
        button.set({ label: item.label, withText: true });
        button.on('execute', () => {
          editor.model.change((writer) => {
            // keep current text attributes (bold, italic, ...)
            const attributes = editor.model.document.selection.getAttributes();
            editor.model.insertContent(writer.createText('{{' + item.key + '}}', attributes));
          });
          editor.editing.view.focus();
        });
        return button;
      });

      addToolbarToDropdown(dropdown, buttons, { isVertical: true });
      return dropdown;
    });
  }
}
