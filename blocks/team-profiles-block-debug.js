(function (blocks, element, blockEditor, components, serverSideRender) {
    console.log('LF Team Profiles Block: Script loaded');
    
    // Check if all dependencies are available
    if (!blocks) console.error('wp.blocks not available');
    if (!element) console.error('wp.element not available');
    if (!blockEditor && !window.wp.editor) console.error('wp.blockEditor/wp.editor not available');
    if (!components) console.error('wp.components not available');
    if (!serverSideRender) console.error('wp.serverSideRender not available');
    
    var el = element.createElement;
    var InspectorControls = (blockEditor && blockEditor.InspectorControls) || (window.wp.editor && window.wp.editor.InspectorControls);
    var PanelBody = components.PanelBody;
    var TextControl = components.TextControl;
    var SelectControl = components.SelectControl;
    var ServerSideRender = serverSideRender;
    var Fragment = element.Fragment;
    
    // Check if components are available
    if (!InspectorControls) console.error('InspectorControls not available');
    if (!PanelBody) console.error('PanelBody not available');
    if (!TextControl) console.error('TextControl not available');
    if (!SelectControl) console.error('SelectControl not available');
    if (!ServerSideRender) console.error('ServerSideRender not available');
    if (!Fragment) console.error('Fragment not available');

    try {
        blocks.registerBlockType('lf-team-profiles/team-profiles', {
            title: 'Team Profiles',
            icon: 'groups',
            category: 'widgets',
            attributes: {
                department: {
                    type: 'string',
                    default: ''
                },
                columns: {
                    type: 'string',
                    default: '4'
                },
                orderby: {
                    type: 'string',
                    default: 'menu_order'
                },
                order: {
                    type: 'string',
                    default: 'ASC'
                }
            },

            edit: function (props) {
                console.log('LF Team Profiles Block: Edit function called', props);
                
                var attributes = props.attributes;
                var setAttributes = props.setAttributes;

                try {
                    return el(Fragment, {},
                        el(InspectorControls, {},
                            el(PanelBody, { title: 'Team Profiles Settings', initialOpen: true },
                                el(TextControl, {
                                    label: 'Department',
                                    help: 'Filter by department slug(s), comma-separated',
                                    value: attributes.department,
                                    onChange: function (value) {
                                        setAttributes({ department: value });
                                    }
                                }),
                                el(SelectControl, {
                                    label: 'Columns',
                                    value: attributes.columns,
                                    options: [
                                        { label: '2 Columns', value: '2' },
                                        { label: '3 Columns', value: '3' },
                                        { label: '4 Columns', value: '4' },
                                        { label: '5 Columns', value: '5' },
                                        { label: '6 Columns', value: '6' }
                                    ],
                                    onChange: function (value) {
                                        setAttributes({ columns: value });
                                    }
                                }),
                                el(SelectControl, {
                                    label: 'Order By',
                                    value: attributes.orderby,
                                    options: [
                                        { label: 'Menu Order', value: 'menu_order' },
                                        { label: 'Title', value: 'title' },
                                        { label: 'Date', value: 'date' },
                                        { label: 'Priority', value: 'meta_value_num' }
                                    ],
                                    onChange: function (value) {
                                        setAttributes({ orderby: value });
                                    }
                                }),
                                el(SelectControl, {
                                    label: 'Order',
                                    value: attributes.order,
                                    options: [
                                        { label: 'Ascending', value: 'ASC' },
                                        { label: 'Descending', value: 'DESC' }
                                    ],
                                    onChange: function (value) {
                                        setAttributes({ order: value });
                                    }
                                })
                            )
                        ),
                        el('div', { className: 'lf-team-profiles-block-editor' },
                            ServerSideRender ? el(ServerSideRender, {
                                block: 'lf-team-profiles/team-profiles',
                                attributes: attributes
                            }) : el('p', {}, 'ServerSideRender component not available')
                        )
                    );
                } catch (error) {
                    console.error('LF Team Profiles Block: Error in edit function', error);
                    return el('div', { className: 'error' }, 'Error rendering block: ' + error.message);
                }
            },

            save: function () {
                return null; // Server-side render
            }
        });
        
        console.log('LF Team Profiles Block: Registration complete');
    } catch (error) {
        console.error('LF Team Profiles Block: Registration failed', error);
    }
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor || window.wp.editor,
    window.wp.components,
    window.wp.serverSideRender
);
