// addEventListener support for IE8
function bindEvent(element, eventName, eventHandler) {
    if (element.addEventListener) {
       element.addEventListener(eventName, eventHandler, false);
    } else if (element.attachEvent) {
       element.attachEvent('on' + eventName, eventHandler);
    }
}
// Send a message to the parent
var sendMessage = function (msg) {
    window.parent.postMessage(msg, '*');
};
var onAnnModified = null;
function setOnAnnModified(handler) {
	onAnnModified = handler;
}
