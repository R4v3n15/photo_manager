<?php

class UploadModel
{
    /**
     * Enviar imagenes por curl_post a otro server
     * @return [type] [description]
     */
    public static function photoUploader($filename, $folder) {
        if ($folder === '' || $folder === null) {
            $folder = 'unknow';
        }
        $server_url = Config::get('PATH_URL_UPLOADER') . 'upload/storePhoto';

        if (file_exists($filename)) {
            //->Prepare files to upload
            $uploadfiles = array(
                'filename' => basename($filename),
                'folderguest' => $folder,
                'filedata' => base64_encode(file_get_contents($filename))
            );

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $server_url);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $uploadfiles);
            $response = curl_exec($curl);
            curl_close($curl);
            unlink($filename);
            return true;
        } else {
            return false;
        }

    }

    /**
     *  Note: This works on the target server.
     *  photoReceiver(isset($_POST['filename']) && $_POST['filedata'])
     * @return [boolean] [description]
     */
    public static function photoReceiver($filename, $filedata){
        //-> Target Directory to Store the Photos
        $targetDir = Config::get('PATH_GUEST_PHOTO') . 'guest_folder/';
        file_put_contents(
            $targetDir.$filename, 
            base64_decode($filedata)
        );

        return true;
    }
}
