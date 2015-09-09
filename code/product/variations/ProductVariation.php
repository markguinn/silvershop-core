<?php
/**
 * Product Variation
 * Provides a means for specifying many variations on a product.
 * Used in combination with ProductAttributes, such as color, size.
 * A variation will specify one particular combination, such as red, and large.
 *
 * @package shop
 * @subpackage variations
 */
class ProductVariation extends DataObject implements Buyable{

	static $db = array(
		//'Title' => 'Text',
		'InternalItemID' => 'Varchar(30)',
		'Price' => 'Currency'
	);
	static $has_one = array(
		'Product' => 'Product',
		'Image' => 'Image'
	);
	static $many_many = array(
		'AttributeValues' => 'ProductAttributeValue'
	);

	static $casting = array(
		'Title' => 'Text',
		'Price' => 'Currency'
	);

	static $versioning = array(
		'Live'
	);

	static $extensions = array(
		"Versioned('Live')"
	);

	static $summary_fields = array(
		'InternalItemID' => 'Product Code',
		//'Product.Title' => 'Product',
		'Title' => 'Variation',
		'Price' => 'Price'
	);
	
	static $searchable_fields = array(
		'Product.Title',
		'InternalItemID'
	);

	public static $default_sort = "InternalItemID";
	
	static $order_item = "ProductVariation_OrderItem";

	function getCMSFields() {
		$fields = array();
		$fields[] = new TextField('InternalItemID','Product Code');
		$fields[] = new TextField('Price');
		//add attributes dropdowns
		if($this->Product()->VariationAttributeTypes()->exists() && $attributes = $this->Product()->VariationAttributeTypes()){
			
			foreach($attributes as $attribute){
				if($field = $attribute->getDropDownField()){
					if($value = $this->AttributeValues()->find('TypeID',$attribute->ID))
						$field->setValue($value->ID);

					$fields[] = $field;
				}else{
					$fields[] = new LiteralField('novalues'.$attribute->Name,"<p class=\"message warning\">".$attribute->Name." has no values to choose from. You can create them in the \"Products\" &#62; \"Product Attribute Type\" section of the CMS.</p>");
				}
				//TODO: allow setting custom value, rather than visiting the products section
			}
		}
		$set = new FieldList($fields);
		$this->extend('updateCMSFields', $set);
		return $set;
	}
	
	function onBeforeWrite(){
		parent::onBeforeWrite();
		//TODO: perhaps move this to onAfterWrite, for the case when the variation has just been created, and thus has no ID to relate
		//..but that might cause recursion
		if(isset($_POST['ProductAttributes']) && is_array($_POST['ProductAttributes'])){
			$this->AttributeValues()->setByIDList(array_values($_POST['ProductAttributes']));
		}
	}

	function getTitle(){
		$values = $this->AttributeValues();
		if($values->exists()){
			$labelvalues = array();
			foreach($values as $value){
				$labelvalues[] = $value->Type()->Label.':'.$value->Value;
			}
			return implode(', ',$labelvalues);
		}
		return $this->InternalItemID;
	}

	//this is used by TableListField to access attribute values.
	function AttributeProxy(){
		$do = new DataObject();
		if($this->AttributeValues()->exists()){
			foreach($this->AttributeValues() as $value){
				$do->{'Val'.$value->Type()->Name} = $value->Value;
			}
		}
		return $do;
	}

	/**
	 * @deprecated use canPurchase instead
	 */
	function AllowPurchase() {
		return $this->canPurchase();
	}

	function canPurchase($member = null) {
		$allowpurchase = false;
		if($product = $this->Product()){
			$allowpurchase = ($this->sellingPrice() > 0) && $product->AllowPurchase;
		}
		$extended = $this->extendedCan('canPurchase', $member);
		if($allowpurchase && $extended !== null){
			$allowpurchase = $extended;
		}
		return $allowpurchase;
	}

	//TODO: change this function to match Product->IsInCart

	/*
	 * Returns if the product variation is already in the shopping cart.
	 * Note : This function is usable in the Product Variation context because a
	 * ProductVariation_OrderItem only has a ProductVariation object in attribute
	 */
	function IsInCart() {
		return ($this->Item() && $this->Item()->Quantity > 0) ? true : false;
	}

	/*
	 * Returns the order item which contains the product variation
	 * Note : This function is usable in the ProductVariation context because a
	 * ProductVariation_OrderItem only has a ProductVariation object in attribute
	 */
	function Item() {
		$filter = array();
		$this->extend('updateItemFilter',$filter);
		$item = ShoppingCart::singleton()->get($this,$filter);
		if(!$item)
			$item = $this->createItem(0); //return dummy item so that we can still make use of Item
		$this->extend('updateDummyItem',$item);
		return $item;
	}

	function addLink() {
		return $this->Item()->addLink($this->ProductID,$this->ID);
	}
	
	function createItem($quantity = 1,$filter = array()){
		$orderitem = $this->stat("order_item");
		$item = new $orderitem();
		$item->ProductID = $this->ProductID;
		$item->ProductVariationID = $this->ID;
		//$item->ProductVariationVersion = $this->Version;
		if($filter){
			$item->update($filter); //TODO: make this a bit safer, perhaps intersect with allowed fields
		}
		$item->Quantity = $quantity;
		return $item;
	}
	
	function sellingPrice(){
		$price = $this->Price;
		if ($price == 0 && $this->Product() && $this->Product()->sellingPrice()){
			$price = $this->Product()->sellingPrice();
		}
		if (!$price) $price = 0;
		$this->extend("updateSellingPrice",$price); //TODO: this is not ideal, because prices manipulations will not happen in a known order
		// NOTE: Ideally, this would be dependent on the locale but as of
		// now the Silverstripe Currency field type has 2 hardcoded all over
		// the place. In the mean time there is an issue where the displayed
		// unit price can not exactly equal the multiplied price on an order
		// (i.e. if the calculated price is 3.145 it will display as 3.15.
		// so if I put 10 of them in my cart I will expect the price to be
		// 31.50 not 31.45).
		return round($price, Order::config()->rounding_precision);
	}

}

/**
 * Product Variation - Order Item
 * Connects a variation to an order, as a line in the order specifying the particular variation.
 * @package shop
 * @subpackage variations
 */
class ProductVariation_OrderItem extends Product_OrderItem {

	static $db = array(
		'ProductVariationVersion' => 'Int'
	);

	static $has_one = array(
		'ProductVariation' => 'ProductVariation'
	);
	
	static $buyable_relationship = "ProductVariation";
	
	/**
	 * Overloaded relationship, for getting versioned variations
	 * @param unknown_type $current
	 */
	public function ProductVariation($forcecurrent = false) {
		if($this->ProductVariationID && $this->ProductVariationVersion && !$forcecurrent){
			return Versioned::get_version('ProductVariation', $this->ProductVariationID, $this->ProductVariationVersion);
		}elseif($this->ProductVariationID && $product = DataObject::get_by_id('ProductVariation', $this->ProductVariationID)){
			return $product;
		}
		return false;
	}

	function SubTitle() {
		if($this->ProductVariation())
			return $this->ProductVariation()->getTitle();
		return false;
	}
	
	function Image(){
		if($this->ProductVariation() && $image = $this->ProductVariation()->Image()){
			return $image;
		}
		if($this->Product()){
			return $this->Product()->Image();
		}
		return null;
	}

}
