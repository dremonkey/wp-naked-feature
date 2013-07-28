/**
 * Retrieves features data
 *
 * @see
 *	backbone.js and underscore.js
 */

// create closure
(function($) {

	$.fn.naked_feature = function(options)
	{
		// Extend our default options with those provided.
		var opts = $.extend({}, $.fn.naked_feature.defaults, options);


		/****************************************************************
		 * The Model
		 */

		// Set up the model to be used for each feature item
		window.Feature = Backbone.Model.extend({

			defaults: {
				post_id: '',
				post_title: '',
				post_type: '',
				pub_date: '',
				pub_time: '',
				guid: '' // url of the post/content
			},

			initialize: function(data)
			{
				if( opts.debug ) {
					console.log( data );
				}
			}

		});


	  	/****************************************************************
		 * The Collection
		 */

	  	// Set up the collection
		window.Features = Backbone.Collection.extend({
			model: Feature,
			url: opts.data_url,

			initialize: function()
			{
				if( opts.debug ) {
					console.log( opts.data_url );
				}
			},

			parse: function(response)
			{
				if( opts.debug ) {
					console.log( response );
				}
				return response.features;
			}

		});


		/****************************************************************
		 * The Views
		 */

		window.FeatureView = Backbone.View.extend({
			template: opts.item_tpl,
			tagName: opts.item_element_tag,

			initialize: function() 
		    {
		      this.initializeTemplate();
		    },

		    initializeTemplate: function() 
		    {
		    	// Grabs the template html by ID. Can also use this to set up a template engine to use. Will be called later to populate the view with data console.log( this.template );
		      	this.template = _.template($(this.template).html());
		    },

		    render: function() 
		    {
		      	var renderedContent = this.template(this.model.toJSON());
		      	$(this.el).html(renderedContent);

		      	// return itself to allow you to chain other calls to render
		      	return this;
		    }

		});


		// prepare the collections view
		window.FeaturesView = Backbone.View.extend({
			initialize: function()
			{
				// bindAll is used to permanently associate methods (callbacks) with a specific object
	      		_.bindAll(this, 'render');

	      		this.collection.bind('reset', this.render);
			},

			render: function()
			{

		    	var $container = $('#' + this.id).find('.inner'),
		    		$placeholders = $container.find(opts.placeholder),
		    		collection = this.collection;

				/**
				 * The iterator (each) is called with three arguments: 
				 * (element, index, list) however we only need to use two of 
				 * them so only two are declared
				 */
		    	var features = [];
	     		collection.each(function(feature, index ) {

		     		// stop the loop early if their are no more placeholders to be filled this happens when the feature_section can hold more features than is actually displayed at one time
		     		if (index > $placeholders.length - 1)
		     			return false;

		     		var i = index,
		     				$ph = $($placeholders.get(i)),
		     				img_src = get_img_src($ph, feature);

		     		var classname = opts.placeholder.substring(1);

		     		var classes = $ph.attr('class').replace(classname, '');
		     		classes = 'feature ' + classes;

		     		feature.set({ img_src: img_src });

					var view = new FeatureView({
					  model: feature,
					  collection: collection,
					  id: opts.item_id_prefix + feature.get('post_id'),
					  className: classes
					});

					// append the view to the page
					var el = view.render().el,
						$el = $(el),
						pos = $ph.position(),
						dur = Math.random() * (1000 - 600) + 600;

					var top = pos.top;
					var left = pos.left;

					$el.css({
						position: 'absolute',
						display: 'none',
						top: top,
						left: left
					});

					$container.append($el);
					$el.fadeIn(dur);
	      		});

				// failsafe to make sure that the features are visible because sometimes after a layout change the previous fadeIn doesn't fire properly...
				// @todo find a better solution for this
				$container.find('.feature').each(function() {
					if ($(this).is(':hidden')) {
						$(this).fadeIn();
					}
				});

		    	// return itself to allow you to chain other calls to render
		    	return this;
			}

		});


		/**
		 * Determines which image source size to retrieve
		 */
		function get_img_src($placeholder, feature) 
		{
			var width = $placeholder.width();
			var height = $placeholder.height();

			if( opts.debug ) {
				console.log(width, height);
			}

			/**
			 * loop through all the available img sizes and find the image 
			 * that matches the dimensions of the placeholder the best
			 */
			var best_match = {
				points: 0,
				src: ''
			};

			if (null == feature.get('img'))
				return;

			var sizes = feature.get('img').sizes;
			for (var key in sizes) {
				var img = sizes[key];
				var points = 0;

				/**
				 * for each point calculation 1 should be the upper limit 
				 * therefore if an image is a perfect match then width = 1 
				 * point, height = 1 point so total points = 2
				 */
				points += img.width >= width ? width / img.width : img.width / width;
				points += img.height >= height ? height / img.height : img.height / height;

				if( opts.debug ) {
					console.log(points, img.src);
				}

				if (points > best_match.points) {
					best_match.points = points;
					best_match.src = img.src;
				}
			}

			if( opts.debug ) {
				console.log( best_match.src );
			}

			return best_match.src;
		}


		/**
		 * Removes all existing features from every section from the 
		 * page before rendering the new ones
		 */
		function remove_features()
		{
			
			$('.features').each(function() {

				// only if placeholders exist (i.e. ajax loading is being used)
				$ph = $(this).find('.placeholder');
				if($ph.length > 0) {
					$features = $(this).find('.feature');

					if ($features.length != 0) {
			    	$features.fadeOut('fast', function() {
			    		$(this).remove();
			    	});
				  }
				}
			});
		}


		// Create a Features Collection instance
		window.features = new Features();

		// iterate over each matched element
	  return this.each(function() 
	  {
	  	if (!opts.data_url || !opts.fetch_data) {
	  		alert('This has not been setup correctly. You must set a data url.');
	  		return false;
	  	}

	  	// remove all existing features
	  	remove_features();

	  	featuresView = new FeaturesView({
				collection: features,
				id: $(this).attr('id')
			});

	  	/**
		 * fetch() will grab the data for our collection form the server.
		 *
		 *	For more info see:
		 *		- http://documentcloud.github.com/backbone/#Collection-fetch
		 *		- http://api.jquery.com/jQuery.ajax/
		 */
	  	window.features.fetch({
				data: opts.fetch_data
				// add: true
			});

	  });

	}


	$.fn.naked_feature.defaults = {
		/**
		 * If true will output debug info
		 */
		'debug' : false,
		/**
		 * The url where the json data used to build the collection is to be fetched
		 */
		'data_url' : '',
		/**
		 * The jquery ajax data object to use when data is fetched. This needs to be an
		 * object literal - {}
		 */
		'fetch_data' : null,
		/**
		 * The selector (class) for the feature item placeholder(s)
		 */
		'placeholder' : '.placeholder',
		/**
		 * The id of the template to use to render individual item views within a collection
		 */
		'item_tpl' : '#feature-item-tpl',
		/**
		 * The prefix that should be used to construct the element id for each feature item. This
		 * prefix will be prepended to the feature post_id so if the post_id for the item is '5'
		 * and the item prefix is 'feature-' the resulting element id would be 'feature-5'
		 */
		'item_id_prefix' : 'feature-',
		/**
		 * The feature item element tag type. This will be used as the element tag type for each
		 * feature item view that is rendered. This can be any valid html element tag but 'article'
		 * or 'div' is recommended
		 */
		 'item_element_tag' : 'article'
	};

})(jQuery);


