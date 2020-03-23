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
	 * @namespace OCA.FilesIFCViewer.BimSurfer
	 */
	OCA.FilesIFCViewer.BimData = {
		viewerContext: {
			viewer: {},
			store: {},
			eventHub: {},
			setAccessToken: {}
		},
		init: function(fileContext) {
			const cfg = {
		         cloudId: 1,
		         projectId: 100,
		         ifcIds: [fileContext.id],
		         apiUrl: fileContext.hostUrl,
		         bimdataPlugins: {
		           bcf:false
		         }
			};
			const accessToken = 'DEMO_TOKEN';
			const {viewer, store, eventHub, setAccessToken} = initBIMDataViewer('bimdata-viewer', accessToken, cfg);
			this.viewerContext.viewer = viewer;
			this.viewerContext.store = store;
			this.viewerContext.eventHub = eventHub;
			this.viewerContext.setAccessToken = setAccessToken;
			console.log("Done!");
		},
		shutdown: function() {
			//viewerContext.viewer.shutdown();
        },
	    hide: function() {
	        this.shutdown();
	    }	
	};
	
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
			$('#bimdata-viewer').remove();
			if ($('#isPublic').val() && $('#filesApp').val()){
				$('#controls').removeClass('hidden');
				$('#content').removeClass('full-height');
				$('footer').removeClass('hidden');
			}
			OCA.FilesIFCViewer.BimData.hide();
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
			var viewer = OC.generateUrl('/apps/files_ifcviewer/?file={file}', {file: fileContext.downloadUrl});
			var $renderedTmpl = $('<div id="bimdata-viewer"/>');

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
				$('#app-content').after($renderedTmpl);
			}
			

//			$("#pageWidthOption").attr("selected","selected");
			// replace the controls with our own
			$('#app-content #controls').addClass('hidden');

			// if a filelist is present, the PDF viewer can be closed to go back there
			if($('#bimdata-viewer').length){
				var viewer_contents = $('#bimdata-viewer').contents();
				if ($('#fileList').length) {
					OCA.FilesIFCViewer.BimData.init(fileContext);
					// Go back on ESC
					$(document).keyup(function(e) {
						if (shown && e.keyCode == 27) {
							shown = false;
							self.hide();
						}
					});
				} else {
					iframe.find("#secondaryToolbarClose").addClass('hidden');
				}
			};
			
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
			var self = this;
			fileActions.registerAction({
				name: 'viewifc',
				displayName: 'ModelViewer',
				mime: 'model/gltf-binary',
				permissions: OC.PERMISSION_READ,
				actionHandler: function(fileName, context) {
					var downloadUrl = context.fileList.getDownloadUrl(fileName, context.dir);
					if (downloadUrl && downloadUrl !== '#') {
//						const hostUrl = context.fileList.filesClient.getClient().resolveUrl(context.dir);
						const hostUrl = context.fileList.filesClient.getClient().resolveUrl("/");
						var model=context.fileList.getModelForFile(fileName);
						var fileContext = {
							hostUrl: hostUrl + "index.php/apps/files_ifcviewer/api",
							downloadUrl: downloadUrl,
							cid: model.cid,
							id: model.id,
							attributes: model.attributes,
						};
						self.show(fileContext, true);
					}
				}
			});
			fileActions.setDefault('model/gltf-binary', 'viewifc');
			fileActions.registerAction({
				name: 'viewifc',
				displayName: 'ModelViewer',
				mime: 'model/xkt-binary',
				permissions: OC.PERMISSION_READ,
				actionHandler: function(fileName, context) {
					var downloadUrl = context.fileList.getDownloadUrl(fileName, context.dir);
					if (downloadUrl && downloadUrl !== '#') {
						const hostUrl = context.fileList.filesClient.getClient().resolveUrl("/");
						var model=context.fileList.getModelForFile(fileName);
						var fileContext = {
							hostUrl: hostUrl + "index.php/apps/files_ifcviewer/api",
							downloadUrl: downloadUrl,
							cid: model.cid,
							id: model.id,
							attributes: model.attributes,
						};
						self.show(fileContext, true);
					}
				}
			});
			fileActions.setDefault('model/xkt-binary', 'viewifc');
			
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
