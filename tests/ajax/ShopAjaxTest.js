(function($) {
	describe('Shop Ajax', function(){

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		describe('links', function(){
			beforeEach(function(){
				setFixtures(
					'<a id="link1" href="/link1" class="ajax">Test Button 1</a>' +
					'<a id="link2" href="/link2" data-target="ajax">Test Button 2</a>' +
					'<a id="link3" href="/link3" data-target="modal">Test Button 3</a>' +
					'<a id="link4" href="/link4">Test Button 4</a>'
				);

				$(document.body).removeClass('ajax-loading');
			});

			it('should make an ajax request given a.ajax', function(){
				$('#link1').click();
				var request = mostRecentAjaxRequest();
				expect(request.url).toMatch(/link1/);
			});

			it('should make an ajax request given a[data-target=ajax]', function(){
				$('#link2').click();
				var request = mostRecentAjaxRequest();
				expect(request.url).toMatch(/link2/);
			});

			it('should add the ajax-loading class to the link and the body and take it away when completed', function(){
				// NOTE: it crashes my browser to use expect($('body')).toHaveClass(...) - or any variation i tried
				expect($('#link1')).not.toHaveClass('ajax-loading');
				expect(document.body.className).not.toMatch(/ajax-loading/);
				$('#link1').click();
				var request = mostRecentAjaxRequest();
				expect($('#link1')).toHaveClass('ajax-loading');
				request.response(TestResponses.generic);
				expect($('#link1')).not.toHaveClass('ajax-loading');
			});

			it('should make an ajax request and open the result in a modal given a[data-target=modal]', function(){
				// TODO (this may not make the first iteration)
			});

			it('should not arm other links', function(){
				$('#link4').click();
				expect(mostRecentAjaxRequest()).toBeNull();
			});
		});

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		describe('forms', function(){
			beforeEach(function(){
				setFixtures(
					'<form id="form1" class="ajax" method="post" action="/contact">' +
						'<input type="hidden" name="the" value="quick">' +
						'<input type="text" name="brown" value="fox">' +
						'<input type="checkbox" name="jumps" value="over" checked>' +
						'<input type="checkbox" name="notpresent" value="hopefully">' +
						'<textarea name="thelazy">DOG</textarea>' +
						'<select name="natural"><option>selection</option><option selected>woman</option></select>' +
						'<input id="form1submit" type="submit" name="right" value="Submit">' +
						'<input type="submit" name="wrong" value="No touching">' +
					'</form>' +
					'<form id="form2" data-target="ajax" method="post" action="/contact">' +
						'<input type="text" name="brown" value="weasel">' +
						'<input id="form2submit" type="submit" name="right" value="Submit">' +
					'</form>' +
					'<form id="form3" method="post" action="/contact">' +
						'<input type="text" name="brown" value="badger">' +
						'<input id="form2submit" type="submit" name="right" value="Submit">' +
					'</form>'
				);
			});

			it('should add ajax-loading to both the form and the button and remove both when the request finishes', function(){
				expect($('#form1')).not.toHaveClass('ajax-loading');
				expect($('#form1submit')).not.toHaveClass('ajax-loading');

				$('#form1submit').trigger('click');
				var request = mostRecentAjaxRequest();
				expect($('#form1')).toHaveClass('ajax-loading');
				expect($('#form1submit')).toHaveClass('ajax-loading');
				expect($('#form2')).not.toHaveClass('ajax-loading');

				request.response(TestResponses.generic);
				expect($('#form1')).not.toHaveClass('ajax-loading');
				expect($('#form1submit')).not.toHaveClass('ajax-loading');
				expect($('#form2')).not.toHaveClass('ajax-loading');
			});

			it('should ajaxify forms with the class "ajax"', function(){
				$('#form1').trigger('submit');
				var request = mostRecentAjaxRequest();
				expect(request.params).toBe('the=quick&brown=fox&jumps=over&thelazy=DOG&natural=woman');
				expect(request.method).toBe('POST');
				expect(request.url).toMatch(/contact$/);
				request.response(TestResponses.generic);
			});

			it('should include the submit button if used', function(){
				$('#form1submit').trigger('click');
				var request = mostRecentAjaxRequest();
				expect(request.params).toBe('the=quick&brown=fox&jumps=over&thelazy=DOG&natural=woman&right=Submit');
				request.response(TestResponses.generic);
			});

			it('should ajaxify forms with data-target="ajax"', function(){
				$('#form2').trigger('submit');
				var request = mostRecentAjaxRequest();
				expect(request.params).toBe('brown=weasel');
				request.response(TestResponses.generic);
			});
		});

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		describe('triggered events', function(){
			var request, spyEvent2;

			beforeEach(function(){
				$.ajax({url:'/'});
				request = mostRecentAjaxRequest();
				spyOnEvent(document, 'event1');
				spyEvent2 = jasmine.createSpy('event2handler');
				$(document).on('event2', spyEvent2);
			});

			it('should trigger an event on the document when the response includes __events__', function(){
				request.response(TestResponses.events);
				expect('event1').toHaveBeenTriggeredOn(document);
				expect(spyEvent2.mostRecentCall.args[1]).toEqual(['a','b','c']);
			});
		});

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		describe('regions', function(){
			beforeEach(function(){
				setFixtures(
					'<div id="region1">Untouched:1</div>' +
					'<div id="region2" class="replaceme">Untouched:2</div>' +
					'<div id="region3" class="replaceme">Untouched:3</div>'
				);
			});

			it('should replace a region on the page when the response includes region codes', function(){
				$.ajax({url:'/'});
				var request = mostRecentAjaxRequest();
				request.response(TestResponses.pushRegion);
				expect($('#region1').html()).toBe('Replaced:1');
			});

			it('should replaces multiple regions if needed', function(){
				$.ajax({url:'/'});
				var request = mostRecentAjaxRequest();
				request.response(TestResponses.pushManyRegions);
				expect($('#region2').html()).toBe('Replaced:<span>2+3</span>');
				expect($('#region3').html()).toBe('Replaced:<span>2+3</span>');
			});
		});

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		describe('ajax pull', function(){
			beforeEach(function(){
				setFixtures(
					'<div id="region1">Untouched:1</div>' +
					'<div id="region2" class="replaceme">Untouched:2</div>' +
					'<div id="region3" class="replaceme" data-ajax-watch="/datawatch" data-ajax-region="Test3">Untouched:3</div>'
				);

				$(document).pullRegionForURL({
					'/cart':    'SideCart',
					'/other':   ['abc', 'def'],
					'/wild/*':  'test',
					'http://otherdomain.com/test1/': 'test2'
				});

				$('#region2').pullRegionForURL({
					'/localwatch': 'Test2'
				})

				// this will cause the data-ajax-watch to get picked up
				$(window).trigger('load');
			});

			afterEach(function(){
				$(document).clearPullRegions();
			});

			it('should add a X-Pull-Region header when requests to matching urls are made', function(){
				$.ajax({url:'/cart'});
				var request = mostRecentAjaxRequest();
				expect(request.requestHeaders['X-Pull-Regions']).toBe('SideCart');
			});

			it('should not add the header for non-matching urls', function(){
				$.ajax({url:'/'});
				var request = mostRecentAjaxRequest();
				expect(request.requestHeaders['X-Pull-Regions']).toBeUndefined();
			});

			it('should handle multiple regions at once', function(){
				$.ajax({url:'/other'});
				var request = mostRecentAjaxRequest();
				expect(request.requestHeaders['X-Pull-Regions']).toBe('abc,def');
			});

			it('should match wildcards in the url', function(){
				$.ajax({url:'/wild/cart/url'});
				var request = mostRecentAjaxRequest();
				expect(request.requestHeaders['X-Pull-Regions']).toBe('test');
			});

			it('should ignore the query string and hash', function(){
				$.ajax({url:'/cart?test=1#test2'});
				var request = mostRecentAjaxRequest();
				expect(request.requestHeaders['X-Pull-Regions']).toBe('SideCart');
			});

			it('should handle the hostname and protocol intelligently', function(){
				var request;

				$.ajax({url:'http://otherdomain.com/cart'});
				request = mostRecentAjaxRequest();
				expect(request.requestHeaders['X-Pull-Regions']).toBeUndefined();

				// this isn't present on phantomjs (travis)
				if (document.location.host) {
					$.ajax({url:'http://' + document.location.host + '/cart'});
					request = mostRecentAjaxRequest();
					expect(request.requestHeaders['X-Pull-Regions']).toBe('SideCart');
				}

				$.ajax({url:'/test1/'});
				request = mostRecentAjaxRequest();
				expect(request.requestHeaders['X-Pull-Regions']).toBeUndefined();

				$.ajax({url:'http://otherdomain.com/test1/'});
				request = mostRecentAjaxRequest();
				expect(request.requestHeaders['X-Pull-Regions']).toBe('test2');
			});

			it('should replace the node the watch was set on if not set on the document', function(){
				$.ajax({url:'/localwatch'});
				var request = mostRecentAjaxRequest();
				expect(request.requestHeaders['X-Pull-Regions']).toBe('Test2');
				request.response(TestResponses.pullLocal);
				expect($('#region2').html()).toBe('Replaced:2');
				expect($('#region3').html()).not.toBe('Replaced:2');
			});

			it('should recognize data-ajax-watch and data-ajax-region attributes', function(){
				$.ajax({url:'/datawatch'});
				var request = mostRecentAjaxRequest();
				expect(request.requestHeaders['X-Pull-Regions']).toBe('Test3');
				request.response(TestResponses.pullDataAttribute);
				expect($('#region2').html()).not.toBe('Replaced:3');
				expect($('#region3').html()).toBe('Replaced:3');
			});
		});

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		describe('ajax errors', function(){

		});
	});
}(jQuery));
