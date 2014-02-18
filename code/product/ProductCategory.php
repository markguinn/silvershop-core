<?php
 /**
  * Product Category provides a way to hierartically categorise products.
  * 
  * It contains functions for versioning child products
  *
  * @package shop
  */
class ProductCategory extends Page implements HiddenClass {

	public static $db = array(
		'ChildGroupsPermission' => "Enum('Show Only Featured Products,Show All Products')"
	);

	public static $belongs_many_many = array(
		'Products' => 'Product'
	);

	public static $singular_name = "Product Category";
	function i18n_singular_name() {
		return _t("ProductCategory.SINGULAR", $this->stat('singular_name'));
	}
	public static $plural_name = "Product Categories";
	function i18n_plural_name() {
		return _t("ProductCategory.PLURAL", $this->stat('plural_name'));
	}

	static $default_child = 'Product';
	static $icon = 'cms/images/treeicons/folder';

	protected static $include_child_groups = true;
	protected static $page_length = 12;
	protected static $must_have_price = true;

	//TODO: allow grouping multiple sort fields under one 'sort option', and allow choosing direction of each
	protected static $sort_options = array(
		'URLSegment' => 'Alphabetical',
		'BasePrice' => 'Lowest Price',
		//'Popularity' => 'Most Popular'
		//'Featured' => 'Featured',
		//'Weight' => 'Weight'
	);

	protected static $featured_products_permissions = array(
		'Show Only Featured Products',
		'Show All Products'
	);

	protected static $non_featured_products_permissions = array(
		'Show All Products'
	);

	static function set_include_child_groups($include = true){
		self::$include_child_groups = $include;
	}

	static function set_page_length($length){
		self::$page_length = $length;
	}

	static function set_must_have_price($must = true){
		self::$must_have_price = $must;
	}

	static function set_sort_options(array $options){
		self::$sort_options = $options;
	}

	static function get_sort_options(){
		return self::$sort_options;
	}

	function getCMSFields() {
		$fields = parent::getCMSFields();

		if(self::$include_child_groups === 'custom'){
			$fields->addFieldToTab(
				'Root.Content',
				new Tab(
					'Child Groups',
					new HeaderField('How should products be presented in the child groups?'),
					new DropdownField(
	  					'ChildGroupsPermission',
	  					'Permission',
	  					$this->dbObject('ChildGroupsPermission')->enumValues(),
	  					'',
	  					null,
	  					'Don\'t Show Any Products'
					)
				)
			);
		}

		return $fields;
	}

	/**
	 * Retrieve a set of products, based on the given parameters. Checks get query for sorting and pagination.
	 *
	 * @param string $extraFilter Additional SQL filters to apply to the Product retrieval
	 * @param bool $recursive [optional]
	 * @return PaginatedList
	 */
	function ProductsShowable($extraFilter = '', $recursive = true){
		$filter = array();

		$this->extend('updateFilter', $extraFilter);
		if ($extraFilter) $filter[] = $extraFilter;
		if (self::$must_have_price) $filter[] = '"BasePrice" > 0';

		$sort = (isset($_GET['sortby'])) ? Convert::raw2sql($_GET['sortby']) : "\"FeaturedProduct\" DESC, \"URLSegment\"";

		//hard coded sort configuration //TODO: make these custom
		if ($sort == "Popularity") $sort .= " DESC";

		// Figure out the categories to check
		$groupids = array($this->ID);
		if (($recursive === true || $recursive === 'true')
				&& self::$include_child_groups
				&& $childgroups = $this->ChildGroups(true)) {
			$groupids = array_merge($groupids, $childgroups->map('ID','ID'));
		}

		// Build the basic DataList
		$products = Versioned::get_by_stage('Product','Live', implode(' AND ', $filter), $sort)
			->leftJoin('Product_ProductCategories', '"Product_ProductCategories"."ProductID" = "Product"."ID"')
			->filterAny(array(
				'ParentID' => $groupids,
				'Product_ProductCategories.ProductCategoryID' => $groupids,
			));

		// Convert to a paginated list
		$products = new PaginatedList($products, Controller::curr()->getRequest());
		$products->setPageLength(self::$page_length);
		$products->TotalCount = $products->getTotalItems(); // this is just for compatibility

		return $products;
	}

	/**
	 * Return children ProductCategory pages of this group.
	 * @param bool $recursive [optional]
	 * @return DataList
	 */
	function ChildGroups($recursive = false) {
		if($recursive){
			if($children = Versioned::get_by_stage('ProductCategory','Live', "\"ParentID\" = '$this->ID'")){
				$output = new ArrayList($children->toArray());
				foreach($children as $group){
					$output->merge($group->ChildGroups($recursive));
				}
				return $output;
			}
			return null;
		}else{
			return Versioned::get_by_stage('ProductCategory','Live', "\"ParentID\" = '$this->ID'");
		}
	}

	/**
	 * Recursively generate a product menu.
	 * @return DataList
	 */
	function GroupsMenu() {
		if($parent = $this->Parent()) {
			return $parent instanceof ProductCategory ? $parent->GroupsMenu() : $this->ChildGroups();
		} else {
			return $this->ChildGroups();
		}
	}

}

class ProductCategory_Controller extends Page_Controller {

	/**
	 * Return the products for this group.
	 */
	public function Products($recursive = true){
		return $this->ProductsShowable('',$recursive);
	}

	/**
	 * Return products that are featured, that is products that have "FeaturedProduct = 1"
	 */
	function FeaturedProducts($recursive = true) {
		return $this->ProductsShowable("\"FeaturedProduct\" = 1",$recursive);
	}

	/**
	 * Return products that are not featured, that is products that have "FeaturedProduct = 0"
	 */
	function NonFeaturedProducts($recursive = true) {
		return $this->ProductsShowable("\"FeaturedProduct\" = 0",$recursive);
	}

		/**
	 * Provides a dataset of links for sorting products.
	 */
	function SortLinks(){
		if(count($this->get_sort_options()) <= 0) return null;

		$sort = (isset($_GET['sortby'])) ? Convert::raw2sql($_GET['sortby']) : "Title";
		$dos = new ArrayList();
		foreach($this->get_sort_options() as $field => $name){
			$current = ($field == $sort) ? 'current' : false;
			$dos->add(new ArrayData(array(
				'Name' => $name,
				'Link' => $this->Link()."?sortby=$field",
				'Current' => $current
			)));
		}
		return $dos;
	}

}