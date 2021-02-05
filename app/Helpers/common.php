<?php
if (!function_exists('uploadImage')) {
    function uploadImage($destinationPath, $photo, $ratio = [], $old_photo = null)
    {
        if (!file_exists($destinationPath)) {
            @mkdir($destinationPath, 0777, true);
        }

        if (!is_null($old_photo) && file_exists($old_photo)) {
            unlink($old_photo);
        }

        $img = Image::make($photo->getRealPath());
        if (!empty($ratio)) {
            $img->resize($ratio[0], $ratio[1], function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        $img->save($destinationPath . '/' . uniqid(time()) . '.' . $photo->getClientOriginalExtension());
        return $img->basename;
    }
}
