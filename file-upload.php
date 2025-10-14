<?php
function file_cleanup(array $uploadedFiles, bool &$errors = false, string &$error_list = "", string $error_msg = "", array &$filenames = [])
{
    foreach ($uploadedFiles as $uploadedFile) {
        if (file_exists($uploadedFile)) {
            unlink($uploadedFile);
        }
    }
    if ($error_msg !== "") {
        $errors = true;
        $error_list .= "<li>$error_msg</li>";
        $filenames = [];
    }
}

function upload_files(
    string $input_name,
    string $uploadDir,
    array &$uploadedFiles,
    bool &$errors,
    string &$error_list,
    string $chosenFileName = "",
    string $fileTopic = "",
    array $allowedTypes = ["pdf", "jpg", "jpeg", "png"],
) {
    $fileInput = $_FILES[$input_name];
    if (isset($fileInput)) {
        $filenames = [];
        $files = is_iterable($fileInput["error"]) ? $fileInput["error"] : [$fileInput["error"]];
        $fileInputNames = is_iterable($fileInput["name"]) ? $fileInput["name"] : [$fileInput["name"]];
        $fileInputTmpNames = is_iterable($fileInput["tmp_name"]) ? $fileInput["tmp_name"] : [$fileInput["tmp_name"]];
        foreach ($files as $key => $error) {
            $displayKey = $key + 1;

            $file = $fileInputNames[$key];
            if ($file != "") {
                if ($error !== UPLOAD_ERR_OK) {
                    file_cleanup(
                        uploadedFiles: $uploadedFiles,
                        errors: $errors,
                        error_list: $error_list,
                        error_msg: "Something went wrong while trying to upload the file(s)",
                        filenames: $filenames
                    );
                    break;
                }

                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedTypes)) {
                    file_cleanup(
                        uploadedFiles: $uploadedFiles,
                        errors: $errors,
                        error_list: $error_list,
                        error_msg: "File type for files must match: " . implode(", ", $allowedTypes),
                        filenames: $filenames
                    );
                    break;
                }

                $fileName = "";
                if ($chosenFileName == "") {
                    if ($fileTopic != "") {
                        $fileName = "$fileTopic File $displayKey";
                    } else {
                        $fileName = explode(".", $file)[0];
                    }
                } else {
                    $fileName = $chosenFileName;
                }
                $fileName .= " " . date("Y-m-d_h-i-sA") . ".$ext";
                $fileName = preg_replace("/[^a-zA-Z0-9\.\-_\s]/", "", $fileName);
                $uploadFilePath = $uploadDir . $fileName;

                $fileTmpPath = $fileInputTmpNames[$key];
                if (!move_uploaded_file($fileTmpPath, $uploadFilePath)) {
                    file_cleanup(
                        uploadedFiles: $uploadedFiles,
                        errors: $errors,
                        error_list: $error_list,
                        error_msg: "Something went wrong while trying to upload the file(s)",
                        filenames: $filenames
                    );
                    break;
                }

                // Keep track of successfully uploaded files
                $uploadedFiles[] = $uploadFilePath;
                $filenames[] = $fileName;
            }
        }
        return $filenames;
    } else {
        $errors = true;
        $error_list .= "<li>Invalid file input</li>";
        return [];
    }
}