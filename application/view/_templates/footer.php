        <div class="footer"></div>
    </div><!-- close class="wrapper" -->

	<script type="text/javascript">
		var _upload_ = "<?php echo Config::get('PATH_URL_UPLOADER'); ?>";
	</script>
	<script src="<?php echo Config::get('URL'); ?>js/jquery.min.js"></script>
	<script src="<?php echo Config::get('URL'); ?>js/libs/socket/socket.io.min.js"></script>

	
	<?php if(Session::userIsLoggedIn()): ?>
		<div id="init-data"
			 data-user-mail="<?= Session::get('user_email'); ?>"
			 data-user-csrf="<?= Session::get('user_id'); ?>"
			 data-user-name="<?= Session::get('user_name'); ?>">
    	</div>
		<script src="<?php echo Config::get('URL'); ?>js/main.js"></script>
		<script src="<?php echo Config::get('URL'); ?>js/image_manager.js"></script>
	<?php endif ?>
</body>
</html>