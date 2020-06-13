var baseUrl = OC.generateUrl('/apps/files_ifcviewer_annotation');
//var iframeEl = document.getElementById('ifcframe');
var sendMessage = function(msg) {
    ($('#ifcframe')[0]).contentWindow.postMessage(msg, '*');
};
var createAnnotation = function (fileId, annotation) {
   
};
/*
            'eye_x' => $this->eyex,
            'eye_y' => $this->eyey,
            'eye_z' => $this->eyez,      
            'up_x' => $this->upx,
            'up_y' => $this->upy,
            'up_z' => $this->upz,      
            'look_x' => $this->lookx,
            'look_y' => $this->looky,
            'look_z' => $this->lookz,
*/
var updateAnnotation = function (fileId, annotation) {
	if (annotation.viewer_id.startsWith("issue")) {
		$.ajax({
		    url: baseUrl + '/annotations/'+annotation.viewer_id.replace("issue_",""),
		    type: 'PUT',
		    contentType: 'application/json',
		    data: JSON.stringify({ 
			    file_id: fileId, 
			    title: annotation.title, 
			    description: annotation.description, 
			    status: annotation.status, 
			    wx: annotation.wPos[0], 
			    wy: annotation.wPos[1], 
			    wz: annotation.wPos[2] 
		    })
		}).done(function (response) {
		}).fail(function (response, code) {
		    // handle failure
		});
	} else {
		$.ajax({
		    url: baseUrl + '/annotations',
		    type: 'POST',
		    contentType: 'application/json',
		    data: JSON.stringify({ 
			    file_id: fileId, 
			    title: annotation.title, 
			    description: annotation.description, 
			    wx: annotation.wPos[0], 
			    wy: annotation.wPos[1], 
			    wz: annotation.wPos[2],
                            eye_x: annotation.eye[0],
                            eye_y: annotation.eye[1],
                            eye_z: annotation.eye[2],
                            up_x: annotation.up[0],
                            up_y: annotation.up[1],
                            up_z: annotation.up[2],
                            look_x: annotation.look[0],
                            look_y: annotation.look[1],
                            look_z: annotation.look[2] 
		    })
		}).done(function (response) {
			var data = response;
			data.viewer_id = annotation.viewer_id;
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

