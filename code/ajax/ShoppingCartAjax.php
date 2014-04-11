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
	 * @param int $quantity [optional]
	 */
	public function updateAddResponse(&$request, &$response, $product=null, $quantity=1) {
		if ($request->isAjax()) {
			if (!$response) $response = $this->owner->getAjaxResponse();
			$this->setupRenderContexts($response, $product);
			$response->pushRegion('SideCart', $this->owner);
			$response->triggerEvent('cartadd');
			$response->triggerEvent('cartchange', array(
				'action'    => 'add',
				'id'        => $product->ID,
				'quantity'  => $quantity,
			));
		}
	}


	/**
	 * @param SS_HTTPRequest $request
	 * @param AjaxHTTPResponse $response
	 * @param Buyable $product [optional]
	 * @param int $quantity [optional]
	 */
	public function updateRemoveResponse(&$request, &$response, $product=null, $quantity=1) {
		if ($request->isAjax()) {
			if (!$response) $response = $this->owner->getAjaxResponse();
			$this->setupRenderContexts($response, $product);
			$response->pushRegion('SideCart', $this->owner);
			$response->triggerEvent('cartremove');
			$response->triggerEvent('cartchange', array(
				'action'    => 'remove',
				'id'        => $product->ID,
				'quantity'  => $quantity,
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
				'id'        => $product,
				'quantity'  => 0,
			));
		}
	}


	/**
	 * @param SS_HTTPRequest $request
	 * @param AjaxHTTPResponse $response
	 * @param Buyable $product [optional]
	 * @param int $quantity [optional]
	 */
	public function updateSetQuantityResponse(&$request, &$response, $product=null, $quantity=1) {
		if ($request->isAjax()) {
			if (!$response) $response = $this->owner->getAjaxResponse();
			$this->setupRenderContexts($response, $product);
			$response->pushRegion('SideCart', $this->owner);
			$response->triggerEvent('cartquantity');
			$response->triggerEvent('cartchange', array(
				'action'    => 'setquantity',
				'id'        => $product->ID,
				'quantity'  => $quantity,
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
	 * Adds the ajax class to the VariationForm
	 */
	public function updateVariationForm() {
		$this->owner->addExtraClass('ajax');
	}


	/**
	 * @param SS_HTTPRequest $request
	 * @param AjaxHTTPResponse $response
	 * @param Buyable $variation [optional]
	 * @param int $quantity [optional]
	 * @param VariationForm $form [optional]
	 */
	public function updateVariationFormResponse(&$request, &$response, $variation=null, $quantity=1, $form=null) {
		if ($request->isAjax()) {
			if (!$response) $response = $this->owner->getAjaxResponse();
			$this->setupRenderContexts($response, $variation);
			$response->addRenderContext('FORM', $form);
			$response->pushRegion('SideCart', $this->owner);
			$response->triggerEvent('cartadd');
			$response->triggerEvent('cartchange', array(
				'action'    => 'add',
				'id'        => $variation->ID,
				'quantity'  => $quantity,
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
		if ($this->owner->hasMethod('Cart')) {
			$response->addRenderContext('CART', $this->owner->Cart());
		}

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