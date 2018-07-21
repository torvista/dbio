<?php
// -----
// Part of the DataBase Import/Export (aka DbIo) plugin, created by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2018, Vinos de Frutas Tropicales.
//
if (!defined('IS_ADMIN_FLAG')) {
    exit('Illegal access');
}

// -----
// This DbIo class handles the customizations required for a basic Zen Cart product "Discount Quantity" import/export.
//
class DbIoProductsDiscountQuantityHandler extends DbIoHandler 
{
    const DISCOUNT_TYPE_NONE            = 0;
    const DISCOUNT_TYPE_PERCENTAGE      = 1;
    const DISCOUNT_TYPE_ACTUAL_PRICE    = 2;
    const DISCOUNT_TYPE_AMOUNT_OFF      = 3;
    
    const DISCOUNT_TYPE_FROM_PRICE      = 0;
    const DISCOUNT_TYPE_FROM_SPECIAL    = 1;
    
    public static function getHandlerInformation()
    {
        global $db;
        DbIoHandler::loadHandlerMessageFile('ProductsDiscountQuantity'); 
        return array(
            'version' => '1.0.0',
            'handler_version' => '1.0.0',
            'include_header' => true,
            'export_only' => false,
            'description' => DBIO_PRODUCTSDISCOUNTQUANTITY_DESCRIPTION,
        );
    }
    
    // -----
    // This function overrides the base DbIo SQL query generation.
    //
    public function exportGetSql($sql_limit = '')
    {
        $export_sql =
            "SELECT p.products_id, p.products_model, p.products_discount_type, p.products_discount_type_from, p.products_mixed_discount_quantity, GROUP_CONCAT(CONCAT_WS(':', pdq.discount_qty, pdq.discount_price) SEPARATOR ';') AS qty_prices 
               FROM " . TABLE_PRODUCTS . " AS p
                    LEFT JOIN " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " pdq 
                        ON pdq.products_id = p.products_id
           GROUP BY p.products_id";
        $this->debugMessage("DbIoProductsDiscountQuantity::exportGetSql:\n$export_sql");
        return $export_sql;
    }

// ----------------------------------------------------------------------------------
//             I N T E R N A L / P R O T E C T E D   F U N C T I O N S 
// ----------------------------------------------------------------------------------
    
    // -----
    // This function, called during the overall class construction, is used to set this handler's database
    // configuration for the dbIO operations.
    //
    protected function setHandlerConfiguration () 
    {
        $this->stats['report_name'] = 'ProductsDiscountQuantity';

        $this->config = self::getHandlerInformation ();
        $this->config['handler_does_import'] = true;  //-Indicate that **all** the import-based database manipulations are performed by this handler
        $this->config['keys'] = array (
            TABLE_PRODUCTS => array (
                'alias' => 'p',
                'capture_key_value' => true,
                'products_id' => array (
                    'type' => self::DBIO_KEY_IS_VARIABLE | self::DBIO_KEY_SELECTED,
                ),
            ),
        );
        $this->config['tables'] = array (
            TABLE_PRODUCTS => array ( 
                'alias' => 'p',
            ), 
            TABLE_PRODUCTS_DISCOUNT_QUANTITY => array ( 
                'alias' => 'pdq',
            ), 
        );
        $this->config['fixed_headers'] = array (
            'products_id' => TABLE_PRODUCTS,
            'products_model' => self::DBIO_NO_IMPORT,
            'products_discount_type' => TABLE_PRODUCTS,
            'products_discount_type_from' => TABLE_PRODUCTS,
            'products_mixed_discount_quantity' => TABLE_PRODUCTS,
            'qty_prices' => self::DBIO_SPECIAL_IMPORT
         );
    }
    
    // -----
    // This function, called at the start of each record's import, gives the handler the opportunity to provide a multi-key
    // method for the import.  The base DbIoHandler processing (based on this handler's configuration) has attempted to
    // locate a UNIQUE record based on either a products_id or products_model match.
    //
    // For this handler, a import-record must contain either a products_id or products_model that exists within the database or
    // the record cannot be imported.
    //
    protected function importCheckKeyValue($data)
    {
        global $db;
        
        // -----
        // If the current import is an insert, then a patching 'products_id' was not found and the associated import
        // record cannot be imported.
        //
        if ($this->import_is_insert) {
            $this->debugMessage("No matching products_id found for the record at line #" . $this->stats['record_count'] . ". The record was not imported.", self::DBIO_WARNING);
            $this->record_status = false;
        }
        return $this->record_status;
    }
     
    // -----
    // This function handles any overall record post-processing required for the ProductsDiscountQuantity import, specifically
    // making sure that the products' price sorter is run for the just inserted/updated product.
    //
    protected function importRecordPostProcess($products_id)
    {
        $this->debugMessage ("ProductsDiscountQuantity::importRecordPostProcess ($products_id)", self::DBIO_INFORMATIONAL);
        if ($products_id !== false && $this->operation != 'check') {
            zen_update_products_price_sorter($products_id);
        }
    }
    
