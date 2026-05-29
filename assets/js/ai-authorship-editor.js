/**
 * AI Authorship Gutenberg Editor Panel
 *
 * Uses @wordpress/components globals (no build step).
 * Icons by Heroicons, licensed under MIT (https://heroicons.com).
 *
 * @package mrmurphy-theme
 */

( function ( wp ) {
	var element = wp.element;
	var blockEditor = wp.blockEditor;
	var blocks = wp.blocks;
	var components = wp.components;
	var compose = wp.compose;
	var i18n = wp.i18n;
	var data = wp.data;

	var __ = i18n.__;
	var createInterpolateElement = element.createInterpolateElement;
	var createElement = element.createElement;
	var Fragment = element.Fragment;
	var useState = element.useState;
	var useEffect = element.useEffect;
	var useMemo = element.useMemo;

	var PanelBody = components.PanelBody;
	var Button = components.Button;
	var SelectControl = components.SelectControl;
	var TextControl = components.TextControl;
	var IconButton = components.IconButton;

	/**
	 * Heroicon SVG paths (subset of v2.1.5, MIT licensed).
	 */
	var heroicons = {
		'user': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 7.5 17.998A17.933 17.933 0 0 1 4.501 20.118Z"/>',
		'users': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.96 6.96 0 0 0 4.501 16.5m13.5-3a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.75 12a3 3 0 1 0-6 0 3 3 0 0 0 6 0Z"/>',
		'cpu-chip': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9v9h-9v-9Z"/>',
		'sparkles': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z"/>',
		'light-bulb': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18v-1.5m0 0a7.5 7.5 0 1 0-3-5.85m3 5.85V12m-6.375 0h12.75m-12.75 3h12.75M12 3v1.5"/>',
		'server': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 7.5a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 7.5v9a2.25 2.25 0 0 0 2.25 2.25h13.5A2.25 2.25 0 0 0 21 16.5v-9ZM4.5 3v18m16.5-18v18"/>',
		'plus': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>',
		'trash': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>',
		'question-mark-circle': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 3.758a4.496 4.496 0 0 1 4.242 7.407c-1.056.807-2.59.807-3.646 0a2.248 2.248 0 0 0-3.476.952l-.152.457a1.124 1.124 0 0 0 1.736 1.278l.213-.154a.75.75 0 0 1 .933 1.178l-.213.154a2.625 2.625 0 0 1-4.061-2.993l.152-.457a3.748 3.748 0 0 1 5.772-1.988ZM10.5 15.75a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0Z"/>',
	};

	/**
	 * Render a heroicon as a small SVG.
	 */
	function Heroicon( props ) {
		var name = props.name || 'question-mark-circle';
		var path = heroicons[ name ] || heroicons['question-mark-circle'];
		var color = props.color || 'currentColor';
		var size = props.size || 16;

		return createElement( 'svg', {
			xmlns: 'http://www.w3.org/2000/svg',
			fill: 'none',
			viewBox: '0 0 16 16',
			width: size,
			height: size,
			'aria-hidden': 'true',
			style: { color: color },
			dangerouslySetInnerHTML: { __html: path },
		} );
	}

	/**
	 * Main sidebar panel component.
	 */
	function AuthorshipPanel() {
		var metaKey = mrmurphyAuthorship.meta_key;
		var categories = mrmurphyAuthorship.categories || {};

		var _wp$data = data,
			useSelect = _wp$data.useSelect,
			useDispatch = _wp$data.useDispatch;
		var _useState1 = useState( {} ),
			localData = _useState1[ 0 ],
			setLocalData = _useState1[ 1 ];
		var _useState2 = useState( false ),
			isSaving = _useState2[ 0 ],
			setIsSaving = _useState2[ 1 ];
		var _useState3 = useState( { name: '', link: '' } ),
			newEntry = _useState3[ 0 ],
			setNewEntry = _useState3[ 1 ];
		var _useState4 = useState( '' ),
			selectedCategory = _useState4[ 0 ],
			setSelectedCategory = _useState4[ 1 ];

		// Read meta from block editor store.
		var authorshipMeta = useSelect( function ( select ) {
			var postMeta = select( 'core/editor' ).getEditPost();
			if ( ! postMeta || ! postMeta.meta ) {
				return '{}';
			}
			return postMeta.meta[ metaKey ] || '{}';
		}, [ metaKey ] );

		var editPost = useDispatch( 'core/editor' ).editPost;

		// Initialize local data from post meta.
		useEffect( function () {
			try {
				var parsed = JSON.parse( authorshipMeta );
				if ( typeof parsed !== 'object' || parsed === null || Array.isArray( parsed ) ) {
					parsed = {};
				}
				setLocalData( parsed );
			} catch ( e ) {
				setLocalData( {} );
			}
		}, [ authorshipMeta ] );

		// Get counts.
		var counts = useMemo( function () {
			var c = {};
			Object.keys( localData ).forEach( function ( key ) {
				if ( Array.isArray( localData[ key ] ) ) {
					c[ key ] = localData[ key ].length;
				}
			} );
			return c;
		}, [ localData ] );

		var total = useMemo( function () {
			return Object.values( counts ).reduce( function ( sum, n ) { return sum + n; }, 0 );
		}, [ counts ] );

		// Auto-select first category with data, or first available.
		useEffect( function () {
			if ( ! selectedCategory ) {
				var firstWithEntries = Object.keys( counts ).find( function ( k ) { return counts[ k ] > 0; } );
				if ( firstWithEntries ) {
					setSelectedCategory( firstWithEntries );
				} else {
					var firstCat = Object.keys( categories )[ 0 ];
					if ( firstCat ) {
						setSelectedCategory( firstCat );
					}
				}
			}
		}, [ categories, counts ] );

		function handleAdd() {
			if ( ! selectedCategory || ! newEntry.name.trim() ) {
				return;
			}

			var updated = Object.assign( {}, localData );
			if ( ! Array.isArray( updated[ selectedCategory ] ) ) {
				updated[ selectedCategory ] = [];
			}

			var entry = { name: newEntry.name.trim() };
			if ( newEntry.link && newEntry.link.trim() ) {
				entry.link = newEntry.link.trim();
			}

			updated[ selectedCategory ].push( entry );
			setLocalData( updated );
			setNewEntry( { name: '', link: '' } );
		}

		function handleRemove( category, index ) {
			var updated = Object.assign( {}, localData );
			updated[ category ].splice( index, 1 );
			if ( updated[ category ].length === 0 ) {
				delete updated[ category ];
			}
			setLocalData( updated );
		}

		function handleSave() {
			setIsSaving( true );
			var json = JSON.stringify( localData );

			// Update the block editor's post meta via the editor store.
			editPost( {
				meta: {}
			} );

			// Save the post (which includes meta) via the editor store.
			var editorDispatch = data.dispatch( 'core/editor' );
			if ( editorDispatch && typeof editorDispatch.savePost === 'function' ) {
				var postId = data.select( 'core/editor' ).getCurrentPostId();
				if ( postId ) {
					editorDispatch.savePost().then( function () {
						setIsSaving( false );
					} ).catch( function ( error ) {
						console.error( 'AI Authorship save error:', error );
						setIsSaving( false );
					} );
				} else {
					setIsSaving( false );
				}
			} else {
				// Fallback: save via REST API directly.
				var postId = data.select( 'core/editor' ).getCurrentPostId();
				if ( postId ) {
					wp.apiFetch( {
						path: '/wp/v2/posts/' + postId + '?force=true',
						method: 'POST',
						data: {
							id: postId,
							meta: {}
						}
					} ).then( function () {
						setIsSaving( false );
					} ).catch( function ( error ) {
						console.error( 'AI Authorship save error:', error );
						setIsSaving( false );
					} );
				} else {
					setIsSaving( false );
				}
			}
		}

		if ( total === 0 ) {
			return createElement( 'div', { className: 'authorship-empty' },
				createElement( 'p', null, __( 'Add attribution for AI-generated or AI-assisted content.', 'mrmurphy-theme' ) ),
				createElement( 'p', { className: 'heroicon-attribution' },
					createElement( 'small', null, createInterpolateElement(
						__( 'Icons by <a href="https://heroicons.com" target="_blank" rel="noopener">Heroicons</a>, MIT License.', 'mrmurphy-theme' ),
						{ a: createElement( 'a', { href: 'https://heroicons.com', target: '_blank', rel: 'noopener' } ) }
					) )
				),
				createElement( 'div', { className: 'authorship-add-entry' },
					createElement( SelectControl, {
						label: __( 'Category', 'mrmurphy-theme' ),
						value: selectedCategory,
						options: Object.keys( categories ).map( function ( key ) {
							return { value: key, label: categories[ key ].label };
						} ),
						onChange: function ( val ) { setSelectedCategory( val ); },
					} ),
					createElement( TextControl, {
						label: __( 'Name', 'mrmurphy-theme' ),
						value: newEntry.name,
						onChange: function ( val ) { setNewEntry( Object.assign( {}, newEntry, { name: val } ) ); },
						placeholder: __( 'e.g., GPT-4, Claude, Human Author', 'mrmurphy-theme' ),
						help: __( 'The name of the contributor.', 'mrmurphy-theme' ),
					} ),
					createElement( TextControl, {
						label: __( 'Link', 'mrmurphy-theme' ),
						value: newEntry.link,
						onChange: function ( val ) { setNewEntry( Object.assign( {}, newEntry, { link: val } ) ); },
						placeholder: 'https://',
						help: __( 'Optional URL.', 'mrmurphy-theme' ),
						type: 'url',
					} ),
					createElement( Button, {
						variant: 'primary',
						onClick: handleAdd,
						disabled: ! newEntry.name.trim(),
					}, __( 'Add', 'mrmurphy-theme' ) )
				)
			);
		}

		return createElement( Fragment, null,
			createElement( 'p', { className: 'heroicon-attribution' },
				createElement( 'small', null, createInterpolateElement(
					__( 'Icons by <a href="https://heroicons.com" target="_blank" rel="noopener">Heroicons</a>, MIT License.', 'mrmurphy-theme' ),
					{ a: createElement( 'a', { href: 'https://heroicons.com', target: '_blank', rel: 'noopener' } ) }
				) )
			),
			createElement( 'div', { className: 'authorship-entries' },
				Object.keys( localData ).map( function ( category ) {
					var entries = localData[ category ];
					if ( ! Array.isArray( entries ) || entries.length === 0 ) {
						return null;
					}

					var catConfig = categories[ category ] || { label: category, icon: 'question-mark-circle', color: 'var(--color-purple)' };

					return createElement( 'div', { className: 'authorship-category-block', key: category },
						createElement( 'div', {
							className: 'authorship-category-header',
							style: { '--cat-color': catConfig.color },
						},
							createElement( Heroicon, { name: catConfig.icon, color: catConfig.color, size: 16 } ),
							createElement( 'span', null, catConfig.label ),
							createElement( 'span', { className: 'authorship-count-badge' }, entries.length )
						),
						createElement( 'ul', { className: 'authorship-entry-list' },
							entries.map( function ( entry, index ) {
								return createElement( 'li', { className: 'authorship-entry-item', key: index },
									createElement( 'span', { className: 'authorship-entry-name' }, entry.name ),
									entry.link ? createElement( 'a', {
										href: entry.link,
										target: '_blank',
										rel: 'noopener',
										className: 'authorship-entry-link',
										style: { fontSize: '11px' },
									}, entry.link ) : null,
									createElement( IconButton, {
										icon: 'trash',
										label: __( 'Remove', 'mrmurphy-theme' ),
										onClick: function () { handleRemove( category, index ); },
										className: 'authorship-remove-btn',
										size: 'compact',
									} )
								);
							} )
						),
						createElement( 'div', { className: 'authorship-add-entry' },
							createElement( TextControl, {
								label: __( 'Add to %s', 'mrmurphy-theme' ).replace( '%s', catConfig.label ),
								value: newEntry.name,
								onChange: function ( val ) { setNewEntry( Object.assign( {}, newEntry, { name: val } ) ); },
								placeholder: __( 'New name...', 'mrmurphy-theme' ),
								onKeyDown: function ( e ) { if ( e.key === 'Enter' ) { e.preventDefault(); handleAdd(); } },
							} ),
							createElement( TextControl, {
								value: newEntry.link,
								onChange: function ( val ) { setNewEntry( Object.assign( {}, newEntry, { link: val } ) ); },
								placeholder: 'https://',
								type: 'url',
								onKeyDown: function ( e ) { if ( e.key === 'Enter' ) { e.preventDefault(); handleAdd(); } },
							} ),
							createElement( Button, {
								variant: 'secondary',
								onClick: handleAdd,
								disabled: ! newEntry.name.trim(),
								style: { marginTop: '4px' },
							}, __( 'Add', 'mrmurphy-theme' ) )
						)
					);
				} )
			),
			createElement( Button, {
				variant: 'primary',
				onClick: handleSave,
				isBusy: isSaving,
				disabled: isSaving,
				style: { marginTop: '12px', width: '100%' },
			}, isSaving ? __( 'Saving...', 'mrmurphy-theme' ) : __( 'Save', 'mrmurphy-theme' ) )
		);
	}

	/**
	 * Register the sidebar panel.
	 */
	function registerAuthorshipPanel() {
		blocks.registerPlugin( 'mrmurphy-authorship-panel', {
			render: function () {
				return createElement( PanelBody, {
					title: __( 'AI Authorship', 'mrmurphy-theme' ),
					initialOpen: false,
					className: 'mrmurphy-authorship-panel',
				},
					createElement( AuthorshipPanel, null )
				);
			},
			icon: 'lightbulb',
		} );
	}

	registerAuthorshipPanel();

} )( window.wp );
