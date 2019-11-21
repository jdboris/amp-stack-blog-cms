
// My libraries
window.Libraries = { };

Libraries.Colors = {
	WARNING: "warning",
	DANGER: "danger"
};

Libraries.Modal = class Modal
{
	constructor( title, body, alertType = "", selector = "#static-modal" )
	{
		this.element = $( selector ).clone( )[ 0 ];
		this.element.id = selector + "-" + Date.now();
		$( this.element ).on( "hidden.bs.modal", function( ) {
			$( this.element ).remove( );
		} );

		$( ".modal-title", this.element ).html( title );
		$( ".modal-body", this.element ).html( body );

		if( alertType !== "" )
		{
			$( ".modal-body", this.element ).addClass( "mb-0 alert alert-" + alertType );
	}

	}

	show( )
	{
		$( this.element ).modal( "show" );
	}
};

Libraries.ConfirmationModal = class ConfirmationModal extends Libraries.Modal
{
	constructor( title, body, alertType = "", selector = "#static-confirmation-modal" )
	{
		super( title, body, alertType, selector );
	}

	yesButton( value, callback, color = "" )
	{
		$( ".yes-button", this.element ).html( value );

		if( color !== "" )
		{
			$( ".yes-button", this.element ).addClass( "btn-" + color );
		}

		this.onYes = callback;
	}

	set onYes( callback )
	{
		$( ".yes-button", this.element ).off( "click" ).on( "click", callback );
	}

	noButton( value, callback, color = "" )
	{
		$( ".no-button", this.element ).html( value );

		if( color !== "" )
		{
			$( ".no-button", this.element ).addClass( "btn-" + color );
		}

		this.onNo = callback;
	}

	set onNo( callback )
	{
		$( ".no-button", this.element ).off( "click" ).on( "click", callback );
	}
};

Libraries.HTML = class HTML
{
	static makeEditable( selector )
	{
		$( selector ).css( "position", "relative" );
		$( selector ).append( `<a class="html-edit-button" href="javascript:;" onclick="Libraries.HTML.openEditor( '` + selector + `' )"><i class="fas fa-edit h3 mb-0"></i></a>` );
	}

	static openEditor( selector )
	{
		$( selector + " > .html-edit-button" ).remove( );

		let html = $( selector ).html( );
		$( selector ).html( "" );

		$( selector ).append( `<div class="summernote-element"></div>` );
		$( selector + " > .summernote-element" ).summernote( "code", html );

		$( selector ).append( `<a class="html-save-button" href="javascript:;" onclick="Libraries.HTML.saveEditor( '` + selector + `' )"><i class="fas fa-save h3 mb-0"></i></a>` );
	}

	static saveEditor( selector )
	{
		$( selector + " > .html-save-button" ).remove( );

		let html = $( selector + " > .summernote-element" ).summernote( "code" );
		$( selector + " > .summernote-element" ).summernote( "destroy" );

		$( selector + " > .summernote-element" ).remove( );
		$( selector ).html( html );

		$( selector ).append( `<a class="html-edit-button" href="javascript:;" onclick="Libraries.HTML.openEditor( '` + selector + `' )"><i class="fas fa-edit h3 mb-0"></i></a>` );

		Libraries.HTML.set( selector, html );
	}

	static set( key, content )
	{
		$.post( "libraries/html-set", { key: key, content: content } );
	}
};

// -----------------------------------------------------------------------------------------
// Function: autoResizeTextarea
// Abstract: Set the specified textarea to resize on input
// -----------------------------------------------------------------------------------------
function autoResizeTextarea( textarea )
{
	function OnInput() {
		this.style.height = 'auto';
		this.style.height = ( this.scrollHeight ) + 'px';
	}

	textarea.setAttribute( 'style', 'height:' + ( textarea.scrollHeight ) + 'px;overflow-y:hidden;' );
	textarea.addEventListener( "input", OnInput, false );
}

// -----------------------------------------------------------------------------------------
// Function: clearQueryString
// Abstract: Clear the query string from the URL in the browser
// -----------------------------------------------------------------------------------------
function clearQueryString( ) {
	var url = window.location.href;

	if( url.indexOf( "?" ) != -1 ) {
		var resUrl = url.split( "?" );

		if( typeof window.history.pushState == 'function' ) {
			window.history.pushState( { }, null, resUrl[0] );
		}
	}
}

