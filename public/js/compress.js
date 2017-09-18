var Compress = {
	pic: {
		qty: 80,
		out_format: 'jpg',
		source: null,
		target: {},
	},

	initialize: function(){
		console.log('Image Compresor initialize');
	},

	setResource: function(){
		this.pic.source_img = $('#source_img');
		this.pic.target_img = $('#target_img');
	},

	prepareCompress: function(){
		let that = this;
		$('#uploader').on('click', function(){
			that.pic.target[src] = that.compress(that.pic.source, that.pic.qty, that.pic.out_format).src;
		});
	},

	compress: function(source_img_obj, quality, output_format){

             var mime_type = "image/jpeg";
             if(typeof output_format !== "undefined" && output_format=="png"){
                mime_type = "image/png";
             }
             
             var cvs = document.createElement('canvas');
             cvs.width = source_img_obj.naturalWidth;
             cvs.height = source_img_obj.naturalHeight;
             var ctx = cvs.getContext("2d").drawImage(source_img_obj, 0, 0);
             var newImageData = cvs.toDataURL(mime_type, quality/100);
             var result_image_obj = new Image();
             result_image_obj.src = newImageData;

             // console.log(result_image_obj);
             return result_image_obj;
        },

}