function Ajax (type, data, cache) {

	this.type     = type;
	this.data     = data;
	this.cache    = cache;
	this.dataType = 'json';
	this.url      = window.ajaxurl || window.llms.ajaxurl;

}

Ajax.prototype.check_voucher_duplicate = function () {

	jQuery.ajax({
		type 		: this.type,
		url			: this.url,
		data 		: this.data,
		cache		: this.cache,
		dataType	: this.dataType,
		success		: function(response) {
			llms_on_voucher_duplicate( response.duplicates );
		}
	});
};
