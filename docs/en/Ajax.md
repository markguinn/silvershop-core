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


## Client Side Usage

### Link Behaviours:

- Ajaxify link: class="ajax" or data-target="ajax"
- Open in modal: data-target="modal"
- While loading, the link and the document.body will have the class ajax-loading added (and removed when loading finished)

### Updating Regions




## Server Side Usage


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