# Ajax Framework

Shop has some built-in ajax functionality. It has the following goals/requirements:

- Progressive enhancement is a must (i.e. everything should work with or without js)
- Following wider SS guidelines here: http://doc.silverstripe.com/framework/en/topics/javascript
  and http://doc.silverstripe.org/framework/en/trunk/misc/coding-conventions
- Should be easy (ideally automatic) to make the output of a controller action consumable
- Server can trigger various behaviours in response to a request
  * open new modal (e.g. requiring login to add to wishlist)
  * close modal (e.g. after registration, login, add to cart)
  * redirect page (e.g. to a landing page after ajax login)
  * replace multiple page elements (e.g. sidecart, buttons)
  * trigger jquery events on the document (or some other pub/sub system)


## Client Side

### Link Behaviours:

- Ajaxify link: class="ajax" or data-target="ajax"
- Open in modal: data-target="modal" (not implemented yet)
- While loading, the link and the document.body will have the class ajax-loading added (and removed when loading finished)

### Updating Regions

The server can push different regions of the page (denoted by SS template name). The client can also "pull"
regions by adding an X-Pull-Regions header or __regions__ GET parameter to the request. The easiest way to
do so is with the "pullRegionForURL" jQuery plugin or the data-ajax-watch and data-ajax-region attributes.

```js
$(document).pullRegionForURL({
	'/shoppingcart/*':    'SideCart',
	'/shoppingcart/add/': 'SomeOtherTemplate',
});
```

Or using data attributes:

```html
<div data-ajax-watch="/shoppingcart/*" data-ajax-region="SideCart">
	<h1>Side Cart</h1>
</div>
```

In each of these cases, any time an ajax request is to a url that starts with '/shoppingcart/' is made, it
will automatically have a header added: `X-Pull-Regions: SideCart` which will cause AjaxHTTPResponse to
automatically render the SideCart.ss template and return it in the response. The ajax framework will also
detect this region in the response and replace it. The following criteria are used for replacement:

1. If one or more regions have a data-ajax-region="SideCart" attribute, it/they will be replaced.
2. If the returned html has an id on the root element, it will look for an element on the page with the same id.
3. Finally, if the returned html has CSS classes on the root element it will replace all elements on the page with the
   same classes.
4. If all of the above come up short, it will fail silently.



## Server Side

The suggested usage is:

```php
	function add($request) {
		// Do something...

		if ($request->isAjax()) {
			$response = $this->getAjaxResponse();
			$response->triggerEvent('cartchange');
			$response->pushRegion('SideCart');
			return $response;
		} else {
			return $this->redirectBack(); // or render or whatever
		}
	}
```

Within shop module the above is extracted into an extension. For example:

```php
class ShoppingCart_Controller {
	function add($request) {
		// Do something...

		if ($request->isAjax()) {
			$response = $this->getAjaxResponse();
			$this->extend('updateAddResponse', $response, $request, $success);
			return $response;
		} else {
			return $this->redirectBack(); // or render or whatever
		}
	}
}

class ShoppingCartAjax extends Extension {
	function updateAddResponse($response, $request) {
		$response->triggerEvent('cartchange');
		$response->pushRegion('SideCart');
	}
}
```


## Conventions

In addition to the Silverstripe javascript conventions:

- Organize modules according to features (e.g. “add to cart button” over “product detail page”)
- Entwine should use the 'shop' namespace (see https://github.com/hafriedlander/jquery.entwine/tree/master#namespaces)
  or 'shop.xxxxx' for modules that want to
- CSS classes can be used to specify behaviours (i.e. “addToCart” class on a button) - this fits the html5 spec which
  says that class is used to add semantic information, rather than strictly for presentation.
- Loading indication should be handled via CSS. I would think it should add a class to the target button/link and also
  the body tag and remove both when loading is complete
- Should use the HTTP status code for error responses (see 400, 409, 422, 500)
- Should generally return json (but be smart enough to handle html if its mistakenly given)
- Anything beyond jquery, like a modal dialog if needed, should be easily interchangeable (i.e. you can tell it to use
  jqueryUI, foundation, bootstrap, fancybox, or write your own adapter for any modal plugin)