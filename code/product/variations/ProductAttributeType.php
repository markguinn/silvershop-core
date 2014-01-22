<?php
/**
 * Producte Attribute Type
 * Types of product attributes.
 * eg: color, size, length
 * @subpackage variations
 */
class ProductAttributeType extends DataObject{

	static $db = array(
		'Name' => 'Varchar', //for back-end use
		'Label' => 'Varchar' //for front-end use
	);

	static $has_many = array(
		'Values' => 'ProductAttributeValue'
	);

	static $summary_fields = array(
		'Name' => 'Name',
		'Label' => 'Label'
	);

	static $default_sort = "ID ASC";

	function getCMSFields(){
		$fields = $this->scaffoldFormFields(array(
			// Don't allow has_many/many_many relationship editing before the record is first saved
			'includeRelations' => ($this->ID > 0),
			'tabbed' => true,
			'ajaxSafe' => true
		));

		if($this->isInDB()){
			$itemsConfig = new GridFieldConfig_RelationEditor();
			$itemsTable = new GridField("Values","Values",$this->Values(),$itemsConfig);
		}else{
			$itemsTable = new LiteralField("Values", "<p class=\"message warning\">Save first, then you can add values.</p>");
		}
		$fields->addFieldToTab("Root.Values", $itemsTable);

		$this->extend('updateCMSFields', $fields);
		return $fields;
	}

	static function find_or_make($name){
		$name = strtolower($name);
		if($type = DataObject::get_one('ProductAttributeType',"LOWER(\"Name\") = '$name'")){
			return $type;
		}
		$type = new ProductAttributeType();
		$type->Name = $name;
		$type->Label = $name;
		$type->write();
		return $type;
	}

	function addValues(array $values){
		$avalues = $this->convertArrayToValues($values);
		$this->Values()->addMany($avalues);
	}

	/**
	 * Finds or creates values for this type.
	 * 
	 * @param array $values
	 * @return DataObjectSet
	 */
	function convertArrayToValues(array $values){
		$set = new ArrayList();
		foreach($values as $value){
			$val = $this->Values()->find('Value',$value);
			if(!$val){  //TODO: ignore case, if possible
				$val = new ProductAttributeValue();
				$val->Value = $value;
				$val->write();
			}
			$set->push($val);
		}
		return $set;
	}

	function getDropDownField($emptystring = null,$values = null){
		$values = ($values) ? $values : $this->Values('','Sort ASC, Value ASC');
		if($values->exists()){
			$field = new DropdownField('ProductAttributes['.$this->ID.']',$this->Name,$values->map('ID','Value'));
			if($emptystring)
				$field->setEmptyString($emptystring);
			return $field;
		}
		return null;
	}
	
	function onBeforeWrite(){
		parent::onBeforeWrite();
		if($this->Name && !$this->Label){
			$this->Label = $this->Name;
		}elseif($this->Label && !$this->Name){
			$this->Name = $this->Label;
		}
		
	}
	
	function canDelete($member = null){
		//TODO: prevent deleting if has been used
		return true;
	}

}