// -----------------------------------------------------------------------------------------
// Function: removeFromQueryString
// Abstract: Remove the specified parameter from the query string in the URL in the browser
// -----------------------------------------------------------------------------------------
function removeFromQueryString( ...parameters ) {
	var url = window.location.href;
	var resUrl = "";
	var data = getURLData( url );
	if( url.indexOf( "?" ) !== -1 ) {
		resUrl = url.split( "?" );
	}

	for( var i = 0; i < parameters.length; i++ )
	{
		var parameter = parameters[ i ];

		delete data[ parameter ];
	}

	if( typeof window.history.pushState === 'function' ) {
		window.history.pushState( { }, null, resUrl[0] + "?" + $.param( data ) );
	}
}

// -----------------------------------------------------------------------------------------
// Function: preventDefault
// Abstract: Prevent the default actions for the specified event
// -----------------------------------------------------------------------------------------
function preventDefault( e ) {
	e = e || window.event;
	if( e.preventDefault )
		e.preventDefault();

	e.returnValue = false;
	return false;
}

// -----------------------------------------------------------------------------------------
// Function: assignMouseWheel
// Abstract: Assign the mouse wheel to the specified element
// -----------------------------------------------------------------------------------------
function assignMouseWheel( strElementId ) {
	// Prevent the default scroll in the element
	if( document.getElementById( strElementId ).addEventListener ) {
		document.getElementById( strElementId ).addEventListener( "DOMMouseScroll", preventDefault, false );
	}
	document.getElementById( strElementId ).onmousewheel = document.onmousewheel = preventDefault;

	// Assign the mousewheel scroll to the element
	var mousewheelevt = ( /Firefox/i.test( navigator.userAgent ) ) ? "DOMMouseScroll" : "mousewheel"; //FF doesn't recognize mousewheel as of FF3.x

	if( document.attachEvent ) //if IE ( and Opera depending on user setting )
	{
		document.attachEvent( "on" + mousewheelevt,
				function( e ) {
					if( $.browser.mozilla )
						intAmount = getWheelDelta( e ) / 2;
					else
						intAmount = getWheelDelta( e );

					$( "#" + strElementId ).scrollTop( $( "#" + strElementId ).scrollTop() - intAmount );
				} );
	}else if( document.addEventListener ) //WC3 browsers
	{
		document.addEventListener( mousewheelevt,
				function( e ) {
					if( $.browser.mozilla )
						intAmount = getWheelDelta( e ) / 2;
					else
						intAmount = getWheelDelta( e );

					$( "#" + strElementId ).scrollTop( $( "#" + strElementId ).scrollTop() - intAmount );
				}, false );
	}
}

// -----------------------------------------------------------------------------------------
// Function: getWheelDelta
// Abstract: Return the mouse wheel delta
// -----------------------------------------------------------------------------------------
function getWheelDelta( e ) {
	var evt = window.event || e; //equalize event object
	var delta = evt.detail ? evt.detail * ( -120 ) : evt.wheelDelta; //check for detail first so Opera uses that instead of wheelDelta
	//alert( delta ); //delta returns +120 when wheel is scrolled up, -120 when down
	return delta;
}

// -----------------------------------------------------------------------------------------
// Function: scrollContent
// Abstract: Scrolls the content of the specified element
// -----------------------------------------------------------------------------------------
function scrollContent( strElementId, intAmount ) {
	if( $.browser.mozilla ) {
		intAmount = intAmount / 2;
	}
	$( "#" + strElementId ).scrollTop( $( "#" + strElementId ).scrollTop() - intAmount );
}

// -----------------------------------------------------------------------------------------
// Function: getURLData
// Abstract: Returns an object containing the data from the query string in the specified URL
// -----------------------------------------------------------------------------------------
function getURLData( url ) {
	var queryStart = url.indexOf( "?" ) + 1;
	var queryEnd = url.indexOf( "#" ) + 1 || url.length + 1;
	var query = url.slice( queryStart, queryEnd - 1 );
	var pairs = query.replace( /\+/g, " " ).split( "&" );
	var object = { };
	var name, value, pair;


	if( query === url || query === "" )
		return null;

	for( var i = 0; i < pairs.length; i++ ) {
		pair = pairs[i].split( "=", 2 );
		name = decodeURIComponent( pair[0] );
		value = decodeURIComponent( pair[1] );

		if( object.hasOwnProperty( name ) ) {

			if( typeof ( object[name] ) != "array" )
				object[name] = [ object[name] ];

			object[name].push( pair.length === 2 ? value : null );

		}else {
			object[name] = value;
		}
	}

	return object;
}

