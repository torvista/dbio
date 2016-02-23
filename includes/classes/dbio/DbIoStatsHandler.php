<?php
// -----
// Part of the DataBase I/O Manager (aka DbIo) plugin, created by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2016, Vinos de Frutas Tropicales.
//
if (!defined ('IS_ADMIN_FLAG')) {
  exit ('Illegal access');
}

// -----
// This DbIo class handles the export of the DbIo statistics table.
//
class DbIoStatsHandler extends DbIoHandler 
{
    public function __construct ($log_file_suffix)
    {
        include (DIR_FS_CATALOG . DIR_WS_LANGUAGES . $_SESSION['language'] . '/dbio/DbIoStatsHandler.php');
        parent::__construct ($log_file_suffix);
    }
    
    // -----
    // This function, called during the overall class construction, is used to set this handler's database
    // configuration for the DbIo operations.
    //
    protected function setHandlerConfiguration () 
    {
        $this->stats['report_name'] = 'Stats';
        $this->config = array (
            'version' => '0.0.0',
            'handler_version' => '0.0.0',
            'include_header' => true,
            'export_only' => true,
            'tables' => array (
                TABLE_DBIO_STATS => array (
                    'short_name' => 'd',
                ),
            ),
            'description' => DBIO_STATS_DESCRIPTION,
        );
    }

// ----------------------------------------------------------------------------------
//             I N T E R N A L / P R O T E C T E D   F U N C T I O N S 
// ----------------------------------------------------------------------------------

}  //-END class DbIoStatsHandler