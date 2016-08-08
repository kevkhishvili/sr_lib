<?php
namespace uploader\lib;

class Uploader
{

    private $path = "/pic/";

    private $allow_ext = [
        'gif',
        'png',
        'jpg',
        'jpeg'
    ];

    private $input_name = "file";

    private $file_ext;

    private $max_size = 1048576;

    public function setParams($path, $input_name = "", $allow_ext = "")
    {
        $this->chekPath($path);
        
        $this->path = (trim($path) != "NULL") ? $path : $this->path;
        $this->allow_ext = ($allow_ext != "NULL") ? $allow_ext : $this->allow_ext;
        $this->input_name = (trim($input_name) != "NULL") ? $input_name : $this->input_name;
    }

    public function setMaxSize($max_size)
    {
        $this->path = $max_size;
    }
    
    // download file
    public function downloadFile($url)
    {
        $ext_by_mime = $this->getImgType($url);
        
        $name = basename($url);
        
        $size = $this->retrieveRemoteFileSize($url);
        
        if ($this->checkExt($ext_by_mime) == false)
            return;
        
        if ($size > $this->max_size) {
            echo " MAXIMUM UPLOAD FILE SIZE " . $this->max_size;
            return;
        }
        
        try {
            $file = fopen($url, 'rb');
            
            if ($file) {
                
                $new_file = fopen($_SERVER['DOCUMENT_ROOT'] . "/" . $this->path . "/" time()."_". md5($name) . "." . $ext_by_mime, 'wb');
                
                if ($new_file) {
                    while (! feof($file)) {
                        fwrite($new_file, fread($file, 1024 * 8), 1024 * 8);
                    }
                }
            } else
                throw new \Exception("<div>URL ERROR: </div>");
            
            if ($file) {
                fclose($file);
            }
            if ($new_file) {
                fclose($new_file);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
    
    // upload file
    public function uploadFile()
    {
        $file_name = $_FILES[$this->input_name]["name"];
        
        // проверка на расширение файла
        $this->file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        
        if ($this->checkExt($this->file_ext) == false)
            return;
            
            // проверка типа айла посредством MIME-типов
        $ext_by_mime = $this->getImgType($_FILES[$this->input_name]["tmp_name"]);
        
        if ($this->checkExt($ext_by_mime) == false)
            return;
        
        if ($_FILES[$this->input_name]["size"] > $this->max_size) {
            echo " MAXIMUM UPLOAD FILE SIZE " . $this->max_size;
            return;
        }
        
        if (is_dir($_SERVER['DOCUMENT_ROOT'] . "/" . $this->path)) {} else {
            echo " UPLOAD DIR NOT FOUND (" . $this->path . ")";
            exit();
        }
        
        $file_name = $this->genNewFileName();
        
        try {
            if (! move_uploaded_file($_FILES[$this->input_name]["tmp_name"], $_SERVER['DOCUMENT_ROOT'] . "/" . $this->path . $file_name)) {
                throw new \Exception('<div>UPLOAD ERROR: ' . $this->errorNumToString($_FILES[$this->input_name]['error'] . "</div>"));
                return false;
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        
        return $file_name;
    }

    private function chekPath($path)
    {
        if ($path != "NULL" && is_dir($_SERVER['DOCUMENT_ROOT'] . "/" . $path) == false) {
            echo "PATH ERROR";
            exit();
        }
    }

    private function genNewFileName()
    {
        $file_name = md5(time() . rand(1000, 9000) . "_" . $_FILES[$this->input_name]["name"]) . "." . $this->file_ext;
        
        if ($this->checkDuplicate($file_name) != false)
            return $file_name;
        
        return $this->genNewFileName();
    }

    private function checkDuplicate($file_name)
    {
        if (is_file($_SERVER['DOCUMENT_ROOT'] . "/" . $this->path . "/" . $file_name))
            return false;
        return true;
    }

    public function getImgType($URL)
    {
        $img_info = getimagesize($URL);
        try {
            if ($img_info == null) {
                throw new \Exception('<div>FILE ERROR</div>');
                return false;
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
        
        $myme = $img_info["mime"];
        $type = "";
        
        switch ($myme) {
            case "image/jpg":
                $type = "jpg";
                break;
            case "image/jpeg":
                $type = "jpg";
                break;
            case "image/png":
                $type = "png";
                break;
            case "image/gif":
                $type = "gif";
                break;
        }
        return $type;
    }

    private function errorNumToString($n)
    {
        $Errors = array(
            0 => 'There is no error, the file uploaded with success',
            1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            3 => 'The uploaded file was only partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk.',
            8 => 'A PHP extension stopped the file upload.'
        );
        return $Errors[$n];
    }

    private function checkExt($ext)
    {
        try {
            if (! in_array(strtolower($ext), $this->allow_ext)) {
                
                throw new \Exception('<div>EXTENSIONS ERROR</div>');
                
                return false;
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
        return true;
    }

    private function myUrlEncode($string)
    {
        $entities = array(
            ' '
        );
        $replacements = array(
            "%20"
        );
        return str_replace($entities, $replacements, $string);
    }

    function retrieveRemoteFileSize($url)
    {
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        
        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        
        curl_close($ch);
        return $size;
    }
}
