<?php
// -----
// Part of the DataBase I/O Manager (aka DbIo) plugin, created by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2015-2016, Vinos de Frutas Tropicales.
//

// -----
// Defines the handler's descriptive text.
//
define ('DBIO_PRODUCTSATTRIBSBASIC_DESCRIPTION', 'This report-format supports import/export of the <em>basic</em> products\' attributes\' values. The report, indexed by the associated product\'s model-number, includes one record per product/product-option pair with the option-specific values separated by ^ characters &mdash; using your store\'s <code>DEFAULT_LANGUAGE</code>.  All options\' names and option values\' names <b>must already exist within your database</b> for an associated attributes\' record to be successfully imported.');