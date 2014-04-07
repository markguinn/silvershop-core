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
			$.ajax({
				url: this.href
			});

			return false;
		});

		// handle ajax responses
		$(document).ajaxComplete(function(event, xhr, ajaxOptions){
			try {
				var data = $.parseJSON(xhr.responseText);
				if (data != null && typeof(data) == 'object') {
					for (var key in data) {
						// FIXME: this actually needs to happen after all regions are done
						if (key === '__events__' && typeof(data[key]) === 'object') {
							for (var eventName in data[key]) {
								$(document).trigger(eventName, [data[key][eventName]]);
							}
						} else {
							// TODO: update region
						}
					}
				}
			} catch(e) {}
		});
	});

}(jQuery, this, this.document));
