<?php

/**
 * @package silvershop-discounts
 */
class ProductsDiscountConstraint extends ItemDiscountConstraint {

    private static $db = array(
        'ExactProducts' => 'Boolean'
    );

    private static $many_many = array(
        "Products" => "Product"
    );

    public function updateCMSFields(FieldList $fields) {
        if($this->owner->isInDB()) {
            $fields->findOrMakeTab('Root.Main.Constraints.Products', _t('ProductsDiscountConstraint.TabTitle', 'Products'));
            $fields->addFieldsToTab("Root.Main.Constraints.Products",array(
                GridField::create("Products",
                    _t('ProductsDiscountConstraint.SpecificProducts', 'Specific Products'),
                    $this->owner->Products(),
                    GridFieldConfig_RelationEditor::create()
                        ->removeComponentsByType("GridFieldAddNewButton")
                        ->removeComponentsByType("GridFieldEditButton")
                )->setDescription(_t(
                    'ProductsDiscountConstraint.SpecificProductsDescription',
                    'Select specific products that this discount applies to'
                )),
                CheckboxField::create("ExactProducts", _t(
                    'ProductsDiscountConstraint.ExactProducts',
                    'All the selected products must be present in cart.'
                )),
            ));
        }
    }

    public function check(Discount $discount) {
        $products = $discount->Products();

        // if no products in the discount even
        if(!$products->exists()) {
            $curr = Versioned::current_stage();

            Versioned::reading_stage('Stage');
            $products = $discount->Products();

            if(!$products->exists()) {
                return true;
            }

            $constraintproductids = $products->map('ID','ID')->toArray();
            Versioned::reading_stage($curr);
        } else {
          $constraintproductids = $products->map('ID','ID')->toArray();
        }

        // uses 'DiscountedProductID' so that subclasses of projects (say a custom nested set of products) can define the
        // underlying DiscountedProductID.
        $cartproductids = $this->order->Items()->map('ProductID','DiscountedProductID')->toArray();
        $intersection = array_intersect($constraintproductids, $cartproductids);

        $incart = $discount->ExactProducts ?
            array_values($constraintproductids) === array_values($intersection) :
            count($intersection) > 0;

        if(!$incart) {
            $this->error(
                _t('ProductsDiscountConstraint.MISSINGPRODUCT', "The required products are not in the cart.")
            );
        }

        return $incart;
    }

    public function itemMatchesCriteria(OrderItem $item, Discount $discount) {
        $products = $discount->Products();
        $itemproduct = $item->Product(true); // true forces the current version of product to be retrieved.

        if($products->exists()) {
            foreach($products as $product) {
                // uses 'DiscountedProductID' since some subclasses of buyable could be used as the item product (such as
                // a bundle) rather than the product stored.
                if($product->ID == $itemproduct->DiscountedProductID) {
                    return true;
                }
            }

            $this->error(
                _t('ProductsDiscountConstraint.MISSINGPRODUCT', "The required products are not in the cart.")
            );

            return false;
        }

        return true;
    }

}
