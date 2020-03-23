<?php
/**
 * Copyright (c) 2020 Gonzalo Aguilar Delgado <gaguilar@level2crm.com>
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */


namespace OCA\Files_Ifcviewer\AppInfo;

use OCP\Util;

Util::addStyle('files_ifcviewer', 'style');

Util::addScript('files_ifcviewer', 'vendor/bimdata/bimdata-viewer.min');
Util::addScript('files_ifcviewer', 'previewifc');



