/**
 * Overall javascript framework for shop module.
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 04.03.2014
 * @package shop
 * @subpackage javascript
 */
(function ($, window, document, undefined) {

	$(document).ready(function(){
		// handle automatic ajax links
		$(document).on('click', 'a.ajax, a[data-target=ajax]', function(){
			var $link = $(this).addClass('ajax-loading');

			$.ajax({
				url: this.href,
				complete: function(){
					$link.removeClass('ajax-loading');
				}
			});

			return false;
		});

		// handle ajax responses
		$(document)
			.ajaxComplete(function(event, xhr, ajaxOptions){
				var data = null;

				try {
					data = $.parseJSON(xhr.responseText);
				} catch(e) {}

				if (data != null && typeof(data) == 'object') {
					for (var key in data) {
						// FIXME: this actually needs to happen after all regions are done
						// FIXME: regions probably need their own key, just in case there are other kinds of ajax going on
						if (key === '__events__' && typeof(data[key]) === 'object') {
							for (var eventName in data[key]) {
								$(document).trigger(eventName, [data[key][eventName]]);
							}
						} else if (typeof(data[key]) === 'string') {
							var $region  = $(data[key]),
								explicit = $('[data-ajax-region=' + key + ']'),
								id       = $region.length > 0 ? $region.prop('id') : '',
								classes  = ($region.length > 0 && $region[0].className)
									? $region[0].className.replace(/^\s|\s$/, '').split(/\s+/)
									: [];

							if (explicit.length > 0) {
								// If there is one (or more) element with a data-ajax-region attribute it
								// means we know for sure it's a match to this region, usually because the
								// watch was set up on that particular element.
								explicit.html( $region.html() );
							} else if (id) {
								// second best is if the root element of the new content contains an id
								$('#'+id).html( $region.html() );
							} else if (classes.length > 0) {
								// otherwise, we try to match by css classes
								$('.'+classes.join('.')).html( $region.html() );
							} else {
								// finally we fail silently but leave a warning for the developer
								if (typeof(console) != 'undefined' && typeof(console.warn) == 'function') {
									console.warn('Region returned without class or id!');
								}
							}
						}
					}
				}
			})
			.ajaxStart(function(){
				$(document.body).addClass('ajax-loading');
			})
			.ajaxStop(function(){
				$(document.body).addClass('ajax-loading');
			})
		;

		// handle ajax pulls
		// TODO: This should probably be pulled out into a separate file
		var pullWatches = {};

		/**
		 * Converts url to an absolute url among other things.
		 * @param {string} url
		 * @returns {string}
		 */
		var normaliseURL = function(url) {
			return $('<a></a>').prop('href', url).prop('search', '').prop('hash', '').prop('href').replace('?#', '');
		};

		/**
		 * Adds a watch to the existing list.
		 * Cleans up the url pattern to make it easier to match.
		 * @param {string} url
		 * @param {string} region
		 * @param {jQuery} target
		 */
		var addWatch = function(url, region, target) {
			url = normaliseURL(url);
			if (typeof(pullWatches[url]) == 'undefined') pullWatches[url] = [];
			pullWatches[url].push(region);

			// If the target is not document, set data-ajax-region attribute
			// so it is cemented as the recipient of that region
			if (target && target.length == 1 && target[0].nodeName != '#document') {
				target.attr('data-ajax-region', region);
			}
		};

		/**
		 * Checks if the given url matches the pattern.
		 * @param {string} url
		 * @param {string} pattern
		 * @returns {boolean}
		 */
		var doesUrlMatch = function(url, pattern) {
			if (pattern.indexOf('*') > -1) {
				var re = new RegExp(pattern.replace('.', '\\.').replace('*', '.*'));
				return re.test(url);
			} else {
				return url === pattern;
			}
		};

		/**
		 * Adds a watch for ajax requests.
		 *
		 * @param {object|string} urls
		 * @param {string} region [optional]
		 * @returns {*}
		 */
		$.fn.pullRegionForURL = function(urls, region) {
			// this is a more user friendly interface if you only have one url to watch
			if (typeof(urls) == 'string') {
				addWatch(urls, region, this);
				return this;
			}

			if (typeof(urls) == 'object') {
				for (var url in urls) {
					if (typeof(urls[url]) == 'object') {
						for (var k in urls[url]) addWatch(url, urls[url][k], this);
					} else {
						addWatch(url, urls[url], this);
					}
				}
			}
			return this;
		};

		/**
		 * Clear all ajax request watches
		 */
		$.fn.clearPullRegions = function() {
			pullWatches = {};
			return this;
		};

		// Watch the outgoing requests and add headers as needed
		$.ajaxPrefilter(function(options, originalOptions, jqXHR) {
			var regions = [],
				checkUrl = normaliseURL(options.url);

			// see if there are any matches
			for (var urlPattern in pullWatches) {
				if (doesUrlMatch(checkUrl, urlPattern)) {
					regions = regions.concat(pullWatches[urlPattern]);
				}
			}

			// if so, add the appropriate header
			if (regions.length > 0) {
				if (typeof(options.headers) != 'object') options.headers = {};
				options.headers['X-Pull-Regions'] = regions.join(',');
			}
		});
	});

	// Automatically set up pulls by data-ajax-watch
	$(window).on('load', function(){
		$('[data-ajax-watch]').each(function(index, el) {
			$(el).pullRegionForURL(el.getAttribute('data-ajax-watch'), el.getAttribute('data-ajax-region'));
		});
	});

}(jQuery, this, this.document));
