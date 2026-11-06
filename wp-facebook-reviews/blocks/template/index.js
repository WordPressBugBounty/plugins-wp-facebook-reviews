( function ( wp ) {
	var el = wp.element.createElement;
	var useEffect = wp.element.useEffect;
	var useRef = wp.element.useRef;
	var useState = wp.element.useState;
	var registerBlockType = wp.blocks.registerBlockType;
	var Placeholder = wp.components.Placeholder;
	var ToolbarGroup = wp.components.ToolbarGroup;
	var ToolbarItem = wp.components.ToolbarItem;
	var ToolbarButton = wp.components.ToolbarButton;
	var BlockControls = wp.blockEditor.BlockControls;
	var ServerSideRender = wp.serverSideRender;
	var __ = wp.i18n.__;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var blockName = 'wp-fb-reviews/template';
	var textDomain = 'wp-fb-reviews';
	var blockTitle = 'WP Review Slider';

	function templateState( templates, templateId ) {
		var options = [
			{
				label: __( 'Select a template', textDomain ),
				value: '0'
			}
		];
		var selected = null;
		var i;

		for ( i = 0; i < templates.length; i++ ) {
			if ( String( templates[ i ].id ) === String( templateId ) ) {
				selected = templates[ i ];
			}
			options.push( {
				label: templates[ i ].title,
				value: String( templates[ i ].id )
			} );
		}

		if ( templateId > 0 && ! selected ) {
			options.push( {
				label: __( 'Saved template is no longer available', textDomain ),
				value: String( templateId )
			} );
		}

		return {
			options: options,
			selected: selected
		};
	}

	function editTemplateUrl( editUrl, templateId ) {
		if ( ! editUrl || templateId < 1 ) {
			return '';
		}
		var url = editUrl.replace( /&amp;/g, '&' );
		return url + ( url.indexOf( '?' ) === -1 ? '?' : '&' ) + 'tid=' + encodeURIComponent( String( templateId ) );
	}

	function templateToolbar( templateId, options, onChange, editUrl, onRefresh ) {
		var href = editTemplateUrl( editUrl, templateId );

		return el(
			BlockControls,
			null,
			el(
				ToolbarGroup,
				{ className: 'wprev-free-toolbar-group' },
				el( ToolbarItem, null, function ( itemProps ) {
					var className = 'wprev-free-toolbar-select';
					if ( itemProps.className ) {
						className += ' ' + itemProps.className;
					}

					return el(
						'select',
						Object.assign( {}, itemProps, {
							className: className,
							value: String( templateId || 0 ),
							'aria-label': __( 'Review template', textDomain ),
							onChange: function ( event ) {
								onChange( event.target.value );
							}
						} ),
						options.map( function ( option ) {
							return el(
								'option',
								{
									key: option.value,
									value: option.value
								},
								option.label
							);
						} )
					);
				} ),
				href
					? el(
						ToolbarButton,
						{
							className: 'wprev-free-toolbar-edit',
							onClick: function () {
								window.open( href, '_blank', 'noopener,noreferrer' );
							}
						},
						__( 'Edit Template', textDomain )
					)
					: null,
				templateId > 0
					? el(
						ToolbarButton,
						{
							className: 'wprev-free-toolbar-refresh',
							icon: 'update',
							label: __( 'Refresh template', textDomain ),
							onClick: onRefresh
						},
						__( 'Refresh', textDomain )
					)
					: null
			)
		);
	}

	function ReviewPreview( props ) {
		var ref = useRef( null );
		var queryRef = useRef( null );

		if ( ! queryRef.current || queryRef.current.wprev_refresh !== props.previewKey ) {
			queryRef.current = { wprev_refresh: props.previewKey };
		}

		useEffect( function () {
			var node = ref.current;
			if ( ! node ) {
				return;
			}
			var win = node.ownerDocument.defaultView || window;

			function init() {
				if ( win.wprevInitReviewSliders ) {
					win.wprevInitReviewSliders( node );
				}
			}

			var observer = new win.MutationObserver( init );
			observer.observe( node, { childList: true, subtree: true } );
			init();

			return function () {
				observer.disconnect();
			};
		}, [] );

		return el(
			'div',
			{ ref: ref, className: 'wprev-free-block-preview' },
			el( ServerSideRender, {
				block: blockName,
				attributes: props.attributes,
				urlQueryArgs: queryRef.current,
				EmptyResponsePlaceholder: function () {
					return el(
						'p',
						{ className: 'wprev-free-block-summary' },
						__( 'This template has no reviews to display. Check its source filter on the Templates page.', textDomain )
					);
				}
			} )
		);
	}

	registerBlockType( blockName, {
		edit: function ( props ) {
			var blockProps = useBlockProps();
			var config = window.wprevFbReviewsBlock || { templates: [], templatesUrl: '' };
			var templates = config.templates || [];
			var templateId = props.attributes.templateId || 0;
			var previewState = useState( 0 );
			var previewKey = previewState[ 0 ];
			var setPreviewKey = previewState[ 1 ];
			var state = templateState( templates, templateId );
			var onChange = function ( value ) {
				props.setAttributes( { templateId: parseInt( value, 10 ) || 0 } );
			};
			var refreshPreview = function () {
				setPreviewKey( function ( key ) {
					return key + 1;
				} );
			};

			var canvas;
			if ( ! templates.length ) {
				canvas = el(
					Placeholder,
					{
						icon: 'star-filled',
						label: __( blockTitle, textDomain ),
						instructions: __( 'Create a review template first, then choose it from the block toolbar. Refresh this page after you create the template.', textDomain )
					},
					config.templatesUrl
						? el(
							'a',
							{
								href: config.templatesUrl,
								target: '_blank',
								rel: 'noopener noreferrer'
							},
							__( 'Open Templates', textDomain )
						)
						: null
				);
			} else if ( templateId < 1 ) {
				canvas = el(
					Placeholder,
					{
						icon: 'star-filled',
						label: __( blockTitle, textDomain ),
						instructions: __( 'Choose a review template from the block toolbar.', textDomain )
					}
				);
			} else {
				canvas = el( ReviewPreview, {
					attributes: props.attributes,
					previewKey: previewKey
				} );
			}

			return el(
				'div',
				blockProps,
				templates.length ? templateToolbar( templateId, state.options, onChange, config.editUrl || '', refreshPreview ) : null,
				canvas
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp );
