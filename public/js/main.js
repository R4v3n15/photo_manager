//->Self Invoque function
function inherit(base, methods) {
    return $.extend(Object.create(base), methods);
}

var Main = (function(options) {

	var socket     =  undefined,
		connection =  false;

	var socketConnection = function(){
		let that = this;
		this.socket = io('http://localhost:3000/fdc-sales');

		var user = $('#init-data').data('user-name');
		this.socket.on('connect', function() {
			console.log('connected to socket fdc_sales room');
			that.socket.emit('connected', {user : user});
		});
	};

	var tester = function() {
		console.log('Testing inherit method');
	};

	return {
		connect: socketConnection,
		testInherit: tester
	};
})();
Main.connect();