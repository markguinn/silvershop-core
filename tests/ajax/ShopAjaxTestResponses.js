var TestResponses = {
	generic: {
		status: 200,
		responseText: 'ok'
	},

	events: {
		status: 200,
		responseText: JSON.stringify({
			__events__: {
				event1: 1,
				event2: ['a','b','c']
			}
		})
	},

	pushRegion: {
		status: 200,
		responseText: JSON.stringify({
			SideCart: '<div id="region1">Replaced:1</div>'
		})
	},

	pushManyRegions: {
		status: 200,
		responseText: JSON.stringify({
			SideCart: '<div id="region1">Replaced:1</div>',
			Other:    '<div class="replaceme">Replaced:<span>2+3</span></div>'
		})
	},

	pullLocal: {
		status: 200,
		responseText: JSON.stringify({
			Test2: '<div class="replaceme">Replaced:2</div>'
		})
	},

	pullDataAttribute: {
		status: 200,
		responseText: JSON.stringify({
			Test3: '<div class="replaceme">Replaced:3</div>'
		})
	},
};