// -----------------------------------------------------------------------------------------
// Function: numberSign
// Abstract: Returns the sign of the number
// -----------------------------------------------------------------------------------------
function numberSign( x ) {
	return typeof x === 'number' ? x ? x < 0 ? -1 : 1 : x === x ? 0 : NaN : NaN;
}

// -----------------------------------------------------------------------------------------
// Function: setCookie
// Abstract: Set the cookie with the given name to the given value
// -----------------------------------------------------------------------------------------
function setCookie( strCookieName, strValue ) {
	document.cookie = strCookieName + "=" + strValue + ";expires=Thu, 01 Jan 2020 00:00:01 GMT;path=/";
}

// -----------------------------------------------------------------------------------------
// Function: deleteCookie
// Abstract: Delete the cookie with the given name
// -----------------------------------------------------------------------------------------
function deleteCookie( strCookieName ) {
	document.cookie = strCookieName + "=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/";
}

// -----------------------------------------------------------------------------------------
// Function: getCookie
// Abstract: Read the value of the cookie with the given name
// -----------------------------------------------------------------------------------------
function getCookie( strName ) {
	var strNameEquals = strName + "=";
	var astrCookies = document.cookie.split( ';' );
	for( var i = 0; i < astrCookies.length; i++ ) {
		var strCookie = astrCookies[i];
		while( strCookie.charAt( 0 ) == ' ' )
			strCookie = strCookie.substring( 1, strCookie.length );
		if( strCookie.indexOf( strNameEquals ) == 0 )
			return strCookie.substring( strNameEquals.length, strCookie.length );
	}

	return null;
}

// -----------------------------------------------------------------------------------------
// Function: loadSVG
// Abstract: Fade the text out
// -----------------------------------------------------------------------------------------
function loadSVG( Element ) {
	var strID = $( Element ).attr( "id" );
	var strClass = $( Element ).attr( "class" );
	var strUrl = $( Element ).attr( "src" );

	$.get( strUrl, function( strData ) {
		// Get the SVG tag, ignore the rest
		var $SVGElement = $( strData ).find( "svg" );

		// Add replaced image's ID to the new SVG
		if( typeof strID !== "undefined" ) {
			$SVGElement = $SVGElement.attr( "id", strID );
		}

		// Add replaced image's classes to the new SVG
		if( typeof strClass !== "undefined" ) {
			$SVGElement = $SVGElement.attr( "class", strClass );
		}

		// Remove any invalid XML tags as per http://validator.w3.org
		$SVGElement = $SVGElement.removeAttr( "xmlns:a" );

		// Replace image with new SVG
		$( Element ).replaceWith( $SVGElement );
	}, "xml" );
}

// -----------------------------------------------------------------------------------------
// Function: checkBrowser
// Abstract: Check the name of the browser
// -----------------------------------------------------------------------------------------
navigator.checkBrowser = ( function() {
	var ua = navigator.userAgent,
			tem,
			M = ua.match( /(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i ) || [ ];
	if( /trident/i.test( M[1] ) ) {
		tem = /\brv[ :]+(\d+)/g.exec( ua ) || [ ];
		return 'IE ' + ( tem[1] || '' );
	}
	if( M[1] === 'Chrome' ) {
		tem = ua.match( /\b(OPR|Edge)\/(\d+)/ );
		if( tem != null )
			return tem.slice( 1 ).join( ' ' ).replace( 'OPR', 'Opera' );
	}
	M = M[2] ? [ M[1], M[2] ] : [ navigator.appName, navigator.appVersion, '-?' ];
	if( ( tem = ua.match( /version\/(\d+)/i ) ) != null )
		M.splice( 1, 1, tem[1] );
	return M.join( ' ' );
} )();

// -----------------------------------------------------------------------------------------
// Function: preventContextMenu
// Abstract: Prevent the user from opening the context menu on the specified element
// -----------------------------------------------------------------------------------------
function preventContextMenu( Element ) {
	if( Element.addEventListener ) {
		Element.addEventListener( "contextmenu", function( e ) {
			e.preventDefault();
		}, false );
	}else {
		Element.attachEvent( "oncontextmenu", function() {
			parent.window.event.returnValue = false;
		} );
	}
}