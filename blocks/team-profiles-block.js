(function (blocks, element, blockEditor, components) {
    var el = element.createElement;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var TextControl = components.TextControl;
    var SelectControl = components.SelectControl;
    var ServerSideRender = components.ServerSideRender;

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
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            return [
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
                el(ServerSideRender, {
                    block: 'lf-team-profiles/team-profiles',
                    attributes: attributes
                })
            ];
        },

        save: function () {
            return null; // Server-side render
        }
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components
);
