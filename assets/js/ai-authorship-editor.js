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

	var Button = components.Button;
	var SelectControl = components.SelectControl;
	var TextControl = components.TextControl;


	var heroicons = {
		'user': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 7.5 17.998A17.933 17.933 0 0 1 4.501 20.118Z"/>',
		'cpu-chip': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9v9h-9v-9Z"/>',
		'sparkles': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z"/>',
		'light-bulb': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18v-1.5m0 0a7.5 7.5 0 1 0-3-5.85m3 5.85V12m-6.375 0h12.75m-12.75 3h12.75M12 3v1.5"/>',
		'server': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 7.5a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 7.5v9a2.25 2.25 0 0 0 2.25 2.25h13.5A2.25 2.25 0 0 0 21 16.5v-9ZM4.5 3v18m16.5-18v18"/>',
		'plus': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>',
		'trash': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>',
		'question-mark-circle': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 3.758a4.496 4.496 0 0 1 4.242 7.407c-1.056.807-2.59.807-3.646 0a2.248 2.248 0 0 0-3.476.952l-.152.457a1.124 1.124 0 0 0 1.736 1.278l.213-.154a.75.75 0 0 1 .933 1.178l-.213.154a2.625 2.625 0 0 1-4.061-2.993l.152-.457a3.748 3.748 0 0 1 5.772-1.988ZM10.5 15.75a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0Z"/>',
	};

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

	function AuthorshipPanel() {
		var metaKey = mrmurphyAuthorship.meta_key;
		var categories = mrmurphyAuthorship.categories || {};

		var _wp$data = data,
			useSelect = _wp$data.useSelect,
			useDispatch = _wp$data.useDispatch;

		var _ls = useState( {} ),
			localData = _ls[ 0 ],
			setLocalData = _ls[ 1 ];
		var _sv = useState( false ),
			isSaving = _sv[ 0 ],
			setIsSaving = _sv[ 1 ];
		var _ne = useState( { name: '', link: '' } ),
			newEntry = _ne[ 0 ],
			setNewEntry = _ne[ 1 ];
		var _sc = useState( '' ),
			selectedCategory = _sc[ 0 ],
			setSelectedCategory = _sc[ 1 ];

		var authorshipMeta = useSelect( function ( select ) {
			var post = select( 'core/editor' ).getCurrentPost();
			if ( ! post || ! post.meta ) {
				return {};
			}
			var val = post.meta[ metaKey ];
			if ( typeof val === 'string' ) {
				try { val = JSON.parse( val ); } catch ( e ) { val = {}; }
			}
			if ( typeof val !== 'object' || val === null || Array.isArray( val ) ) {
				val = {};
			}
			return val;
		}, [ metaKey ] );

		var editPost = useDispatch( 'core/editor' ).editPost;

		useEffect( function () {
			setLocalData( authorshipMeta );
		}, [ authorshipMeta ] );

		var catKeys = Object.keys( categories );
		var catOptions = catKeys.map( function ( key ) {
			return { value: key, label: categories[ key ].label };
		} );

		if ( ! selectedCategory && catKeys.length > 0 ) {
			setSelectedCategory( catKeys[ 0 ] );
		}

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

		function handleEditField( category, index, field, value ) {
			var updated = Object.assign( {}, localData );
			updated[ category ] = updated[ category ].slice();
			updated[ category ][ index ] = Object.assign( {}, updated[ category ][ index ] );
			updated[ category ][ index ][ field ] = value;
			setLocalData( updated );
		}

		function handleSave() {
			setIsSaving( true );
			var meta = {};
			meta[ metaKey ] = localData;
			editPost( { meta: meta } );
			setIsSaving( false );
		}

		var entryElements = [];
		Object.keys( localData ).forEach( function ( category ) {
			var entries = localData[ category ];
			if ( ! Array.isArray( entries ) || entries.length === 0 ) return;
			var catConfig = categories[ category ] || { label: category, icon: 'question-mark-circle', color: 'var(--color-purple)' };
			var listItems = entries.map( function ( entry, index ) {
				return createElement( 'li', { className: 'authorship-entry-item', key: index },
					createElement( TextControl, {
						value: entry.name || '',
						onChange: function ( val ) { handleEditField( category, index, 'name', val ); },
						placeholder: 'Name',
						style: { flex: 1, margin: 0 },
					} ),
					createElement( TextControl, {
						value: entry.link || '',
						onChange: function ( val ) { handleEditField( category, index, 'link', val ); },
						placeholder: 'https://',
						type: 'url',
						style: { flex: 0.6, margin: 0, minWidth: '100px' },
					} ),
					createElement( Button, {
						icon: 'trash',
						label: 'Remove',
						onClick: function () { handleRemove( category, index ); },
						className: 'authorship-remove-btn',
						size: 'compact',
						variant: 'secondary',
						isDestructive: true,
					} )
				);
			} );
			entryElements.push(
				createElement( 'div', { className: 'authorship-category-block', key: category },
					createElement( 'div', { className: 'authorship-category-header', style: { '--cat-color': catConfig.color } },
						createElement( Heroicon, { name: catConfig.icon, color: catConfig.color, size: 16 } ),
						createElement( 'span', null, catConfig.label ),
						createElement( 'span', { className: 'authorship-count-badge' }, entries.length )
					),
					createElement( 'ul', { className: 'authorship-entry-list' }, listItems )
				)
			);
		} );

		return createElement( 'div', null,
			entryElements.length > 0 ? createElement( 'div', { className: 'authorship-entries' }, entryElements ) : null,
			createElement( 'div', { className: 'authorship-add-entry' },
				createElement( SelectControl, { label: 'Category', value: selectedCategory, options: catOptions, onChange: function ( val ) { setSelectedCategory( val ); } } ),
				createElement( TextControl, { label: 'Name', value: newEntry.name, onChange: function ( val ) { setNewEntry( Object.assign( {}, newEntry, { name: val } ) ); }, placeholder: 'e.g. GPT-4, Human Author' } ),
				createElement( TextControl, { label: 'Link', value: newEntry.link, onChange: function ( val ) { setNewEntry( Object.assign( {}, newEntry, { link: val } ) ); }, placeholder: 'https://', type: 'url' } ),
				createElement( Button, { variant: 'primary', onClick: handleAdd, disabled: ! newEntry.name.trim(), style: { width: '100%' } }, 'Add' ),
			),
			createElement( Button, { variant: 'primary', onClick: handleSave, isBusy: isSaving, disabled: isSaving, style: { marginTop: '12px', width: '100%' } }, isSaving ? 'Saving...' : 'Save' )
		);
	}

	function registerAuthorshipPanel() {
		var PluginDocumentSettingPanel = ( wp.editor && wp.editor.PluginDocumentSettingPanel ) || wp.editPost.PluginDocumentSettingPanel;

		wp.plugins.registerPlugin( 'mrmurphy-authorship-panel', {
			render: function () {
				return createElement( PluginDocumentSettingPanel, {
					name: 'mrmurphy-authorship',
					title: __( 'AI Authorship', 'mrmurphy-theme' ),
					className: 'mrmurphy-authorship-panel',
					icon: 'lightbulb',
				},
					createElement( AuthorshipPanel, null )
				);
			},
			icon: 'lightbulb',
		} );
	}

	registerAuthorshipPanel();

} )( window.wp );
