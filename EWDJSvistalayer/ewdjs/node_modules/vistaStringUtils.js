function VistaStringUtils() { }

VistaStringUtils.adjustForNameSearch = function(target) {
	if (target === undefined || target === null || target == "") {
		return "";
	}
	
	var result = target.substr(0, target.length-1); // grab all but last char
	var lastCharASCIICode = target.charCodeAt(target.length-1); // get ASCII value of last target char
	var newChar = String.fromCharCode(lastCharASCIICode-1); // substract one from ASCII value and convert back to char
	return result + newChar + "~"; // concatenate everything but last char with adjusted ASCII value and tilde
};

module.exports = {
	adjustForNameSearch: function(target) {
		return VistaStringUtils.adjustForNameSearch(target);
	}
};