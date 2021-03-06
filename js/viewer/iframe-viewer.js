// Set up application
//import {Server, BIMViewer};
window.onload = function () {
	
    //--------------------------------------------------------------------------------------------------------------
    // Process page request params, which set up initial viewer state
    //--------------------------------------------------------------------------------------------------------------

    const requestParams = getRequestParams();

	
    // Server client will load data from the file systems
    const server = new Server({
    	dataDir: "api"
    });
    server.downloadURL= requestParams.file;
    // Override default methods
    server.getGeometry=function(projectId, modelId, done, error){
        const url = "/remote.php/webdav/"+modelId+".xkt"; 
        //"/projects/" + projectId + "/models/" + modelId + "/geometry.xkt";
        utils.loadArraybuffer(url, done, error);
	};
    server.getMetadata=function(projectId, modelId, done, error){
        const url = "/remote.php/webdav/"+modelId+"_metadata.json"; 
        //"/projects/" + projectId + "/models/" + modelId + "/geometry.xkt";
        utils.loadJSON(url, done, error);
	};

    // Create  BIMViewer that loads data via the Server
    const bimViewer = new BIMViewer(server, {
        canvasElement: document.getElementById("myCanvas"), // WebGL canvas
        explorerElement: document.getElementById("myExplorer"), // Left panel
        toolbarElement: document.getElementById("myToolbar"), // Toolbar
        navCubeCanvasElement: document.getElementById("myNavCubeCanvas"),
        busyModelBackdropElement: document.querySelector(".xeokit-busy-modal-backdrop")
    });

    // Create tooltips on various HTML elements created by BIMViewer
    tippy('[data-tippy-content]', {
        appendTo: function () {
            return document.querySelector('#myViewer')
        }
    });


    // Configure our viewer
    bimViewer.setConfigs({});

    // Log info on whatever objects we click with the BIMViewer's Query tool
    bimViewer.on("queryPicked", (event) => {
        console.log(JSON.stringify(event, null, "\t"));
    });

    bimViewer.getProjectsInfo((projectsInfo) => {
        //console.log(JSON.stringify(projectsInfo, null, "\t"));
    });


    // Project to load into the viewer
    const projectId = requestParams.projectId;
    if (!projectId) {
        return;
    }

    // Viewer configurations
    const viewerConfigs = requestParams.configs;
    if (viewerConfigs) {
        const configNameVals = viewerConfigs.split(",");
        for (let i = 0, len = configNameVals.length; i < len; i++) {
            const configNameValStr = configNameVals[i];
            const configNameVal = configNameValStr.split(":");
            const configName = configNameVal[0];
            const configVal = configNameVal[1];
            bimViewer.setConfig(configName, configVal);
        }
    }
   
    // Load a project
    bimViewer.loadProject(projectId, () => {

            // The project may load one or models initially.

            // Withe request params, we can also specify:
            //  - models to load
            // - explorer tab to open


            const modelId = requestParams.modelId;
            if (modelId) {
                bimViewer.loadModel(modelId);
            }

            const tab = requestParams.tab;
            if (tab) {
                bimViewer.openTab(tab);
            }

            //
            window.setInterval((function () {
                var lastHash = "";
                return function () {
                    const currentHash = window.location.hash;
                    if (currentHash !== lastHash) {
                        parseHashParams();
                        lastHash = currentHash;
                    }
                };
            })(), 200);
        },
        (errorMsg) => {
            console.error(errorMsg);
        });

    function getRequestParams() {
        var vars = {};
        window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, (m, key, value) => {
            vars[key] = value;
        });
        return vars;
    }

    function parseHashParams() {
        const params = getHashParams();
        const actionsStr = params.actions;
        if (!actionsStr) {
            return;
        }
        const actions = actionsStr.split(",");
        if (actions.length === 0) {
            return;
        }
        for (var i = 0, len = actions.length; i < len; i++) {
            const action = actions[i];
            switch (action) {
                case "focusObject":
                    const objectId = params.objectId;
                    if (!objectId) {
                        console.error("Param expected for `focusObject` action: 'objectId'");
                        break;
                    }
                    bimViewer.setAllObjectsSelected(false);
                    bimViewer.setObjectsSelected([objectId], true);
                    bimViewer.flyToObject(objectId, () => {
                        // FIXME: Showing objects in tabs involves scrolling the HTML within the tabs - disable until we know how to scroll the correct DOM element. Otherwise, that function works OK

                        // bimViewer.showObjectInObjectsTab(objectId);
                        // bimViewer.showObjectInClassesTab(objectId);
                        // bimViewer.showObjectInStoreysTab(objectId);
                    });
                    break;
                case "focusObjects":
                    const objectIds = params.objectIds;
                    if (!objectIds) {
                        console.error("Param expected for `focusObjects` action: 'objectIds'");
                        break;
                    }
                    const objectIdArray = objectIds.split(",");
                    bimViewer.setAllObjectsSelected(false);
                    bimViewer.setObjectsSelected(objectIdArray, true);
                    bimViewer.viewFitObjects(objectIdArray, () => {
                    });
                    break;
                case "clearFocusObjects":
                    bimViewer.setAllObjectsSelected(false);
                    bimViewer.viewFitAll();
                    // TODO: view fit nothing?
                    break;
                case "openTab":
                    const tabId = params.tabId;
                    if (!tabId) {
                        console.error("Param expected for `openTab` action: 'tabId'");
                        break;
                    }
                    bimViewer.openTab(tabId);
                    break;
                default:
                    console.error("Action not supported: '" + action + "'");
                    break;
            }
        }
    }

    function getHashParams() {
        const hashParams = {};
        let e;
        const a = /\+/g;  // Regex for replacing addition symbol with a space
        const r = /([^&;=]+)=?([^&;]*)/g;
        const d = function (s) {
            return decodeURIComponent(s.replace(a, " "));
        };
        const q = window.location.hash.substring(1);
        while (e = r.exec(q)) {
            hashParams[d(e[1])] = d(e[2]);
        }
        return hashParams;
    }
    var hide_flag = false;
        document.getElementById("left-arrow-img").style.display = 'block';
        document.getElementById("right-arrow-img").style.display = 'none';
        document.getElementById('panel_control_btn').addEventListener('click', function() {
            var element = document.getElementById("myViewer");
            var element1 = document.getElementById("myExplorer");
            if (hide_flag == false) {
                element.classList.add("panel-hide");
                element1.classList.remove("active");
                setTimeout(function() {
                    document.getElementById("left-arrow-img").style.display = 'none';
                    document.getElementById("right-arrow-img").style.display = 'block';
                }, 600);
            } else {
                element.classList.remove("panel-hide");
                element1.classList.add("active");
                setTimeout(function() {
                    document.getElementById("left-arrow-img").style.display = 'block';
                    document.getElementById("right-arrow-img").style.display = 'none';
                }, 600);
            }
            hide_flag = !hide_flag;
        });

        document.getElementById('hide_panel_btn_mobile').addEventListener('click', function() {
            var element = document.getElementById("myViewer");
            var element1 = document.getElementById("myExplorer");
            if (hide_flag == false) {
                element.classList.add("panel-hide");
                element1.classList.remove("active");
            } else {
                element.classList.remove("panel-hide");
                element1.classList.add("active");
            }
            hide_flag = !hide_flag;
        });

        document.getElementById('xeokit_close_btn').addEventListener('click', function() {
            location.reload();
        });

        var autoExpand = function (field) {
            // Reset field height
            field.style.height = 'inherit';

            // Get the computed styles for the element
            var computed = window.getComputedStyle(field);

            // Calculate the height
            var height = parseInt(computed.getPropertyValue('border-top-width'), 10)
                        + parseInt(computed.getPropertyValue('padding-top'), 10)
                        + field.scrollHeight
                        + parseInt(computed.getPropertyValue('padding-bottom'), 10)
                        + parseInt(computed.getPropertyValue('border-bottom-width'), 10);
            field.style.height = height + 'px';
        };

        document.addEventListener('input', function (event) {
            if (event.target.tagName.toLowerCase() !== 'textarea') return;
            autoExpand(event.target);
        }, false);

        var tab_buttons = document.getElementsByClassName('xeokit-tab-btn');
        for (var i=0; i<tab_buttons.length; i++) {
            tab_buttons[i].addEventListener('click', function() {
                document.getElementsByClassName('xeokit-objects xeokit-tree-panel')[0].scrollLeft = 0;
                document.getElementsByClassName('xeokit-classes xeokit-tree-panel')[0].scrollLeft = 0;
                document.getElementsByClassName('xeokit-storeys xeokit-tree-panel')[0].scrollLeft = 0;
            });
        }
    window.bimViewer = bimViewer; // For debugging

};
