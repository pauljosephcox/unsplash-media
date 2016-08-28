// ------------------------------------
//
// Unsplash Admin Connector
//
// ------------------------------------

jQuery(document).ready(function($) {

	var Unsplash = {

		// ------------------------------------
		// Initialize
		// ------------------------------------

	    init: function() {


	    	alert("Unsplash Connector");

	    }

	}

	// ------------------------------------
	// Go
	// ------------------------------------

	if($('.unsplash-media-library').length > 0){


		window.Unsplash = Unsplash;

		Unsplash.init();

	}



});