/**
 * Load the feature
 *
 * Not sure if this is the best place for this code
 */

// closure
(function($) {

	/**
	 * Convenience function to load all features sections
	 */
	function render_features()
	{		
		$('.feature-section').each(function() {
			// only if placeholders exist (i.e. ajax loading is being used)
			$ph = $(this).find('.placeholder');
			if($ph.length > 0) {
				var section = $(this).attr('id').replace('-features', ''),
						api_url = naked_feature_vars.json_api_url;

				$(this).naked_feature({
					'data_url' : api_url + 'feature/get_features/',
					'fetch_data' : { 'section' : section }
				});
			}
		});
	}


	// Document Ready
	$(function() {

		var width = $(window).width();

		render_features();

		$(window).smartresize(function() {

			var debug = false;
			var new_width = $(this).width();

			if( debug ) {
				console.log('width: ' + width, 'new width: ' + new_width);
			}

			//
			// reload features
			//
			// going from smaller than 1280 layout to 1280 layout
			if (width < 1280 && new_width >= 1280) {
				render_features();
			}
			// going from less than 960 layout to a 960 layout
			else if (width < 640 && new_width >= 640 && new_width < 1280) {
				render_features();
			}
			// going from 1280 layout to 960 layout
			else if (width >= 1280 && new_width < 1280 && new_width >= 640) {
				render_features();
			}
			// going from 960 or 1280 layout to single column layout
			else if (width >= 640 && new_width < 640) {
				render_features();
			}

			// record the current width
			width = new_width;
		});

	});

})(jQuery);
