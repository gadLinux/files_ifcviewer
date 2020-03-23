<?php
/*
 * Copyright (c) 2020 Gonzalo Aguilar Delgado <gaguilar@level2crm.com>
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */

namespace OCA\Files_Ifcviewer\AppInfo;

return ['routes' => [
	['name' => 'Display#showIFCViewer', 'url' => '/', 'verb' => 'GET'],
    ['name' => 'Api#serve', 'url' => '/api/', 'verb' => 'GET'],
    ['name' => 'Api#ifc', 'url' => '/api/cloud/{cloudid}/project/{projectid}/ifc/{ifcid}', 'verb' => 'GET'],
    ['name' => 'Api#ifclist', 'url' => '/api/cloud/{cloudid}/project/{projectid}/ifc', 'verb' => 'GET'],
    ['name' => 'Api#structure', 'url' => '/api/cloud/{cloudid}/project/{projectid}/ifc/{ifcid}/structure', 'verb' => 'GET'],
]];
