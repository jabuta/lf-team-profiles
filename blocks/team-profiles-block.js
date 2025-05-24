(function() {
    'use strict';
    
    // Wait for wp object to be available
    if (typeof wp === 'undefined') {
        console.error('LF Team Profiles Block: WordPress scripts not loaded');
        return;
    }
    
    // Get dependencies with fallbacks
    var blocks = wp.blocks;
    var element = wp.element;
    var blockEditor = wp.blockEditor || wp.editor;
    var components = wp.components;
    var serverSideRender = wp.serverSideRender;
    var i18n = wp.i18n;
    
    // Check critical dependencies
    if (!blocks || !element || !components) {
        console.error('LF Team Profiles Block: Missing critical dependencies');
        return;
    }
    
    var el = element.createElement;
    var Fragment = element.Fragment;
    var __ = i18n ? i18n.__ : function(text) { return text; };
    
    // Get components with fallbacks
    var InspectorControls = blockEditor ? blockEditor.InspectorControls : null;
    var PanelBody = components.PanelBody;
    var TextControl = components.TextControl;
    var SelectControl = components.SelectControl;
    var ServerSideRender = serverSideRender ? serverSideRender.default || serverSideRender : null;
    var Placeholder = components.Placeholder;
    var Spinner = components.Spinner;
    
    // Register the block
    blocks.registerBlockType('lf-team-profiles/team-profiles', {
        title: __('Team Profiles', 'lf-team-profiles'),
        description: __('Display team members with department filtering', 'lf-team-profiles'),
        icon: 'groups',
        category: 'widgets',
        keywords: ['team', 'profiles', 'members', 'staff'],
        supports: {
            align: ['wide', 'full'],
            html: false
        },
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

        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            
            // Settings panel
            var inspectorControls = InspectorControls ? el(
                InspectorControls,
                {},
                el(
                    PanelBody,
                    { 
                        title: __('Team Profiles Settings', 'lf-team-profiles'),
                        initialOpen: true 
                    },
                    el(TextControl, {
                        label: __('Department', 'lf-team-profiles'),
                        help: __('Filter by department slug(s), comma-separated', 'lf-team-profiles'),
                        value: attributes.department,
                        onChange: function(value) {
                            setAttributes({ department: value });
                        }
                    }),
                    el(SelectControl, {
                        label: __('Columns', 'lf-team-profiles'),
                        value: attributes.columns,
                        options: [
                            { label: __('2 Columns', 'lf-team-profiles'), value: '2' },
                            { label: __('3 Columns', 'lf-team-profiles'), value: '3' },
                            { label: __('4 Columns', 'lf-team-profiles'), value: '4' },
                            { label: __('5 Columns', 'lf-team-profiles'), value: '5' },
                            { label: __('6 Columns', 'lf-team-profiles'), value: '6' }
                        ],
                        onChange: function(value) {
                            setAttributes({ columns: value });
                        }
                    }),
                    el(SelectControl, {
                        label: __('Order By', 'lf-team-profiles'),
                        value: attributes.orderby,
                        options: [
                            { label: __('Menu Order', 'lf-team-profiles'), value: 'menu_order' },
                            { label: __('Title', 'lf-team-profiles'), value: 'title' },
                            { label: __('Date', 'lf-team-profiles'), value: 'date' },
                            { label: __('Priority', 'lf-team-profiles'), value: 'meta_value_num' }
                        ],
                        onChange: function(value) {
                            setAttributes({ orderby: value });
                        }
                    }),
                    el(SelectControl, {
                        label: __('Order', 'lf-team-profiles'),
                        value: attributes.order,
                        options: [
                            { label: __('Ascending', 'lf-team-profiles'), value: 'ASC' },
                            { label: __('Descending', 'lf-team-profiles'), value: 'DESC' }
                        ],
                        onChange: function(value) {
                            setAttributes({ order: value });
                        }
                    })
                )
            ) : null;
            
            // Preview area
            var preview;
            if (ServerSideRender) {
                preview = el(
                    ServerSideRender,
                    {
                        block: 'lf-team-profiles/team-profiles',
                        attributes: attributes,
                        EmptyResponsePlaceholder: function() {
                            return el(
                                Placeholder,
                                {
                                    icon: 'groups',
                                    label: __('Team Profiles', 'lf-team-profiles')
                                },
                                __('No team members found. Add some team members to see the preview.', 'lf-team-profiles')
                            );
                        },
                        ErrorResponsePlaceholder: function() {
                            return el(
                                Placeholder,
                                {
                                    icon: 'warning',
                                    label: __('Team Profiles', 'lf-team-profiles')
                                },
                                __('Error loading preview. Please check your settings.', 'lf-team-profiles')
                            );
                        },
                        LoadingResponsePlaceholder: function() {
                            return el(
                                Placeholder,
                                {
                                    icon: 'groups',
                                    label: __('Team Profiles', 'lf-team-profiles')
                                },
                                el(Spinner)
                            );
                        }
                    }
                );
            } else {
                preview = el(
                    Placeholder,
                    {
                        icon: 'groups',
                        label: __('Team Profiles', 'lf-team-profiles'),
                        instructions: __('Team profiles will be displayed here on the frontend.', 'lf-team-profiles')
                    }
                );
            }
            
            // Return the complete block edit interface
            return el(
                Fragment,
                {},
                inspectorControls,
                el(
                    'div',
                    { className: props.className },
                    preview
                )
            );
        },

        save: function() {
            // Server-side rendering
            return null;
        }
    });
})();
