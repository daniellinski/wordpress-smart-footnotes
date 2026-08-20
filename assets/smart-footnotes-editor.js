( function ( wp ) {
	const { domReady } = wp;
	const {
		registerFormatType,
		insertObject,
	} = wp.richText;
	const { RichTextToolbarButton } = wp.blockEditor;
	const {
		Modal,
		TextareaControl,
		TextControl,
		Button,
	} = wp.components;
	const { createElement, Fragment, useState, useEffect, useRef } = wp.element;
	const { __ } = wp.i18n;

	const FORMAT_NAME = 'smart-footnotes/footnote';
	const FORMAT_TITLE = __( 'Smart footnotes', 'smart-footnotes' );
	const MARKER_LABEL = __( 'footnote', 'smart-footnotes' );

	/**
	 * Editor UI for the Smart Footnotes format.
	 *
	 * The footnote is stored as an inline, non-editable object in the rich
	 * text (same pattern as core's own footnotes). Its text and optional
	 * source URL live in data-sfn-* attributes and are rendered into the
	 * popover by a `the_content` filter on the front end.
	 *
	 * @param {Object} props
	 * @param {Object} props.value                 Current rich text value.
	 * @param {Function} props.onChange            Rich text change handler.
	 * @param {boolean} props.isObjectActive       Whether the footnote object is selected.
	 * @param {boolean} props.isActive             Whether the format is active.
	 * @param {Object} props.activeObjectAttributes Attributes of the selected footnote.
	 * @return {WP.Element}
	 */
	function FootnoteEdit( {
		value,
		onChange,
		isObjectActive,
		isActive,
		activeObjectAttributes,
	} ) {
		const [ isOpen, setIsOpen ] = useState( false );
		const [ text, setText ] = useState( '' );
		const [ url, setUrl ] = useState( '' );
		const justSavedRef = useRef( false );

		useEffect( function () {
			if ( isObjectActive && ! justSavedRef.current ) {
				openModal();
			}

			if ( ! isObjectActive ) {
				setIsOpen( false );
			}
		}, [ isObjectActive ] );

		function openModal() {
			const attributes = isObjectActive ? activeObjectAttributes : {};

			setText( attributes[ 'data-sfn-text' ] || '' );
			setUrl( attributes[ 'data-sfn-url' ] || '' );
			setIsOpen( true );
		}

		function closeModal() {
			setIsOpen( false );
		}

		function saveFootnote() {
			if ( ! text.trim() && ! url.trim() ) {
				return;
			}

			const object = {
				type: FORMAT_NAME,
				attributes: {
					'data-sfn-text': text.trim(),
					'data-sfn-url': url.trim(),
				},
				innerHTML: '<span class="smart-footnote__button">' + MARKER_LABEL + '</span>',
			};

			justSavedRef.current = true;
			setTimeout( function () {
				justSavedRef.current = false;
			}, 0 );

			if ( isObjectActive ) {
				const start = value.start;
				onChange( insertObject( value, object, start, start + 1 ) );
			} else {
				const start = value.start;
				onChange( insertObject( value, object, start, start ) );
			}

			closeModal();
		}

		return createElement(
			Fragment,
			null,
			createElement( RichTextToolbarButton, {
				icon: 'editor-ol',
				title: FORMAT_TITLE,
				onClick: openModal,
				isActive: isActive || isObjectActive,
			} ),
			isOpen &&
				createElement(
					Modal,
					{
						title: FORMAT_TITLE,
						onRequestClose: closeModal,
					},
					createElement( TextareaControl, {
						label: __( 'Footnote text', 'smart-footnotes' ),
						help: __( 'Shown in a popover next to the footnote marker.', 'smart-footnotes' ),
						value: text,
						onChange: setText,
					} ),
					createElement( TextControl, {
						label: __( 'Source URL (optional)', 'smart-footnotes' ),
						value: url,
						onChange: setUrl,
					} ),
					createElement(
						'div',
						{ className: 'smart-footnote-editor__actions' },
						createElement(
							Button,
							{
								variant: 'primary',
								onClick: saveFootnote,
								disabled: ! text.trim() && ! url.trim(),
							},
							__( isObjectActive ? 'Save footnote' : 'Add footnote', 'smart-footnotes' )
						)
					)
				)
		);
	}

	domReady( function () {
		registerFormatType( FORMAT_NAME, {
			title: FORMAT_TITLE,
			tagName: 'span',
			className: 'smart-footnote',
			attributes: {
				'data-sfn-text': 'data-sfn-text',
				'data-sfn-url': 'data-sfn-url',
			},
			interactive: true,
			contentEditable: false,
			edit: FootnoteEdit,
		} );
	} );
} )( window.wp );