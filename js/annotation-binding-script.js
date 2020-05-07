// addEventListener support for IE8
function bindEvent(element, eventName, eventHandler) {
    if (element.addEventListener){
        element.addEventListener(eventName, eventHandler, false);
    } else if (element.attachEvent) {
        element.attachEvent('on' + eventName, eventHandler);
    }
}
var fileId = 1;
var baseUrl = OC.generateUrl('/apps/files_ifcviewer_annotation');
var iframeEl = document.getElementById('viewer-iframe');
var sendMessage = function(msg) {
    iframeEl.contentWindow.postMessage(msg, '*');
};
var createAnnotation = function (fileId, annotation) {
   
};
var updateAnnotation = function (fileId, annotation) {
	if (annotation.viewer_id.startsWith("issue")) {
		$.ajax({
		    url: baseUrl + '/annotations/'+annotation.viewer_id.replace("issue_",""),
		    type: 'PUT',
		    contentType: 'application/json',
		    data: JSON.stringify({ file_id: fileId, title: annotation.title, description: annotation.description, wx: annotation.wPos[0], wy: annotation.wPos[1], wz: annotation.wPos[2] })
		}).done(function (response) {		    
		}).fail(function (response, code) {
		    // handle failure
		});
	} else {
		$.ajax({
		    url: baseUrl + '/annotations',
		    type: 'POST',
		    contentType: 'application/json',
		    data: JSON.stringify({ file_id: fileId, title: annotation.title, description: annotation.description, wx: annotation.wPos[0], wy: annotation.wPos[1], wz: annotation.wPos[2] })
		}).done(function (response) {
			var data = response;			
		    sendMessage({ op: 'update-annotation', data: response})
		}).fail(function (response, code) {
		    // handle failure
		});		
	}

};
var deleteAnnotation = function (fileId, annotation) {

};
var getAnnotations = function (fileId) {
	$.ajax({
	    url: baseUrl + '/annotations?fileId=' + fileId,
	    type: 'GET',
	    contentType: 'application/json'
	}).done(function (response) {
	    sendMessage({ op: 'annotation-list', data: response})
	}).fail(function (response, code) {
	    // handle failure
	});
};
// Listen to message from child window
bindEvent(window, 'message', function (e) {
	var msg = e.data;
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
