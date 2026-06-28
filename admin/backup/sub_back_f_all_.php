<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
     // Get real path for our folder
     $rootPath = realpath($bu);
    
     // Initialize archive object
     $zip = new ZipArchive();
     $zip->open("files/".$file_name, ZipArchive::CREATE | ZipArchive::OVERWRITE);
     $zip->setPassword(getenv("BACKUP_PASSWORD") ?: "change-me"); // Showcase placeholder
     
     // Create recursive directory iterator
     /** @var SplFileInfo[] $files */
     $files = new RecursiveIteratorIterator(
         new RecursiveDirectoryIterator($rootPath),
         RecursiveIteratorIterator::LEAVES_ONLY
     );
    
     foreach ($files as $name => $file)
     {
         // Skip directories (they would be added automatically)
         if (!$file->isDir())
         {
             // Get real and relative path for current file
             $filePath = $file->getRealPath();
             $relativePath = substr($filePath, strlen($rootPath) + 1);
             
             if(stripos($relativePath,"admin/backup/files") === false && stripos($relativePath,".js") === false){
                 // Add current file to archive
                 $zip->addFile($filePath, $relativePath);
//                 $zip->setEncryptionName($filePath, ZipArchive::EM_AES_256); 
             }
         }
     }
    
     // Zip archive will be created only after closing object
     $zip->close();
?>
<?php
        }
    }
?>
