// ------------------------------------
//
// Unsplash Admin Connector
//
// ------------------------------------

jQuery(document).ready(function($) {

	var Unsplash = {

		count: 28,

		page: 1,

		currentRequest: null,

		// ------------------------------------
		// Initialize
		// ------------------------------------

	    init: function() {

	    	// Initial Load
	    	this.get('/api/unsplash/photos/search?per_page='+this.count+'&query=', Unsplash.displayPhotos);
	    	Unsplash.currentRequest = '/api/unsplash/photos/search?per_page='+this.count+'&query=';

	    	// Setup Search
	    	$('.unsplash-search').submit(function(){ Unsplash.search(); return false; });

	    	$('.unsplash-media-library .load-more').click(Unsplash.loadMore);


	    },

	    // ------------------------------------
	    // Search
	    // ------------------------------------

	    search: function(){

	    	// Clear Current Photos
	    	$('.unsplash-photos li').fadeOut().delay(1000).remove();
	    	$('.unsplash-media-library .load-more').fadeOut();

	    	var value = $('.unsplash-search-text').val();

	    	// Add Loading Message
	    	$('.unplash-message').text('Searching for ' + value).fadeIn();

	    	// Query Unsplash
	    	this.get('/api/unsplash/photos/search?per_page='+this.count+'&query='+encodeURI(value), Unsplash.displayPhotos);
	    	Unsplash.currentRequest = '/api/unsplash/photos/search?per_page='+this.count+'&query='+encodeURI(value);

	    	return false;

	    },

	    // ------------------------------------
	    // Load More
	    // ------------------------------------

	    loadMore: function(e){

	    	e.preventDefault();

	    	$(this).fadeOut();

	    	Unsplash.page++;

	    	Unsplash.get(Unsplash.currentRequest+'&page='+Unsplash.page, Unsplash.displayPhotos);

	    },

	    // ------------------------------------
	    // Get
	    // ------------------------------------

	    get: function(path, cb){

	    	$.ajax({
				method: "GET",
			  	url: path,
			  	dataType: 'json',
			  	success : function( data ) {
			  		cb(data);
			  	}
			});

	    },

	    // ------------------------------------
	    // Display Photos
	    // ------------------------------------

	    displayPhotos: function(data){

	    	$('.unplash-message').fadeOut();

	    	for(var i in data){

	    		Unsplash.renderPhoto(data[i]);

	    	}

	    	if(data.length < Unsplash.count){
	    		$('.unsplash-media-library .load-more').fadeOut();
	    	} else {
	    		$('.unsplash-media-library .load-more').fadeIn();
	    	}

	    },

	    // ------------------------------------
	    // Render Photo
	    // ------------------------------------

	    renderPhoto: function(data){


	    	var $photo = $('<img>');
	    	$photo.attr('src', data.urls.thumb);

	    	var $li = $('<li tabindex="0" role="checkbox" class="attachment save-ready"></li>');
	    	var $preview = $('<div class="attachment-preview js--select-attachment type-image subtype-jpeg landscape"></div>');
	    	var $thumbnail = $('<div class="thumbnail"></div>');
	    	var $centered = $('<div class="centered"></div>');

	    	// Attach everything
	    	$centered.append($photo);
	    	$thumbnail.append($centered);
	    	$preview.append($thumbnail);
	    	$li.append($preview);

	    	// Set Data:
	    	$li.data('photo',data.urls.full);
	    	$li.data('credit',data.user.name);
	    	$li.data('title',data.id);


	    	// Add Events
	    	$li.click(Unsplash.selectPhoto);

	    	// Push to screen
	    	$('.unsplash-photos').append($li);


	    },

	    // ------------------------------------
	    // Select Photo
	    // ------------------------------------

	    selectPhoto: function(e){

	    	var $photo = $(this);
	    	$photo.css('opacity','0.75');

	    	$message = $('<div class="message" style="position:absolute; bottom:1em; left:0em; width:100%; ">Importing...</div>');
	    	$photo.append($message);

	    	var args = {};
	    	args.photo = $photo.data('photo');
	    	args.credit = $photo.data('credit');
	    	args.title = $photo.data('title');
	    	args._wpnonce = $('#_wpnonce').val();
	    	args._wp_http_referer = $('#_wp_http_referer').val();
	    	args.unsplash_media_actions = 'import';

	    	// Import Photo
	    	$.ajax({
				method: "POST",
			  	url: "/api/unsplash/import",
			  	dataType: 'json',
			  	data: args,
			  	success : function( data ) {

			  		if(data.success == 'imported'){

			  			$message.text('Finished');
			  			setTimeout(function(){ $photo.fadeOut(); }, 2000);

			  		}

			  	}
			});


	    },



	}

	// ------------------------------------
	// Go
	// ------------------------------------

	if($('.unsplash-media-library').length > 0){


		window.Unsplash = Unsplash;

		Unsplash.init();

	}



});
