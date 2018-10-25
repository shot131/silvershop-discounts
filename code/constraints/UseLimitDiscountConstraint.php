<?php

/**
 * @package silvershop-discounts
 */
class UseLimitDiscountConstraint extends DiscountConstraint {

    private static $db = array(
        "UseLimit" => "Int"
    );

    private static $field_labels = array(
        "UseLimit" => "Maximum number of uses"
    );

    public function updateCMSFields(FieldList $fields) {
        $fields->findOrMakeTab('Root.Main.Constraints.Main', _t('DiscountModelAdmin.Main', 'Main'));
        $fields->addFieldToTab("Root.Main.Constraints.Main",
            NumericField::create("UseLimit", _t('UseLimitDiscountConstraint.UseLimit', 'Limit number of uses'), 0)
                ->setDescription(_t('UseLimitDiscountConstraint.UseLimitDescription', 'Note: 0 = unlimited'))
        );
    }

    public function check(Discount $discount) {
        if($discount->UseLimit) {
            if($discount->getUseCount($this->order->ID) >= $discount->UseLimit) {
                $this->error(_t('DiscountConstraint.USELIMITREACHED', "This discount has reached its maximum number of uses."));

                return false;
            }
        }

        return true;
    }

}
