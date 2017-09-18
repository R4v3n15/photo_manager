<?php

// use Imagine\Gd\Image;
use Imagine\Image\Box;
use Imagine\Image\Point;

class AvatarModel
{

    /**
     * Gets the user's avatar file path
     * @param $user_id integer The user's id
     * @return string avatar picture path
     */
    public static function getPublicUserAvatarFilePathByUserId($user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("SELECT user_has_avatar FROM users WHERE user_id = :user_id LIMIT 1");
        $query->execute(array(':user_id' => $user_id));

        if ($query->fetch()->user_has_avatar) {
            return Config::get('URL') . Config::get('PATH_AVATARS_PUBLIC') . $user_id . '.jpg';
        }

        return Config::get('URL') . Config::get('PATH_AVATARS_PUBLIC') . Config::get('AVATAR_DEFAULT_IMAGE');
    }

    /**
     * Create an avatar picture (and checks all necessary things too)
     * TODO decouple
     * TODO total rebuild
     */
    public static function createAvatar($folder)
    {
        // check avatar folder writing rights, check if upload fits all rules
        if (self::isAvatarFolderWritable() AND self::validateImageFile()) {

            // create a jpg file in the avatar folder, write marker to database
            $user = Session::get('user_id');
            $target_path = Config::get('PATH_AVATARS') . $user;
            $demo_path   = Config::get('PATH_GUEST_PHOTO'). $folder .'/';
            $file_name   = $_FILES['avatar_file']['name'];
            $file_temp   = $_FILES['avatar_file']['tmp_name'];
            $file_size   = Config::get('AVATAR_SIZE');

            $upload = self::resizeAvatarImage($file_name, $file_temp, $target_path, $demo_path, $folder);
            if ($upload) {
                Session::add('feedback_positive', Text::get('FEEDBACK_AVATAR_UPLOAD_SUCCESSFUL'));
            } else {
                Session::add('feedback_negative', Text::get('FEEDBACK_AVATAR_IMAGE_UPLOAD_FAILED'));
            }
        }
    }

    public static function resizeAvatarImage($file_name, $file_temp, $target_path, $demo_path, $folder) {
        //->Temp Directory to store pictures before to send
        $tempDir = Config::get('PATH_GUEST_PHOTO');
        $uploadfile = $tempDir . basename($file_name);
        $thumb_path = $tempDir.'thumb_'.basename($file_name);
        $imagine    = new Imagine\Gd\Imagine();
        $thumb_size = new Imagine\Image\Box(200, 200);
        // $thumb_mode = Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
        $img_data   = getimagesize($file_temp);
        $img_width  = $img_data[0];
        $img_height = $img_data[1];
        $wtr_mark   = Config::get('PATH_GUEST_PHOTO') . 'mark.png';
        $img_mark   = $imagine->open($wtr_mark);

        $new_size = new Box(960, 640);
        $setting  = new Point(480, 280);

        //->If image is portrait
        if ($img_width < $img_height) {
            $new_size = new Box(431, 647);
            $setting  = new Point(150, 280);
        }

        if (move_uploaded_file($file_temp, $uploadfile)) {
            //->Resizing image
            $image = $imagine->open($uploadfile)
                             ->resize($new_size)
                             ->paste($img_mark, $setting)
                             ->save($uploadfile, array('jpeg_quality' => 85));

            //->Create thumbnail image
            $thumb = $imagine->open($uploadfile)
                             ->thumbnail($thumb_size)
                             // ->thumbnail($thumb_size, $thumb_mode) to make thumbnail square
                             ->save($thumb_path);
                             
            UploadModel::photoUploader($uploadfile, $folder);                
            UploadModel::photoUploader($thumb_path, $folder.'/thumbs');
            //->Destroy the thumbnail temp
            // unlink($thumb_path);
            return true;
        }
        return false;
    }

    /**
     * Checks if the avatar folder exists and is writable
     */
    public static function isAvatarFolderWritable()
    {
        if (is_dir(Config::get('PATH_AVATARS')) AND is_writable(Config::get('PATH_AVATARS'))) {
            return true;
        }

        Session::add('feedback_negative', Text::get('FEEDBACK_AVATAR_FOLDER_DOES_NOT_EXIST_OR_NOT_WRITABLE'));
        return false;
    }

    /**
     * Validates the image
     * Only accepts gif, jpg, png types
     * @return bool
     */
    public static function validateImageFile()
    {
        if (!isset($_FILES['avatar_file'])) {
            Session::add('feedback_negative', Text::get('FEEDBACK_AVATAR_IMAGE_UPLOAD_FAILED'));
            return false;
        }

        // if input file too big (>5MB)
        if ($_FILES['avatar_file']['size'] > 25000000) {
            Session::add('feedback_negative', Text::get('FEEDBACK_AVATAR_UPLOAD_TOO_BIG'));
            return false;
        }

        // get the image width, height and mime type
        $image_proportions = getimagesize($_FILES['avatar_file']['tmp_name']);

        // if input file too small, [0] is the width, [1] is the height
        if ($image_proportions[0] < Config::get('AVATAR_SIZE') OR $image_proportions[1] < Config::get('AVATAR_SIZE')) {
            Session::add('feedback_negative', Text::get('FEEDBACK_AVATAR_UPLOAD_TOO_SMALL'));
            return false;
        }

        // if file type is not jpg, gif or png
        if (!in_array($image_proportions['mime'], array('image/jpeg', 'image/gif', 'image/png'))) {
            Session::add('feedback_negative', Text::get('FEEDBACK_AVATAR_UPLOAD_WRONG_TYPE'));
            return false;
        }

        return true;
    }


    /**
     * Delete a user's avatar
     *
     * @param int $userId
     * @return bool success
     */
    public static function deleteAvatar($userId)
    {
        if (!ctype_digit($userId)) {
            Session::add("feedback_negative", Text::get("FEEDBACK_AVATAR_IMAGE_DELETE_FAILED"));
            return false;
        }

        // try to delete image, but still go on regardless of file deletion result
        self::deleteAvatarImageFile($userId);

        $database = DatabaseFactory::getFactory()->getConnection();

        $sth = $database->prepare("UPDATE users SET user_has_avatar = 0 WHERE user_id = :user_id LIMIT 1");
        $sth->bindValue(":user_id", (int)$userId, PDO::PARAM_INT);
        $sth->execute();

        if ($sth->rowCount() == 1) {
            Session::set('user_avatar_file', self::getPublicUserAvatarFilePathByUserId($userId));
            Session::add("feedback_positive", Text::get("FEEDBACK_AVATAR_IMAGE_DELETE_SUCCESSFUL"));
            return true;
        } else {
            Session::add("feedback_negative", Text::get("FEEDBACK_AVATAR_IMAGE_DELETE_FAILED"));
            return false;
        }
    }

    /**
     * Removes the avatar image file from the filesystem
     *
     * @param integer $userId
     * @return bool
     */
    public static function deleteAvatarImageFile($userId)
    {
        // Check if file exists
        if (!file_exists(Config::get('PATH_AVATARS') . $userId . ".jpg")) {
            Session::add("feedback_negative", Text::get("FEEDBACK_AVATAR_IMAGE_DELETE_NO_FILE"));
            return false;
        }

        // Delete avatar file
        if (!unlink(Config::get('PATH_AVATARS') . $userId . ".jpg")) {
            Session::add("feedback_negative", Text::get("FEEDBACK_AVATAR_IMAGE_DELETE_FAILED"));
            return false;
        }

        return true;
    }
}
