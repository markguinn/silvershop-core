(function($) {
	describe('Shop Ajax', function(){
		describe('links', function(){
			beforeEach(function(){
				setFixtures(
					'<a id="link1" href="/link1" class="ajax">Test Button 1</a>' +
					'<a id="link2" href="/link2" data-target="ajax">Test Button 2</a>' +
					'<a id="link3" href="/link3" data-target="modal">Test Button 3</a>' +
					'<a id="link4" href="/link4">Test Button 4</a>'
				);
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
				//expect($(document.body)).not.toHaveClass('ajax-loading');
				expect($('#link1')).not.toHaveClass('ajax-loading');
				$('#link1').click();
				var request = mostRecentAjaxRequest();
				//expect($(document.body)).toHaveClass('ajax-loading');
				expect($('#link1')).toHaveClass('ajax-loading');
				request.response(TestResponses.generic);
				//expect($(document.body)).not.toHaveClass('ajax-loading');
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

				$.ajax({url:'http://' + document.location.host + '/cart'});
				request = mostRecentAjaxRequest();
				expect(request.requestHeaders['X-Pull-Regions']).toBe('SideCart');

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

		describe('ajax errors', function(){

		});
	});
}(jQuery));
