<div class="container">
    <h1>Edit your avatar</h1>
    <?php 
        echo $_SERVER['SERVER_ADDR'].'<br>';
        echo $_SERVER['REMOTE_ADDR'].'<br>';
        exec('ipconfig /all', $response);
        $ip = explode(':', $response[35]);
        var_dump($ip[1]); 
    ?>

    <!-- echo out the system feedback (error and success messages) -->
    <?php $this->renderFeedbackMessages(); $base = Config::get('URL'); ?>

    <div class="box">

        <!-- <div class="feedback info">
            If you still see the old picture after uploading a new one: Hard-Reload the page with F5!.
        </div> -->
        <h3 class="text-center">Create Folder</h3>
        <button type="button" id="folder_creator" />Create Folder</button>
        <h3 class="text-center">Upload Images</h3>
        <form action="<?= $base; ?>user/uploadAvatar_action" method="post" enctype="multipart/form-data">
            <label for="avatar_file">Select an avatar image:</label><br>
            <input type="file" name="avatar_file" required />
            <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
            <input type="hidden" id="folder_id" name="folder_id" />
            <input type="hidden" id="folder_path" name="folder_path" />
            <input type="submit" value="Upload image" />
        </form>
    </div>

    <div class="box">
        <h3>Close Current Process</h3>
        <button type="button" id="close_folder" />Close Folder</button>
    </div>
</div>