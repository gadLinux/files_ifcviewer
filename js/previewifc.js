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
		bind_event: // addEventListener support for IE8
			function bindEvent(element, eventName, eventHandler) {
		    if (element.addEventListener){
		        element.addEventListener(eventName, eventHandler, false);
		    } else if (element.attachEvent) {
		        element.attachEvent('on' + eventName, eventHandler);
		    }
		},
		
		bind_annotations: function(fileContext) {
			var self = this;
			// Listen to message from child window
			self.bind_event(window, 'message', function (e) {
				var msg = e.data;
				var fileId = fileContext.fileId;
				if(msg.op == 'get-annotation-list') {
					getAnnotations(fileId);
				} else if (msg.op == 'create-annotation') {
			        createAnnotation(fileId, msg.data);
				} else if (msg.op == 'update-annotation') {
			        updateAnnotation(fileId, msg.data);
				} else if (msg.op == 'delete-annotation') {
			        deleteAnnotation(fileId, msg.data);
				}
			});
		},

		/**
		 * @param fileContext
		 * @param isFileList
		 */
		show: function(fileContext, isFileList) {
			var self = this;
			var shown = true;
			var $iframe;
			var viewer = OC.generateUrl('/apps/files_ifcviewer/iframe?fileId={fileId}&file={file}&projectId={projectId}&modelId={modelId}&dataDir={dataDir}', 
					{
						fileId: fileContext.fileId,
						file: fileContext.downloadUrl,
						projectId: fileContext.id,
						modelId: fileContext.id,
						dataDir: fileContext.hostUrl
					});
			$iframe = $('<iframe id="ifcframe" style="width:100%;height:100%;display:block;position:absolute;top:0;z-index:1041;margin-top:50px" src="'+viewer+'" sandbox="allow-scripts allow-same-origin allow-popups allow-modals allow-top-navigation" allowfullscreen="true"/>');

			if(isFileList === true) {
				FileList.setViewerMode(true);
			}

			if ($('#isPublic').val()) {
				// force the preview to adjust its height
				$('#preview').append($iframe).css({height: '100%'});
				$('body').css({height: '100%'});
				$('#content').addClass('full-height');
				$('footer').addClass('hidden');
				$('#imgframe').addClass('hidden');
				$('.directLink').addClass('hidden');
				$('.directDownload').addClass('hidden');
				$('#controls').addClass('hidden');
			} else {
				$('#app-content').after($iframe);
			}
			

			$("#pageWidthOption").attr("selected","selected");
			// replace the controls with our own
			$('#app-content #controls').addClass('hidden');

			// if a filelist is present, the PDF viewer can be closed to go back there
			$('#ifcframe').load(function(){
				self.bind_annotations(fileContext);
				var iframe = $('#ifcframe').contents();
				if ($('#fileList').length)
				{
					// Go back on ESC
					$(document).keyup(function(e) {
						if (shown && e.keyCode == 27) {
							shown = false;
							self.hide();
						}
					});
				} else {
					console.log("IFCViewer: No file list contents");
//					iframe.find("#secondaryToolbarClose").addClass('hidden');
				}
			});
			
			if(!$('html').hasClass('ie8')) {
				history.pushState({}, '', '#bimdata-viewer');
			}

			if(!$('html').hasClass('ie8')) {
				$(window).one('popstate', function (e) {
					self.hide();
				});
			}
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
