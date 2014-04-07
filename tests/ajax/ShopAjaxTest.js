(function($) {
	var TestResponses = {
		events: {
			status: 200,
			responseText: JSON.stringify({
				__events__: {
					event1: 1,
					event2: ['a','b','c']
				}
			})
		}
	};

	describe('Shop Ajax', function(){
		describe('links', function(){
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
				// TODO
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
			it('should replace a region on the page when the response includes region codes', function(){

			});

			it('should replaces multiple regions if needed', function(){

			});

			it('should add a X-Pull-Region header when requests to certain urls are made', function(){

			});

			it('should add an ajaxWatch plugin for a specific node', function(){

			});

			it('should recognize data-ajax-watch and data-ajax-render attributes', function(){

			});
		});

		describe('ajax errors', function(){

		});
	});
}(jQuery));
