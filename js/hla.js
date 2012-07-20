/* Declare a new method XMLHttpRequest.prototype.sendAsBinary() */
if (!('sendAsBinary' in XMLHttpRequest.prototype)) {
  XMLHttpRequest.prototype.sendAsBinary = function(string) {
    var bytes = Array.prototype.map.call(string, function(c) {
      return c.charCodeAt(0) & 0xff;
    });
    this.send(new Uint8Array(bytes).buffer);
  };
}

/*
 * @description        Uploads a file via multipart/form-data, via a Canvas elt
 * @param url  String: Url to post the data
 * @param name String: name of form element
 * @param fn   String: Name of file
 * @param canvas HTMLCanvasElement: The canvas element.
 * @param type String: Content-Type, eg image/png
 ***/
function postCanvasToURL(url, name, fn, canvas, type) {
  var data = canvas.toDataURL(type);
  data = data.replace('data:' + type + ';base64,', '');

  var xhr = new XMLHttpRequest();
  xhr.open('POST', url, true);
  var boundary = 'ohaiimaboundary';
  xhr.setRequestHeader(
    'Content-Type', 'multipart/form-data; boundary=' + boundary);
  xhr.sendAsBinary([
    '--' + boundary,
    'Content-Disposition: form-data; name="' + name + '"; filename="' + fn + '"',
    'Content-Type: ' + type,
    '',
    atob(data),
    '--' + boundary + '--'
  ].join('\r\n'));
}

$(document).ready(function () {
	$('#print-page').click(function() {
		html2canvas( [ document.body ], {
		    onrendered: function( canvas ) {
		        /* canvas is the actual canvas element, 
		           to append it to the page call for example 
		           document.body.appendChild( canvas );
		        */
		        var dataUrl = canvas.toDataURL();
		        window.open(dataUrl, "toDataURL() image");
        	}
        }
        );

	});

});