    // -----
    // This function, called to process each field within a CSV import, validates the values for the products-table
    // entries.
    //
    protected function importProcessField($table_name, $field_name, $language_id, $field_value)
    {
        parent::importProcessField($table_name, $field_name, $language_id, $field_value);
        if ($this->record_status && $table_name == TABLE_PRODUCTS) {
            switch ($field_name) {
                case 'products_discount_type':
                    switch ($field_value) {
                        case self::DISCOUNT_TYPE_NONE:          //-Fall through ...
                        case self::DISCOUNT_TYPE_PERCENTAGE:    //-Fall through ...
                        case self::DISCOUNT_TYPE_AMOUNT_OFF:    //-Fall through ...
                        case self::DISCOUNT_TYPE_ACTUAL_PRICE:
                            break;
                        default:
                            $this->record_status = false;
                            $this->debugMessage ("[*] $table_name.$field_name, line #" . $this->stats['record_count'] . ": Value ($field_value) is not valid for this field.", self::DBIO_ERROR);
                            break;
                    }
                    break;
                case 'products_discount_type_from':
                    switch ($field_value) {
                        case self::DISCOUNT_TYPE_FROM_PRICE:   //-Fall through ...
                        case self::DISCOUNT_TYPE_FROM_SPECIAL:
                            break;
                        default:
                            $this->record_status = false;
                            $this->debugMessage ("[*] $table_name.$field_name, line #" . $this->stats['record_count'] . ": Value ($field_value) is not valid for this field.", self::DBIO_ERROR);
                            break;
                    }
                    break;
                case 'products_mixed_discount_quantity':
                    if ($field_value != 0 && $field_value != 1) {
                        $this->record_status = false;
                        $this->debugMessage ("[*] $table_name.$field_name, line #" . $this->stats['record_count'] . ": Value ($field_value) is not valid for this field.", self::DBIO_ERROR);
                    }
                default:
                    break;
            }
        }
        if ($this->record_status) {
            if (!isset($this->saved_data)) {
                $this->saved_data = array();
            }
            $this->saved_data[$field_name] = $field_value;
        }
    }
    
    // -----
    // Since this handler performs the insert itself, this required method provides that processing for each
    // imported CSV record.
    //
    protected function importFinishProcessing()
    {
        $missing_fields = array();
        foreach ($this->config['fixed_headers'] as $field_name => $table_name) {
            if ($table_name == self::DBIO_NO_IMPORT) {
                continue;
            }
            if (!isset($this->saved_data[$field_name])) {
                $missing_fields[] = $field_name;
            }
        }
        if (count($missing_fields) != 0) {
            $message = "Missing one or more required fields: " . implode(',', $missing_fields);
        } else {
            $message = '';
            
            $products_id = $this->saved_data['products_id'];
            $qty_prices = $this->saved_data['qty_prices'];
            $products_discount_type = $this->saved_data['products_discount_type'];
            
            if ($products_discount_type == self::DISCOUNT_TYPE_NONE) {
                if (!empty($qty_prices)) {
                    $message = "Discount type is 'None', but quantity discounts provided.";
                }
            } else {
                if (empty($qty_prices)) {
                    $message = "Discount type is not 'None', but no quantity discounts provided.";
                } else {
                    $quantity_discounts = array();
                    $discounts = explode(';', $qty_prices);
                    foreach ($discounts as $current_qty_price) {
                        $current_entry = explode(':', $current_qty_price);
                        if (!is_array($current_entry) || count($current_entry) != 2) {
                            $message = "Invalid quantity/price pair ($current_qty_price).";
                            break;
                        }
                        if (((float)$current_entry[0]) <= 0) {
                            $message = "Quantity must be numeric and greater than 0 ($current_qty_price).";
                            break;
                        }
                        if (((float)$current_entry[1]) <= 0) {
                            $message = "Price must be numeric and greater than 0 ($current_qty_price).";
                            break;
                        }
                        if (isset($quantity_discounts[$current_entry[0]])) {
                            $message = "Duplicate quantity specified ($current_qty_price).";
                            break;
                        }
                        $quantity_discounts[$current_entry[0]] = $current_entry[1];
                    }
                }
            }
        }
        
        if ($message == '') {
            if ($this->operation != 'check') {
                $GLOBALS['db']->Execute(
                    "DELETE FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . "
                      WHERE products_id = $products_id"
                );
            }
            
            if ($products_discount_type == self::DISCOUNT_TYPE_NONE) {
                $this->debugMessage("Removing all discounts for $products_id.");
                if ($this->operation != 'check') {
                    $GLOBALS['db']->Execute(
                        "UPDATE " . TABLE_PRODUCTS . "
                            SET products_discount_type = " . self::DISCOUNT_TYPE_NONE . "
                          WHERE products_id = $products_id
                          LIMIT 1"
                    );
                }
            } else {
                if ($this->operation != 'check') {
                    $GLOBALS['db']->Execute(
                        "UPDATE " . TABLE_PRODUCTS . "
                            SET products_discount_type = $products_discount_type,
                                products_discount_type_from = " . $this->saved_data['products_discount_type_from'] . ",
                                products_mixed_discount_quantity = " . $this->saved_data['products_mixed_discount_quantity'] . "
                          WHERE products_id = $products_id
                          LIMIT 1"
                    );

                    ksort($quantity_discounts);
                    $sql_data_array = array(
                        'discount_id' => 1,
                        'products_id' => $products_id
                    );
                    foreach ($quantity_discounts as $quantity => $price) {
                        $sql_data_array['discount_qty'] = $quantity;
                        $sql_data_array['discount_price'] = $price;
                        zen_db_perform(TABLE_PRODUCTS_DISCOUNT_QUANTITY, $sql_data_array);
                        $sql_data_array['discount_id']++;
                    }
                }
            }
        }

        if ($message != '') {
            $this->record_status = false;
            $this->debugMessage("[*] Discounts not inserted at line number " . $this->stats['record_count'] . "; $message", self::DBIO_WARNING);
        }
    }

}  //-END class DbIoProductsDiscountQuantityHandler
