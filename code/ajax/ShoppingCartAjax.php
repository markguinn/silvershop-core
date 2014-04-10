<?php
/**
 * Ajax-specific functionality for shopping cart controller
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 04.07.2014
 * @package shop
 * @subpackage ajax
 */
class ShoppingCartAjax extends Extension {

	/**
	 * @param SS_HTTPRequest $request
	 * @param AjaxHTTPResponse $response
	 * @param Buyable $product [optional]
	 */
	public function updateAddResponse(&$request, &$response, $product=null) {
		if ($request->isAjax()) {
			if (!$response) $response = $this->owner->getAjaxResponse();
			$this->setupRenderContexts($response, $product);
			$response->pushRegion('SideCart', $this->owner);
			$response->triggerEvent('cartadd');
			$response->triggerEvent('cartchange', array(
				'action'    => 'add',
				'id'        => $request->param('ID'),
				'quantity'  => $request->requestVar('quantity'),
			));
		}
	}


	/**
	 * @param SS_HTTPRequest $request
	 * @param AjaxHTTPResponse $response
	 * @param Buyable $product [optional]
	 */
	public function updateRemoveResponse(&$request, &$response, $product=null) {
		if ($request->isAjax()) {
			if (!$response) $response = $this->owner->getAjaxResponse();
			$this->setupRenderContexts($response, $product);
			$response->pushRegion('SideCart', $this->owner);
			$response->triggerEvent('cartremove');
			$response->triggerEvent('cartchange', array(
				'action'    => 'remove',
				'id'        => $request->param('ID'),
				'quantity'  => $request->requestVar('quantity'),
			));
		}
	}


	/**
	 * @param SS_HTTPRequest $request
	 * @param AjaxHTTPResponse $response
	 * @param Buyable $product [optional]
	 */
	public function updateRemoveAllResponse(&$request, &$response, $product=null) {
		if ($request->isAjax()) {
			if (!$response) $response = $this->owner->getAjaxResponse();
			$this->setupRenderContexts($response, $product);
			$response->pushRegion('SideCart', $this->owner);
			$response->triggerEvent('cartremove');
			$response->triggerEvent('cartchange', array(
				'action'    => 'removeall',
				'id'        => $request->param('ID'),
				'quantity'  => 0,
			));
		}
	}


	/**
	 * @param SS_HTTPRequest $request
	 * @param AjaxHTTPResponse $response
	 * @param Buyable $product [optional]
	 */
	public function updateSetQuantityResponse(&$request, &$response, $product=null) {
		if ($request->isAjax()) {
			if (!$response) $response = $this->owner->getAjaxResponse();
			$this->setupRenderContexts($response, $product);
			$response->pushRegion('SideCart', $this->owner);
			$response->triggerEvent('cartquantity');
			$response->triggerEvent('cartchange', array(
				'action'    => 'setquantity',
				'id'        => $request->param('ID'),
				'quantity'  => $request->requestVar('quantity'),
			));
		}
	}


	/**
	 * @param SS_HTTPRequest $request
	 * @param AjaxHTTPResponse $response
	 */
	public function updateClearResponse(&$request, &$response) {
		if ($request->isAjax()) {
			if (!$response) $response = $this->owner->getAjaxResponse();
			$this->setupRenderContexts($response);
			$response->pushRegion('SideCart', $this->owner);
			$response->triggerEvent('cartclear');
			$response->triggerEvent('cartchange', array(
				'action'    => 'clear',
			));
		}
	}


	/**
	 * Adds some standard render contexts for pulled regions.
	 *
	 * @param AjaxHTTPResponse $response
	 * @param Buyable $buyable [optional]
	 */
	protected function setupRenderContexts(AjaxHTTPResponse $response, $buyable=null) {
		$response->addRenderContext('CART', $this->owner->Cart());

		if ($buyable) {
			$response->addRenderContext('BUYABLE', $buyable);

			// this could be a Product or ProductVariation (or something else)
			// but we want a render target available for the product specifically
			// for rendering ProductGroupItem in a category or search view
			if ($buyable instanceof Product) {
				$response->addRenderContext('PRODUCT', $buyable);
			} elseif ($buyable->hasMethod('Product')) {
				$response->addRenderContext('PRODUCT', $buyable->Product());
			}
		}
	}

}