<?php

/**
 * @package silvershop-discounts
 **/
class DiscountModelAdmin extends ModelAdmin {

    private static $url_segment = 'discounts';

    private static $menu_title = 'Discounts';

    private static $menu_icon = 'silvershop-discounts/images/icon-coupons.png';

    private static $menu_priority = 2;

    private static $managed_models = array(
        "OrderDiscount",
        "OrderCoupon",
        //"PartialUseDiscount"
    );

    public static $model_importers = array();

    private static $allowed_actions = array(
        "generatecoupons",
        "GenerateCouponsForm"
    );

    private static $model_descriptions = array(
        "OrderDiscount" => "Discounts are applied at the checkout, based on defined constraints. If not constraints are given, then the discount will always be applied.",
        "OrderCoupon" => "Coupons are like discounts, but have an associated code.",
        "PartialUseDiscount" => "Partial use discounts are 'amount only' discounts that allow remainder amounts to be used."
    );

    public function getEditForm($id = null, $fields = null) {
        $form = parent::getEditForm($id, $fields);

        if($grid = $form->Fields()->fieldByName("OrderCoupon")) {
            $grid->getConfig()
                ->addComponent(
                    $link = new GridField_LinkComponent(_t('OrderCoupon.GenerateMultipleAction', 'Generate Multiple Coupons'), $this->Link()."/generatecoupons"),
                    "GridFieldExportButton"
                );
            $link->addExtraClass("ss-ui-action-constructive");
        }

        $descriptions = self::config()->model_descriptions;

        if(isset($descriptions[$this->modelClass])) {
            $form->Fields()->fieldByName($this->modelClass)
                ->setDescription(_t($this->modelClass . '.Description', $descriptions[$this->modelClass]));
        }

        return $form;
    }

    /**
     * Update results list, to include custom search filters
     */
    public function getList() {
        $context = $this->getSearchContext();
        $params = $this->request->requestVar('q');
        $list = $context->getResults($params);

        if(isset($params['HasBeenUsed'])) {
            $list = $list
                ->leftJoin("Product_OrderItem_Discounts", "\"Product_OrderItem_Discounts\".\"DiscountID\" = \"Discount\".\"ID\"")
                ->leftJoin("OrderDiscountModifier_Discounts", "\"OrderDiscountModifier_Discounts\".\"DiscountID\" = \"Discount\".\"ID\"")
                ->innerJoin("OrderAttribute", implode(" OR ", array(
                    "\"OrderAttribute\".\"ID\" = \"Product_OrderItem_Discounts\".\"Product_OrderItemID\"",
                    "\"OrderAttribute\".\"ID\" = \"OrderDiscountModifier_Discounts\".\"OrderDiscountModifierID\""
                )));
        }

        if(isset($params['Products'])) {
            $list = $list
                ->innerJoin("Discount_Products", "Discount_Products.DiscountID = Discount.ID")
                ->filter("Discount_Products.ProductID", $params['Products']);
        }

        if(isset($params['Categories'])) {
            $list = $list
                ->innerJoin("Discount_Categories", "Discount_Categories.DiscountID = Discount.ID")
                ->filter("Discount_Categories.ProductCategoryID", $params['Categories']);
        }

        $this->extend('updateList', $list);

        return $list;
    }

    public function GenerateCouponsForm() {
        $fields = Object::create('OrderCoupon')->getCMSFields();
        $fields->removeByName('Code');
        $fields->removeByName('GiftVoucherID');
        $fields->removeByName('SaveNote');

        $fields->addFieldsToTab("Root.Main", array(
            NumericField::create('Number', _t('GenerateCouponsForm.Number', 'Number of Coupons')),
            FieldGroup::create("Code",
                TextField::create("Prefix", _t('GenerateCouponsForm.CodePrefix', 'Code Prefix'))
                    ->setMaxLength(5),
                DropdownField::create("Length", _t('GenerateCouponsForm.Length', 'Code Characters Length'),
                    array_combine(range(5,20),range(5,20)),
                    OrderCoupon::config()->generated_code_length
                )->setDescription(_t('GenerateCouponsForm.LengthDescription', 'This is in addition to the length of the prefix.'))
            )
        ), "Title");

        $actions = new FieldList(
            new FormAction('generate', _t('GenerateCouponsForm.GenerateAction', 'Generate'))
        );
        $validator = new RequiredFields(array(
            'Title',
            'Number',
            'Type'
        ));
        $form = new Form($this, "GenerateCouponsForm", $fields, $actions, $validator);
        $form->addExtraClass("cms-edit-form cms-panel-padded center ui-tabs-panel ui-widget-content ui-corner-bottom");
        $form->setAttribute('data-pjax-fragment', 'CurrentForm');
        $form->setHTMLID('Form_EditForm');
        $form->loadDataFrom(array(
            'Number' => 1,
            'Active' => 1,
            'ForCart' => 1,
            'UseLimit' => 1
        ));
        return $form;
    }

    public function generate($data, $form) {
        $count = 1;

        if(isset($data['Number']) && is_numeric($data['Number'])) {
            $count = (int) $data['Number'];
        }

        $prefix = isset($data['Prefix']) ? $data['Prefix'] : "";
        $length = isset($data['Length']) ? (int) $data['Length'] : OrderCoupon::config()->generated_code_length;

        for($i = 0; $i < $count; $i++){
            $coupon = new OrderCoupon();
            $form->saveInto($coupon);

            $coupon->Code = OrderCoupon::generate_code(
                $length,
                $prefix
            );

            $coupon->write();
        }
        $this->redirect($this->Link());
    }

    function generatecoupons() {
        return array(
            'Title' => _t('OrderCoupon.GenerateCoupons', 'Generate Coupons'),
            'EditForm' => $this->GenerateCouponsForm(),
            'SearchForm' => '',
            'ImportForm' => ''
        );
    }

}
