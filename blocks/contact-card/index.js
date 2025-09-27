( function(wp){
  const { registerBlockType } = wp.blocks;
  const { InspectorControls } = wp.blockEditor;
  const { PanelBody, TextControl, SelectControl } = wp.components;

  const textFields = ['name','title','org','phone','email','website','address','whatsapp','telegram','imo','skype','qrtext','button'];

  registerBlockType('smartcc/contact-card', {
    edit: (props) => {
      const { attributes, setAttributes } = props;
      return (
        wp.element.createElement('div', {},
          wp.element.createElement(InspectorControls, {},
            wp.element.createElement(PanelBody, { title: 'Settings' },
              [
                wp.element.createElement(SelectControl, {
                  key: 'layout',
                  label: 'Layout',
                  value: attributes.layout,
                  options: [{label:'Card', value:'card'}, {label:'Inline', value:'inline'}],
                  onChange: (v)=> setAttributes({ layout: v })
                }),
                wp.element.createElement(SelectControl, {
                  key: 'qr',
                  label: 'QR Mode',
                  value: attributes.qr,
                  options: [{label:'Inline vCard', value:'inline_vcard'}, {label:'Custom Payload', value:'custom'}],
                  onChange: (v)=> setAttributes({ qr: v })
                }),
                ...textFields.map((key) =>
                  wp.element.createElement(TextControl, {
                    key, label: key, value: attributes[key] || '',
                    onChange: (v)=> setAttributes({ [key]: v })
                  })
                )
              ]
            )
          ),
          wp.element.createElement('p', {}, 'SmartCC Contact Card (preview on front-end)')
        )
      );
    },
    save: () => null
  });
})(window.wp);
