var Manager = inherit(Main, {

	initialize: function(){
		this.createFolder();
		this.closingProcess();
		this.readingResponse();
		// localStorage.clear();
		if (localStorage.getItem('folder_id')) {
			$('#folder_creator').hide();
			$('#close_folder').show();
			$('#folder_id').val(localStorage.getItem('folder_id'));
			$('#folder_path').val(localStorage.getItem('folder_path'));
		} else {
			$('#folder_creator').show();
			$('#close_folder').hide();
		}
	},

	createFolder: function() {
		let that = this;
		$('#folder_creator').on('click', () => {
			var user = $('#init-data').data('user-csrf');
			that.socket.emit('getFolder', { user: user});
		});
	},

	readingResponse: function() {
		let that = this;

		that.socket.on('setFolder', (data) => {
			if (data.error === false) {
				$('#folder_id').val(data.id);
				$('#folder_path').val(data.folder);
				localStorage.setItem('folder_id', data.id);
				localStorage.setItem('folder_path', data.folder);
				$('#folder_creator').hide();
				$('#close_folder').show();
				// console.log(localStorage.getItem('folder_id'), localStorage.getItem('folder_path'));
			} else {
				console.log('Error de Lectura, Reintentar proceso...');
				$('#folder_creator').click();
			}
		});

		this.socket.on('closedFolder', (data) => {
			if (data.closed === true) {
				var folder = localStorage.getItem('folder_path');
				that.notification('Nuevo Contenido', 'Se han cargado nuevas fotos en la carpeta: '+ folder);
				$('#folder_creator').show();
				$('#close_folder').hide();
				localStorage.clear();
				console.log('End Process');
			} else {
				console.log('Error de Lectura, Reintentar proceso...');
			}
		});
	},

	closingProcess: function(){
		let that  = this;
		$('#close_folder').on('click', () => {
			var user = $('#init-data').data('user-csrf');
			that.socket.emit('closeFolder', { status: 1, guest: user, folder: localStorage.getItem('folder_id') });
		});
	},

	notification: function(title, message) {
		let that = this;
		if(window.Notification && Notification.permission !== "denied") {
			Notification.requestPermission(function(status) {  // status is "granted", if accepted by user
				var n = new Notification(title, { 
					body: message
				}); 
			});
		}
	},


});

Manager.initialize();