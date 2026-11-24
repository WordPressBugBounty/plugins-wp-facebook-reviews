/**
 * Setup wizard screen (admin.php?page=wpfb-setup).
 *
 * Steps: get reviews, choose a look (saves a real template and shows its
 * preview), add to the site, finish.
 *
 * Facebook reviews come the same way as on the plugin's Facebook page
 * (admin/js/wprev_facebook.js): the browser asks the plugin's Facebook app
 * (fbapp.ljapps.com, JSONP) for the pages and reviews the saved access code
 * can read, then posts each batch to the wpfb_get_results ajax action.
 */
( function ( $ ) {
	'use strict';

	var cfg = window.wpfbWizard || {};
	var i18n = cfg.i18n || {};
	var state = cfg.state || {};
	var fbapp = cfg.fbapp || {};
	var steps = [ 'source', 'look', 'place', 'finish' ];

	var current = 0;
	var furthest = 0;
	var savedKey = '';
	var saveTimer = null;
	var saveRequest = null;

	var $root;

	function text( key ) {
		return i18n[ key ] || '';
	}

	function post( action, data ) {
		return $.post( cfg.ajaxurl, $.extend( { action: action, nonce: cfg.nonce }, data ) );
	}

	function setStatus( $el, message, type ) {
		$el.removeClass( 'is-error is-success is-info' ).addClass( 'is-' + ( type || 'info' ) );
		$el.text( message ).prop( 'hidden', ! message );
	}

	// Ajax replies are normally { success, data }, but some of the plugin's own
	// code answers errors itself, for example with { ack, ackmsg } (sometimes as a string).
	function parseResponse( response ) {
		if ( 'string' === typeof response ) {
			var start = response.indexOf( '{' );
			try {
				return start > -1 ? JSON.parse( response.slice( start ) ) : null;
			} catch ( e ) {
				return null;
			}
		}
		return response;
	}

	function errorMessage( response, prefix ) {
		response = parseResponse( response );
		if ( response && response.data && response.data.message ) {
			return response.data.message;
		}
		if ( response && 'string' === typeof response.data && response.data ) {
			return text( prefix || 'requestFailed' ) + ' (' + response.data + ')';
		}
		if ( response && ( response.ackmsg || ( response.ack && 'success' !== response.ack ) ) ) {
			return text( prefix || 'fbFailed' ) + ' (' + ( response.ackmsg || response.ack ) + ')';
		}
		return text( 'requestFailed' );
	}

	/* ---------- Step 1: reviews ---------- */

	// Pages the saved access code can read, from the plugin's Facebook app.
	var connected = [];
	var connectedLoaded = false;
	// Page picked on an earlier visit, until the user picks one here.
	var preferred = state.pageid || '';
	var userPicked = false;
	// Pages whose download finished during this visit, even if Facebook had no reviews.
	var downloaded = {};
	var busy = false;

	function findSaved( pageid ) {
		var list = state.saved || [];
		for ( var i = 0; i < list.length; i++ ) {
			if ( list[ i ].pageid === pageid ) {
				return list[ i ];
			}
		}
		return null;
	}

	// Connected pages first (in Facebook's order), then pages that only have saved reviews.
	function allSources() {
		var list = [];
		var seen = {};
		var i;
		for ( i = 0; i < connected.length; i++ ) {
			var saved = findSaved( connected[ i ].pageid );
			list.push( {
				pageid: connected[ i ].pageid,
				name: connected[ i ].name,
				store: connected[ i ].store,
				count: saved ? saved.count : 0,
				connected: true
			} );
			seen[ connected[ i ].pageid ] = true;
		}
		var savedList = state.saved || [];
		for ( i = 0; i < savedList.length; i++ ) {
			if ( ! seen[ savedList[ i ].pageid ] ) {
				list.push( {
					pageid: savedList[ i ].pageid,
					name: savedList[ i ].name,
					count: savedList[ i ].count,
					connected: false
				} );
			}
		}
		return list;
	}

	function findSource( pageid ) {
		var list = allSources();
		for ( var i = 0; i < list.length; i++ ) {
			if ( list[ i ].pageid === pageid ) {
				return list[ i ];
			}
		}
		return null;
	}

	function selectedPageid() {
		var $radio = $root.find( 'input[name="wpfb-wiz-source"]:checked' );
		return $radio.length ? String( $radio.val() ) : '';
	}

	function sourceNote( src ) {
		if ( src.count > 0 ) {
			return text( 1 === src.count ? 'savedOne' : 'savedMany' ).replace( '%s', src.count );
		}
		return text( 'noneSaved' );
	}

	function renderSources() {
		var list = allSources();
		var pick = userPicked ? selectedPageid() : preferred;
		if ( ! findSource( pick ) ) {
			pick = '';
			// A page that already has reviews, or the only page there is.
			for ( var i = 0; i < list.length; i++ ) {
				if ( list[ i ].count > 0 ) {
					pick = list[ i ].pageid;
					break;
				}
			}
			if ( ! pick && 1 === list.length ) {
				pick = list[ 0 ].pageid;
			}
		}

		var $list = $root.find( '[data-source-list]' ).empty();
		$.each( list, function ( index, src ) {
			var $input = $( '<input type="radio" name="wpfb-wiz-source">' ).val( src.pageid ).prop( 'checked', src.pageid === pick );
			var $note = $( '<span class="wpfb-wiz__muted">' ).attr( 'data-source-note', src.pageid ).text( sourceNote( src ) );
			$( '<label class="wpfb-wiz__source">' )
				.append( $input, $( '<span>' ).append( $( '<strong>' ).text( src.name ), $note ) )
				.appendTo( $list );
		} );
		$root.find( '[data-sources]' ).prop( 'hidden', ! list.length );

		var picked = findSource( pick );
		if ( picked ) {
			state.pageid = picked.pageid;
			state.pagename = picked.name;
		}
		updateGetButton();
	}

	function updateGetButton() {
		var src = findSource( selectedPageid() );
		$root.find( '[data-get-wrap]' ).prop( 'hidden', ! connectedLoaded || ! connected.length );
		$root.find( '[data-get-reviews]' ).prop( 'disabled', busy || ! src || ! src.connected );
		$root.find( '[data-get-spinner]' ).toggleClass( 'is-active', busy );
	}

	function showConnect( message, type, title ) {
		$root.find( '[data-connect-title]' ).text( title || ( ( state.saved || [] ).length ? text( 'connectAgain' ) : text( 'connectFirst' ) ) );
		$root.find( '[data-connect]' ).prop( 'hidden', false );
		setStatus( $root.find( '[data-connect-status]' ), message || '', type );
	}

	function hideConnect() {
		$root.find( '[data-connect]' ).prop( 'hidden', true );
		setStatus( $root.find( '[data-connect-status]' ), '', 'info' );
	}

	// Ask the plugin's Facebook app which pages an access code can read.
	function requestPages( code ) {
		return $.ajax( {
			url: fbapp.pages,
			dataType: 'jsonp',
			jsonp: 'callback',
			timeout: 30000,
			data: { q: 'listpages', acode: code, format: 'json' }
		} );
	}

	// The app's page list (a successful listpages reply) as the connected pages.
	function setConnected( response ) {
		connected = [];
		var data = Array.isArray( response.data ) ? response.data : [];
		for ( var i = 0; i < data.length; i++ ) {
			if ( data[ i ] && data[ i ].fbpageid ) {
				// Facebook's app sends names HTML-encoded ("&amp;", "&#039;").
				var rawName = String( data[ i ].fbpagename || data[ i ].fbpageid );
				connected.push( {
					pageid: String( data[ i ].fbpageid ),
					name: decodeHtml( rawName ),
					store: storeName( rawName )
				} );
			}
		}
		connectedLoaded = true;
	}

	// The pages the saved access code can read.
	function listPages() {
		var done = $.Deferred();
		connected = [];
		connectedLoaded = false;
		if ( ! state.code ) {
			renderSources();
			return done.resolve().promise();
		}

		var $loading = $root.find( '[data-pages-loading]' ).prop( 'hidden', false );
		requestPages( state.code )
			.done( function ( response ) {
				if ( ! response || 'success' !== response.ack ) {
					showConnect( text( 'codeFailed' ) + ( response && response.ack ? ' ' + response.ack : '' ), 'error' );
					return;
				}
				setConnected( response );
				if ( connected.length ) {
					hideConnect();
				} else {
					showConnect( text( 'noPages' ), 'error' );
				}
			} )
			.fail( function () {
				showConnect( text( 'unreachable' ), 'error' );
			} )
			.always( function () {
				$loading.prop( 'hidden', true );
				renderSources();
				done.resolve();
			} );
		return done.promise();
	}

	// "Get my access code": the app's Facebook login in a small window, so this tab stays on the wizard.
	// If the browser blocks the window, the link opens a new tab instead.
	function openCodeWindow( event ) {
		var url = $( this ).attr( 'href' );
		var width = 620;
		var height = 720;
		var left = Math.max( 0, ( window.screenX || 0 ) + ( ( window.outerWidth || width ) - width ) / 2 );
		var top = Math.max( 0, ( window.screenY || 0 ) + ( ( window.outerHeight || height ) - height ) / 2 );
		var win = window.open( url, 'wpfbAccessCode', 'popup=yes,width=' + width + ',height=' + height + ',left=' + Math.round( left ) + ',top=' + Math.round( top ) );
		if ( win ) {
			event.preventDefault();
			try {
				win.opener = null;
			} catch ( e ) {}
			win.focus();
		} else {
			setStatus( $root.find( '[data-connect-status]' ), text( 'popupBlocked' ), 'info' );
		}
	}

	// "Connect": check the pasted code with the Facebook app, save it, then show its pages.
	// A code the app rejects is never saved.
	function connectWithCode() {
		var $input = $root.find( '[data-code-input]' );
		var code = $.trim( String( $input.val() || '' ) );
		var $status = $root.find( '[data-connect-status]' );
		if ( ! code ) {
			setStatus( $status, text( 'codeEmpty' ), 'error' );
			$input.trigger( 'focus' );
			return;
		}

		var $btn = $root.find( '[data-connect-code]' ).prop( 'disabled', true );
		var $spin = $root.find( '[data-check-spinner]' ).addClass( 'is-active' );
		var finish = function () {
			$btn.prop( 'disabled', false );
			$spin.removeClass( 'is-active' );
		};
		setStatus( $status, text( 'codeChecking' ), 'info' );
		setStatus( $root.find( '[data-source-status]' ), '', 'info' );
		setNavMessage( '' );

		requestPages( code )
			.done( function ( response ) {
				if ( ! response || 'success' !== response.ack ) {
					setStatus( $status, text( 'codeRejected' ) + ( response && response.ack ? ' ' + response.ack : '' ), 'error' );
					finish();
					return;
				}
				post( 'wpfb_wizard_save_code', { code: code } )
					.done( function ( saved ) {
						saved = parseResponse( saved );
						if ( ! saved || ! saved.success ) {
							setStatus( $status, errorMessage( saved ), 'error' );
							return;
						}
						state.code = saved.data.code || code;
						state.saved = saved.data.saved || state.saved || [];
						$input.val( '' );
						setConnected( response );
						if ( connected.length ) {
							hideConnect();
							setStatus( $root.find( '[data-source-status]' ), text( 'codeSaved' ), 'success' );
						} else {
							setStatus( $status, text( 'noPages' ), 'error' );
						}
						renderSources();
					} )
					.fail( function () {
						setStatus( $status, text( 'requestFailed' ), 'error' );
					} )
					.always( finish );
			} )
			.fail( function () {
				setStatus( $status, text( 'unreachable' ), 'error' );
				finish();
			} );
	}

	// "Use a different access code" (a page is missing, or the user switched accounts).
	function changeCode() {
		showConnect( '', 'info', text( 'connectChange' ) );
		$root.find( '[data-code-input]' ).trigger( 'focus' );
	}

	// "Check again": reload the access code and saved reviews, then the connected pages.
	function checkAgain() {
		var $buttons = $root.find( '[data-check-again]' ).prop( 'disabled', true );
		var $spin = $root.find( '[data-check-spinner]' ).addClass( 'is-active' );
		var finish = function () {
			$buttons.prop( 'disabled', false );
			$spin.removeClass( 'is-active' );
		};
		setNavMessage( '' );
		setStatus( $root.find( '[data-source-status]' ), '', 'info' );

		post( 'wpfb_wizard_sources', {} )
			.done( function ( response ) {
				response = parseResponse( response );
				if ( ! response || ! response.success ) {
					showConnect( errorMessage( response ), 'error' );
					finish();
					return;
				}
				state.code = response.data.code || '';
				state.saved = response.data.saved || [];
				if ( ! state.code ) {
					connected = [];
					connectedLoaded = false;
					renderSources();
					showConnect( text( 'noCode' ), 'info' );
					finish();
					return;
				}
				listPages().always( finish );
			} )
			.fail( function () {
				showConnect( text( 'requestFailed' ), 'error' );
				finish();
			} );
	}

	function setBusy( value ) {
		busy = value;
		updateGetButton();
	}

	// Decode HTML entities into plain text (shown with .text(), so nothing is parsed as HTML).
	function decodeHtml( value ) {
		return $( '<textarea>' ).html( String( value ) ).text();
	}

	// The name the Facebook page saves with each review. It swaps ' for %27 and drops " in the
	// raw (encoded) name, and the browser then decodes the entities, so do the same in that order.
	function storeName( name ) {
		return decodeHtml( String( name ).replace( /'/g, '%27' ).replace( /"/g, '' ) );
	}

	// Message for a wpfb_get_results reply that isn't "inserted-lastid-count".
	function saveError( raw ) {
		var parsed = parseResponse( raw );
		var detail = '';
		if ( parsed && 'object' === typeof parsed ) {
			detail = parsed.ackmsg || ( parsed.data && parsed.data.message ) || ( 'string' === typeof parsed.data ? parsed.data : '' ) || parsed.message || parsed.ack || '';
		}
		if ( ! detail && 'string' === typeof raw ) {
			detail = $.trim( $( '<div>' ).html( raw ).text() ).slice( 0, 200 );
		}
		return text( 'saveFailed' ) + ( detail ? ' (' + detail + ')' : '' );
	}

	function downloadFailed( message ) {
		setBusy( false );
		setStatus( $root.find( '[data-source-status]' ), message, 'error' );
		// Batches saved before the error still count; refresh the numbers.
		post( 'wpfb_wizard_sources', {} ).done( function ( response ) {
			response = parseResponse( response );
			if ( response && response.success ) {
				state.saved = response.data.saved || state.saved;
				renderSources();
			}
		} );
	}

	function downloadDone( src ) {
		post( 'wpfb_wizard_sources', { pageid: src.pageid, pagename: src.name } )
			.done( function ( response ) {
				response = parseResponse( response );
				setBusy( false );
				if ( ! response || ! response.success ) {
					setStatus( $root.find( '[data-source-status]' ), errorMessage( response ), 'error' );
					return;
				}
				state.saved = response.data.saved || state.saved;
				downloaded[ src.pageid ] = true;
				if ( ! userPicked || selectedPageid() === src.pageid ) {
					preferred = src.pageid;
				}
				renderSources();
				setStatus( $root.find( '[data-source-status]' ), response.data.message, response.data.count > 0 ? 'success' : 'info' );
				setNavMessage( '' );
				if ( response.data.count > 0 ) {
					// Same last step as the Facebook page: keep copies of the reviewers' photos on the site.
					// Nothing waits for it; it can take a while on sites with many reviews.
					$.post( cfg.ajaxurl, { action: 'wpfb_avatar_tolocal', wpfb_nonce: cfg.fbNonce } );
				}
				// The reviews just arrived: redraw the preview with them.
				if ( state.tid ) {
					savedKey = '';
					if ( 'look' === steps[ current ] ) {
						queueSave( 0 );
					}
				}
			} )
			.fail( function () {
				setBusy( false );
				setStatus( $root.find( '[data-source-status]' ), text( 'requestFailed' ), 'error' );
			} );
	}

	// One batch from the plugin's Facebook app, saved with wpfb_get_results; repeats while Facebook has more.
	function download( src, after, added, round ) {
		$.ajax( {
			url: fbapp.reviews,
			dataType: 'jsonp',
			jsonp: 'callback',
			timeout: 60000,
			data: { q: 'getrevs', acode: state.code, pid: src.pageid, afterc: after, format: 'json' }
		} )
			.done( function ( response ) {
				if ( ! response || ( 'undefined' !== typeof response.ack && 'success' !== response.ack ) ) {
					downloadFailed( text( 'fbFailed' ) + ( response && response.ack ? ' (' + response.ack + ')' : '' ) );
					return;
				}
				var list = Array.isArray( response.data ) ? response.data : [];
				var batch = [];
				for ( var i = 0; i < list.length; i++ ) {
					var item = list[ i ];
					if ( ! item || ! item.reviewer ) {
						continue;
					}
					batch.push( {
						pageid: src.pageid,
						pagename: src.store || storeName( src.name ),
						created_time: item.created_time,
						reviewer_name: item.reviewer.name,
						reviewer_id: item.reviewer.id,
						rating: item.rating,
						recommendation_type: item.recommendation_type || '',
						review_text: item.review_text || '',
						reviewer_imgurl: item.reviewer.imgurl || '',
						type: 'Facebook'
					} );
				}
				var paging = response.paging || {};
				var next = paging.next && paging.cursors && paging.cursors.after ? String( paging.cursors.after ) : '';
				if ( next === after || round >= 100 ) {
					next = '';
				}
				if ( ! batch.length ) {
					downloadDone( src );
					return;
				}

				$.post( cfg.ajaxurl, { action: 'wpfb_get_results', wpfb_nonce: cfg.fbNonce, postreviewarray: batch } )
					.done( function ( reply ) {
						// The handler answers "inserted-lastid-count" (inserted is empty when the last insert failed).
						var match = 'string' === typeof reply ? $.trim( reply ).match( /(\d*)-(\d+)-(\d+)$/ ) : null;
						if ( ! match ) {
							downloadFailed( saveError( reply ) );
							return;
						}
						added += parseInt( match[ 3 ], 10 ) || 0;
						setStatus( $root.find( '[data-source-status]' ), text( 'downloadingN' ).replace( '%s', added ), 'info' );
						if ( next ) {
							download( src, next, added, round + 1 );
						} else {
							downloadDone( src );
						}
					} )
					.fail( function ( xhr ) {
						downloadFailed( saveError( xhr && xhr.responseText ? xhr.responseText : null ) );
					} );
			} )
			.fail( function () {
				downloadFailed( text( 'unreachable' ) );
			} );
	}

	function getReviews() {
		var src = findSource( selectedPageid() );
		var $status = $root.find( '[data-source-status]' );
		if ( busy ) {
			return;
		}
		if ( ! src || ! src.connected ) {
			setStatus( $status, text( 'needPick' ), 'error' );
			return;
		}
		setBusy( true );
		setNavMessage( '' );
		setStatus( $status, text( 'downloading' ), 'info' );
		download( src, '', 0, 0 );
	}

	// Reviews saved, or a finished download (the preview then uses sample reviews).
	function canLeaveSource() {
		var src = findSource( selectedPageid() );
		if ( ! src ) {
			return false;
		}
		state.pageid = src.pageid;
		state.pagename = src.name;
		return src.count > 0 || !! downloaded[ src.pageid ];
	}

	function sourceMessage() {
		if ( ! allSources().length ) {
			return text( 'needConnect' );
		}
		return selectedPageid() ? text( 'needReviews' ) : text( 'needPick' );
	}

	/* ---------- Step 2: look ---------- */

	function lookValues() {
		return {
			pageid: state.pageid,
			pagename: state.pagename || '',
			style: $root.find( 'input[name="wpfb-wiz-style"]:checked' ).val() || '1',
			layout: $root.find( 'input[name="wpfb-wiz-layout"]:checked' ).val() || 'slider',
			per_row: $root.find( 'input[name="wpfb-wiz-perrow"]:checked' ).val() || '3',
			rows: $root.find( 'input[name="wpfb-wiz-rows"]:checked' ).val() || '1',
			badge: $root.find( 'input[name="wpfb-wiz-badge"]:checked' ).val() || 'above',
			read_more: $root.find( 'input[name="wpfb-wiz-readmore"]:checked' ).val() || '30'
		};
	}

	// A badge beside the reviews leaves little room for 3 or 4 reviews per row.
	function updateBadgeHint() {
		var values = lookValues();
		var side = 'left' === values.badge || 'right' === values.badge;
		$root.find( '[data-badge-hint]' ).prop( 'hidden', ! ( side && parseInt( values.per_row, 10 ) >= 3 ) );
	}

	function queueSave( delay ) {
		updateBadgeHint();
		window.clearTimeout( saveTimer );
		saveTimer = window.setTimeout( saveTemplate, delay );
	}

	function saveTemplate() {
		var values = lookValues();
		var key = JSON.stringify( values );
		if ( key === savedKey && state.tid ) {
			return;
		}
		if ( saveRequest ) {
			saveRequest.abort();
		}

		var $spin = $root.find( '[data-preview-spinner]' );
		var $msg = $root.find( '[data-preview-msg]' );
		$spin.addClass( 'is-active' );
		$msg.text( text( 'saving' ) );
		$root.find( '[data-preview-wrap]' ).addClass( 'is-loading' );

		saveRequest = post( 'wpfb_wizard_save_template', $.extend( { tid: state.tid || 0 }, values ) )
			.done( function ( response ) {
				if ( ! response || ! response.success ) {
					$msg.text( errorMessage( response ) );
					return;
				}
				savedKey = key;
				state.tid = response.data.tid;
				state.title = response.data.title;
				state.shortcode = response.data.shortcode;
				// With no reviews saved yet, the preview uses sample reviews; say so.
				$msg.text( response.data.sample ? text( 'sampleNote' ) : '' );
				$root.find( '[data-preview-wrap]' ).toggleClass( 'is-sample', !! response.data.sample );
				var $preview = $root.find( '[data-preview]' );
				if ( ! response.data.html ) {
					$preview.html( $( '<p class="wpfb-wiz__waiting">' ).text( text( 'previewWaiting' ) ) );
				} else {
					$preview.html( response.data.html );
				}
				if ( typeof window.wprevInitReviewSliders === 'function' ) {
					window.wprevInitReviewSliders( $preview[ 0 ] );
				}
				fillTemplateDetails();
			} )
			.fail( function ( xhr, status ) {
				if ( 'abort' !== status ) {
					$msg.text( text( 'requestFailed' ) );
				}
			} )
			.always( function ( a, status ) {
				if ( 'abort' === status ) {
					return;
				}
				saveRequest = null;
				$spin.removeClass( 'is-active' );
				$root.find( '[data-preview-wrap]' ).removeClass( 'is-loading' );
			} );
	}

	/* ---------- Step 3: place ---------- */

	function fillTemplateDetails() {
		$root.find( '[data-template-title]' ).text( state.title ? '“' + state.title + '”' : '' );
		$root.find( '[data-shortcode]' ).text( state.tid ? ( state.shortcode || '[wprevpro_usetemplate tid="' + state.tid + '"]' ) : '' );
	}

	function showPage( page ) {
		if ( ! page ) {
			return;
		}
		state.page = page;
		$root.find( '[data-page-edit]' ).attr( 'href', page.edit );
		$root.find( '[data-page-view]' ).attr( 'href', page.view );
		$root.find( '[data-page-links]' ).prop( 'hidden', false );
		// One page is enough; the form would only make a duplicate.
		$root.find( '[data-page-form]' ).prop( 'hidden', true );
	}

	function createPage() {
		var $btn = $root.find( '[data-create-page]' );
		var $spin = $root.find( '[data-page-spinner]' );
		var $status = $root.find( '[data-page-status]' );

		$btn.prop( 'disabled', true );
		$spin.addClass( 'is-active' );
		setStatus( $status, text( 'creatingPage' ), 'info' );

		post( 'wpfb_wizard_create_page', { tid: state.tid, title: $.trim( $( '#wpfb-wiz-pagetitle' ).val() ) } )
			.done( function ( response ) {
				if ( response && response.success ) {
					setStatus( $status, response.data.message, 'success' );
					showPage( response.data.page );
				} else {
					setStatus( $status, errorMessage( response ), 'error' );
				}
			} )
			.fail( function () {
				setStatus( $status, text( 'requestFailed' ), 'error' );
			} )
			.always( function () {
				$btn.prop( 'disabled', false );
				$spin.removeClass( 'is-active' );
			} );
	}

	function copyShortcode() {
		var value = $root.find( '[data-shortcode]' ).text();
		var $btn = $root.find( '[data-copy-shortcode]' );
		var done = function () {
			$btn.text( text( 'copied' ) );
			window.setTimeout( function () {
				$btn.text( text( 'copy' ) );
			}, 1500 );
		};
		if ( navigator.clipboard && window.isSecureContext ) {
			navigator.clipboard.writeText( value ).then( done );
			return;
		}
		var $tmp = $( '<textarea readonly>' ).val( value ).css( { position: 'absolute', left: '-9999px' } ).appendTo( 'body' );
		$tmp[ 0 ].select();
		try {
			document.execCommand( 'copy' );
			done();
		} catch ( e ) {}
		$tmp.remove();
	}

	/* ---------- Navigation ---------- */

	function setNavMessage( message ) {
		$root.find( '[data-nav-msg]' ).text( message );
	}

	function show( index, focus ) {
		current = index;
		furthest = Math.max( furthest, index );
		var key = steps[ index ];

		$root.find( '[data-step]' ).each( function () {
			$( this ).prop( 'hidden', $( this ).attr( 'data-step' ) !== key );
		} );
		$root.find( '[data-step-item]' ).each( function ( i ) {
			$( this ).toggleClass( 'is-current', i === index ).toggleClass( 'is-done', i < index );
			$( this ).find( '[data-goto]' ).prop( 'disabled', i > furthest ).attr( 'aria-current', i === index ? 'step' : null );
		} );

		$root.find( '[data-prev]' ).prop( 'hidden', 0 === index );
		$root.find( '[data-next]' ).text( index === steps.length - 1 ? text( 'finish' ) : text( 'continue' ) );
		$root.find( '[data-count]' ).text( text( 'stepOf' ).replace( '%1$d', index + 1 ).replace( '%2$d', steps.length ) );
		setNavMessage( '' );

		if ( 'look' === key ) {
			queueSave( 0 );
		}
		if ( 'place' === key ) {
			fillTemplateDetails();
		}
		if ( 'finish' === key ) {
			var src = findSource( state.pageid );
			$root.find( '[data-finish-empty]' ).prop( 'hidden', ! src || src.count > 0 );
		}
		if ( focus ) {
			$root.find( '[data-step="' + key + '"] h2' ).trigger( 'focus' );
		}
	}

	function next() {
		var key = steps[ current ];
		if ( 'source' === key && ! canLeaveSource() ) {
			setNavMessage( sourceMessage() );
			return;
		}
		if ( 'look' === key && ( ! state.tid || saveRequest ) ) {
			setNavMessage( text( 'noTemplate' ) );
			return;
		}
		if ( current === steps.length - 1 ) {
			finish();
			return;
		}
		show( current + 1, true );
	}

	function finish() {
		var $btn = $root.find( '[data-next]' ).prop( 'disabled', true );
		post( 'wpfb_wizard_finish', {} )
			.done( function ( response ) {
				if ( response && response.success && response.data.redirect ) {
					window.location.href = response.data.redirect;
					return;
				}
				setNavMessage( errorMessage( response ) );
				$btn.prop( 'disabled', false );
			} )
			.fail( function () {
				setNavMessage( text( 'requestFailed' ) );
				$btn.prop( 'disabled', false );
			} );
	}

	$( function () {
		$root = $( '[data-wpfb-wizard]' );
		if ( ! $root.length ) {
			return;
		}

		$root.on( 'change', 'input[name="wpfb-wiz-source"]', function () {
			userPicked = true;
			preferred = String( $( this ).val() );
			var picked = findSource( preferred );
			if ( picked ) {
				state.pageid = picked.pageid;
				state.pagename = picked.name;
			}
			if ( ! busy ) {
				setStatus( $root.find( '[data-source-status]' ), '', 'info' );
			}
			setNavMessage( '' );
			updateGetButton();
		} );
		$root.on( 'click', '[data-get-reviews]', getReviews );
		$root.on( 'click', '[data-check-again]', checkAgain );
		$root.on( 'click', '[data-get-code]', openCodeWindow );
		$root.on( 'click', '[data-connect-code]', connectWithCode );
		$root.on( 'click', '[data-change-code]', changeCode );
		$root.on( 'keydown', '[data-code-input]', function ( event ) {
			if ( 13 === event.which ) {
				event.preventDefault();
				connectWithCode();
			}
		} );

		$root.on( 'change', 'input[name="wpfb-wiz-style"], input[name="wpfb-wiz-layout"], input[name="wpfb-wiz-perrow"], input[name="wpfb-wiz-rows"], input[name="wpfb-wiz-badge"], input[name="wpfb-wiz-readmore"]', function () {
			queueSave( 250 );
		} );

		$root.on( 'click', '[data-create-page]', createPage );
		$root.on( 'click', '[data-copy-shortcode]', copyShortcode );

		$root.on( 'click', '[data-next]', next );
		$root.on( 'click', '[data-prev]', function () {
			if ( current > 0 ) {
				show( current - 1, true );
			}
		} );
		$root.on( 'click', '[data-goto]', function () {
			var index = steps.indexOf( $( this ).attr( 'data-goto' ) );
			if ( index > -1 && index <= furthest && index !== current ) {
				if ( index > current ) {
					// Moving forward through the stepper follows the same checks as Continue.
					while ( current < index ) {
						var before = current;
						next();
						if ( current === before ) {
							return;
						}
					}
					return;
				}
				show( index, true );
			}
		} );

		if ( state.page ) {
			showPage( state.page );
		}
		renderSources();
		if ( state.code ) {
			listPages();
		} else {
			showConnect( '', 'info' );
		}
		show( 0, false );
	} );
}( window.jQuery ) );
