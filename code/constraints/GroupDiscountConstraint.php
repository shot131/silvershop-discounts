<?php

class GroupDiscountConstraint extends DiscountConstraint{

	private static $has_one = array(
		"Group" => "Group"
	);

	public function updateCMSFields(FieldList $fields) {
        $fields->findOrMakeTab('Root.Main.Constraints.Main', _t('DiscountModelAdmin.Main', 'Main'));
		$fields->addFieldToTab("Root.Main.Constraints.Main",
			DropdownField::create("GroupID",
				_t('GroupDiscountConstraint.GroupID', 'Member Belongs to Group'),
				Group::get()->map('ID', 'Title')
			)->setHasEmptyDefault(true)
			->setEmptyString(_t('GroupDiscountConstraint.EmptyGroupID', 'Any or no group'))
		);
	}
	
	public function filter(DataList $list) {
		$groupids = array(0);
		if($member = $this->getMember()){
			$groupids = $groupids + $member->Groups()
										->map('ID', 'ID')
										->toArray();
		}

		return $list->filter("GroupID", $groupids);
	}

	public function check(Discount $discount) {
		$group = $discount->Group();
		$member = $this->getMember();
		if($group->exists() && (!$member || !$member->inGroup($group))){
			$this->error(_t(
				"Discount.GROUPED", 
				"Only specific members can use this discount."
			));
			return false;
		}

		return true;
	}
	
	public function getMember(){
		return isset($this->context['Member']) ? $this->context['Member'] : $this->order->Member();
	}

}