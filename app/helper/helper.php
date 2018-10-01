<?php
function saveImage($id=null,$file, $folder)
{
    if (is_null($id)){
        $fileName = $file->getClientOriginalName();
    }else{
        $fileName = $id.'-'.$file->getClientOriginalName();
    }

    $dest = public_path($folder);
    $file->move($dest, $fileName);

    return '/public/' . $folder . '/' . $fileName;
    $image = $file; // your base64 encoded
    $image = str_replace('data:image/png;base64,', '', $image);
    $image = str_replace(' ', '+', $image);
    $imageName = $file->getClientOriginalName().'.png';
    \File::put(public_path(). '/' . $imageName, base64_decode($image));
    return '/public/' . $folder . '/' . $fileName;
}

//function showImage($image, $alt = null, $option = [])
//{
//    return Html::image(url($image), $alt, $option);
//}





