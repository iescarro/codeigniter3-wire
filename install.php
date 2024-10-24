<?php

function copy_files($source, $target)
{
  if (!is_dir($target)) {
    mkdir($target, 0755, true);
  }

  $directoryIterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
  );

  foreach ($directoryIterator as $item) {
    $targetPath = $target . DIRECTORY_SEPARATOR . $directoryIterator->getSubPathName();

    if ($item->isDir()) {
      if (!is_dir($targetPath)) {
        mkdir($targetPath, 0755);
      }
    } else {
      copy($item, $targetPath);
    }
  }
}


$sourceDirs = [
  __DIR__ . '/src/controllers',
  __DIR__ . '/src/core',
];
$targetDirs = [
  __DIR__ . '/../../../application/controllers',
  __DIR__ . '/../../../application/core',
];


$i = 0;
foreach ($sourceDirs as $sourceDir) {
  $targetDir = $targetDirs[$i];
  copy_files($sourceDir, $targetDir);
  $i++;
}

echo "All files copied successfully!\n";
