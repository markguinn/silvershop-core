<?php
/**
 * Customisations to {@link Payment} specifically for the shop module.
 *
 * @package shop
 */
class ShopPayment extends DataExtension {

	private static $has_one = array(
		'Order' => 'Order'
	);

	/**
	 * @var bool - Should the admin see and be able to change the credit card associated with a payment?
	 */
	private static $show_admin_credit_card_field = true;

	/**
	 * @var bool - If this is true, a CMS admin will be able to choose from all saved credit cards associated with
	 *           the same email address if the user doesn't have an account.
	 */
	private static $show_admin_credit_cards_by_email = true;


	/**
	 * @param GatewayResponse $response
	 */
	public function onCaptured($response) {
		$order = $this->owner->Order();
		if($order->exists()){
			OrderProcessor::create($order)->completePayment();
		}
	}


	/**
	 * @param FieldList $fields
	 */
	public function updateCMSFields(FieldList $fields) {
		$cfg = Config::inst();
		if ($cfg->get('ShopPayment', 'show_admin_credit_card_field')) {
			$order = $this->owner->Order();
			if (!$order || !$order->exists()) return;
			$member = $order->Member();

			if ($member && $member->exists()) {
				// If it's a member account, just display all cards associated with the account
				$paymentFilter = array(
					'Order.MemberID' => $member->ID,
				);
			} elseif ($cfg->get('ShopPayment', 'show_admin_credit_cards_by_email')) {
				// If appropriate, look up other cards used by the same email address
				$paymentFilter = array(
					'Order.Email'    => $order->Email,
					'Order.MemberID' => 0,
				);
			} else {
				// If all else fails, at least add other payments from the same order (if for example the total changed)
				$paymentFilter = array(
					'Order.ID' => $order->ID,
				);
			}

			// Only include successfully charged cards
			$paymentFilter['Status'] = array('Captured', 'Authorized', 'Refunded');

			// Build out a list of saved card options
			$cards = array();
			$payments = Payment::get()
				->innerJoin('Order', '"Order"."ID" = "Payment"."OrderID"')
				->filter($paymentFilter)
				->sort('Created', 'DESC');

			foreach ($payments as $payment) {
				if ($payment->SavedCreditCardID && $card = $payment->SavedCreditCard()) {
					$cards[ $card->ID ] = $card->Name . ' (used ' . $payment->obj('Created')->Ago() . ')';
				}
			}

			// Add the field
			$cardField = new DropdownField('SavedCreditCardID', 'Credit Card', $cards);
			$cardField->setEmptyString('No credit card.');
			$fields->insertBefore($cardField, 'Messages');
		}
	}

}
