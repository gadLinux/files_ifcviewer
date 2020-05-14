/*
 * Copyright (c) 2020 Gonzalo Aguilar Delgado <gaguilar@level2crm.com>
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */

(function(OCA) {

	OCA.FilesIFCViewer = OCA.FilesIFCViewer || {};
	
	/**
	 * @namespace OCA.FilesIFCViewer.PreviewPlugin
	 */
	OCA.FilesIFCViewer.PreviewPlugin = {

		/**
		 * @param fileList
		 */
		attach: function(fileList) {
			this._extendFileActions(fileList.fileActions);
		},

		hide: function() {
			$('#ifcframe').remove();
			if ($('#isPublic').val() && $('#filesApp').val()){
				$('#controls').removeClass('hidden');
				$('#content').removeClass('full-height');
				$('footer').removeClass('hidden');
			}
			FileList.setViewerMode(false);

			// replace the controls with our own
			$('#app-content #controls').removeClass('hidden');
		},

		/**
		 * @param fileContext
		 * @param isFileList
		 */
		show: function(fileContext, isFileList) {
			var self = this;
			var shown = true;
			var $iframe;
			var viewer = OC.generateUrl('/apps/files_ifcviewer/?fileId={fileId}&file={file}&projectId={projectId}&modelId={modelId}&dataDir={dataDir}', 
					{
						fileId: fileContext.fileId,
						file: fileContext.downloadUrl,
						projectId: fileContext.id,
						modelId: fileContext.id,
						dataDir: fileContext.hostUrl
					});
			if(isFileList === true) {
				FileList.setViewerMode(true);
			}
                        window.location.replace(viewer);

		},

		/**
		 * This will register the actionHandler to be called everytime
		 * a file is clicked
		 * @param fileActions
		 * @private
		 */
		_extendFileActions: function(fileActions) {
			var registerMimeTypes = ['application/x-step', 'model/gltf-binary'];
			var self = this;
			var index = 1;
			
			for (const mimeType of registerMimeTypes) {
				fileActions.registerAction({
					name: 'viewifc_'+index,
					displayName: 'ModelViewer',
					mime: mimeType,
					permissions: OC.PERMISSION_READ,
					actionHandler: function(fileName, context) {
						const hostUrl = context.fileList.filesClient.getClient().resolveUrl("/");
						const downloadUrl = context.fileList.getDownloadUrl(fileName, context.dir);
						if (downloadUrl && downloadUrl !== '#') {
							var model=context.fileList.getModelForFile(fileName);
							var fileInfo = context.fileList.findFile(fileName);
							var fileContext = {
								hostUrl: hostUrl + "index.php/apps/files_ifcviewer/api",
								downloadUrl: downloadUrl,
								cid: model.cid,
								id: model.id,
								attributes: model.attributes,
								fileId: fileInfo.id,
							};
							self.show(fileContext, true);
						}
					}
				});
				fileActions.setDefault(mimeType, 'viewifc_'+index++	);
			}
		}
	};

})(OCA);

OC.Plugins.register('OCA.Files.FileList', OCA.FilesIFCViewer.PreviewPlugin);

// FIXME: Hack for single public file view since it is not attached to the fileslist
$(document).ready(function(){
	if ($('#isPublic').val() && $('#mimetype').val() === 'model/gltf-binary') {
		var sharingToken = $('#sharingToken').val();
		var downloadUrl = OC.generateUrl('/s/{token}/download', {token: sharingToken});
		var viewer = OCA.FilesIFCViewer.PreviewPlugin;
		viewer.show(downloadUrl, false);
	}
});
