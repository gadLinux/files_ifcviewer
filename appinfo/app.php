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

Util::addStyle('files_ifcviewer', 'BIMViewer');
Util::addStyle('files_ifcviewer', 'style');
Util::addStyle('files_ifcviewer', 'fontawesome-free-5.11.2-web/css/all.min');

//Util::addScript('files_ifcviewer', 'vendor/bimdata/bimdata-viewer.min');
Util::addScript('files_ifcviewer', 'vendor/popper');
Util::addScript('files_ifcviewer', 'vendor/tippy');
Util::addScript('files_ifcviewer', 'xeokit/xeokit-viewer.min');
Util::addScript('files_ifcviewer', 'previewifc');



