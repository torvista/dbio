<?php
// -----
// Part of the DataBase Import/Export (aka dbIO) plugin, created by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2016, Vinos de Frutas Tropicales.
//
if (!defined ('IS_ADMIN_FLAG')) {
  exit ('Illegal access');
}

// -----
// This dbIO class handles the customizations required for a basic Zen Cart "Orders Products/Attributes" export-only.  The class
// provides its own header to limit the processing output.
//
class DbIoOrdersProductsAttribsHandler extends DbIoOrdersProductsHandler 
{
    public function __construct ($log_file_suffix)
    {
        include (DIR_FS_CATALOG . DIR_WS_LANGUAGES . $_SESSION['language'] . '/dbio/DbIoOrdersProductsAttribsHandler.php');
        parent::__construct ($log_file_suffix);
    }
    
    // -----
    // This function, called during the overall class construction, is used to set this handler's database
    // configuration for the dbIO operations.  Since this handler "extends" the OrdersProducts handler, let
    // that handler provide the default configuration, then make extension-specific modifications.
    //
    protected function setHandlerConfiguration () 
    {
        parent::setHandlerConfiguration ();
        $this->stats['report_name'] = 'OrdersProductsAttribs';
        $this->config['export_headers']['tables'][TABLE_ORDERS_PRODUCTS] = 'op LEFT JOIN ' . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . ' opa ON op.orders_products_id = opa.orders_products_id';
        $this->config['export_headers']['fields']['products_options'] = 'opa';
        $this->config['export_headers']['fields']['products_options_values'] = 'opa';
        $this->config['export_headers']['order_by_clause'] .= ', op.orders_products_id ASC, opa.orders_products_attributes_id ASC';
        $this->config['description'] = DBIO_ORDERSPRODUCTSATTRIBS_DESCRIPTION;
    }

    // -----
    // Let the OrdersProducts handler do its thing, gathering the base order and product information, then check to
    // see if the current product has any attributes ... and add them.  Note that
    //
    public function exportPrepareFields (array $fields) 
    {
        $fields = parent::exportPrepareFields ($fields);
        if (!isset ($this->last_model_handled) || $this->last_model_handled !== $fields['products_model']) {
            $this->last_model_handled = $fields['products_model'];
        } else {
            foreach ($fields as $field_name => &$field_value) {
                if ($field_name == 'products_options') {
                    break;
                }
                $field_value = '';
            }
        }
        return $fields;
    }
    
// ----------------------------------------------------------------------------------
//             I N T E R N A L / P R O T E C T E D   F U N C T I O N S 
// ----------------------------------------------------------------------------------

}  //-END class DbIoOrdersProductsHandler