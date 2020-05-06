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
			server: {},
			viewer: {},
			eventHub: {},
			setAccessToken: {}
		},
		init: function(fileContext) {
			/*
			var fileContext = {
					hostUrl: hostUrl + "index.php/apps/files_ifcviewer/api",
					downloadUrl: downloadUrl,
					cid: model.cid,
					id: model.id,
					attributes: model.attributes,
				};
			*/
			// Server client will load data from the file systems
			this.viewerContext.server = new Server({
	            dataDir: fileContext.hostUrl,
	        });
			
			this.viewerContext.server.getGeometry = function(projectId, modelId, done, error) {
                const url = "/remote.php/webdav/admin/factory-vf83598797856cbd0013f0732d69f3e05.xkt";
                utils.loadArraybuffer(url, done, error);
            }
			
	        // Create  BIMViewer that loads data via the Server
			// $('#myCanvas')?
			this.viewerContext.viewer = new BIMViewer(this.viewerContext.server, {
	            canvasElement: document.getElementById("myCanvas"), // WebGL canvas
	            explorerElement: document.getElementById("myExplorer"), // Left panel
	            toolbarElement: document.getElementById("myToolbar"), // Toolbar
	            navCubeCanvasElement: document.getElementById("myNavCubeCanvas"),
	            busyModelBackdropElement: document.querySelector(".xeokit-busy-modal-backdrop")
	        });
			
	        // Create tooltips on various HTML elements created by BIMViewer
	        tippy('[data-tippy-content]', {
	            appendTo: function () {
	                return document.querySelector('#bimdata-viewer')
	            }
	        });
	        
	        this.viewerContext.viewer.setConfigs({});
	        
	        /*
			const cfg = {
		         cloudId: 1,
		         projectId: 100,myViewer
		         ifcIds: [fileContext.id],
		         apiUrl: fileContext.hostUrl,
		         bimdataPlugins: {
		           bcf:false
		         }
			};
	        */
	        // Load a project
	        this.viewerContext.viewer.loadProject("1", () => {
	        	// The project may load one or models initially.

	        	// Withe request params, we can also specify:
	        	//  - models to load
	        	// - explorer tab to open
//	        	const modelId = requestParams.modelId;
//	        	if (modelId) {
	        	this.viewerContext.viewer.loadModel("" + fileContext.id);
//	        	}
//	        	const tab = requestParams.tab;myViewer
//	        	if (tab) {
//	        		bimViewer.openTab(tab);
//	        	}
	        	//
//	        	window.setInterval((function () {
//	        		var lastHash = "";
//	        		return function () {
//	        			const currentHash = window.location.hash;
//	        			if (currentHash !== lastHash) {
//	        				parseHashParams();
//	        				lastHash = currentHash;
//	        			}
//	        		};
//	        	})(), 200);
	        },
	        (errorMsg) => {
	        	console.error(errorMsg);
	        });
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
			var $renderedTmpl = $('<div id="bimdata-viewer" class="xeokit-busy-modal-backdrop"><div id="myExplorer" class="active"></div><div id="myContent"><div id="myToolbar"></div><canvas id="myCanvas"></canvas></div></div><canvas id="myNavCubeCanvas"></canvas>');

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
			var registerMimeTypes = ['application/x-step', 'model/gltf-binary'];
			var self = this;
			var index = 1;
//			const hostUrl = context.fileList.filesClient.getClient().resolveUrl(context.dir);
			
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
