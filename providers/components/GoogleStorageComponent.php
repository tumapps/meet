<?php

namespace app\providers\components;

use Yii;
use Google\Cloud\Storage\StorageClient;
use yii\base\Component;

class GoogleStorageComponent extends Component
{
    public $serviceAccountPath = '@app/config/crackit-cloud-8017276a55dd.json';
    public $projectId = 'crackit-cloud';
    public $bucketName = 'crackit-technologies';

    private $storage;

    public function init()
    {
        parent::init();

        $this->storage = new StorageClient([
            'keyFilePath' => Yii::getAlias($this->serviceAccountPath),
        ]);
    }

    public function uploadFile($filePath, $destination, $isPublic = false)
    {
        $bucket = $this->storage->bucket($this->bucketName);
        $options = ['name' => $destination];

        if ($isPublic) {
            $options['predefinedAcl'] = 'publicRead';
        }

        $object = $bucket->upload(fopen($filePath, 'r'), $options);

        return $object;
    }

    public function downloadFile($fileName, $destination)
    {
        $bucket = $this->storage->bucket($this->bucketName);
        $object = $bucket->object($fileName);

        if (!$object->exists()) {
            return [
                'status' => false,
                'message' => 'File not found.',
            ];
        }

        $object->downloadToFile($destination);
        return [
            'status' => true,
            'info' => $object->info(),
        ];
    }

     
    public function deleteFile($fileName)
    {
        $bucket = $this->storage->bucket($this->bucketName);
        $object = $bucket->object($fileName);

        if (!$object->exists()) {
            return [
                'status' => false,
                'message' => 'File not found.',
            ];
        }

        $object->delete();
        return [
            'status' => true,
            'message' => 'File deleted successfully.',
        ];
    }
     
    public function getFiles($path)
    {
        $bucket = $this->storage->bucket($this->bucketName);
        $objects = $bucket->objects(['prefix' => $path]);

        $files = [];
        foreach ($objects as $object) {
            $name = $object->name();
            $relativeName = substr($name, strlen($path));

            $files[] = [
                'name' => $relativeName,
                'size' => $object->info()['size'],
                'updated' => $object->info()['updated'],
                'md5Hash' => $object->info()['md5Hash'],
                'crc32c' => $object->info()['crc32c'],
                'timeCreated' => $object->info()['timeCreated'],
                'contentType' => $object->info()['contentType'],
                'selfLink' => $object->info()['selfLink'],
                'mediaLink' => $object->info()['mediaLink'],
                'generation' => $object->info()['generation'],
                'etag' => $object->info()['etag'],
                'bucket' => $object->info()['bucket'],
            ];
        }

        if (empty($files)) {
            return [
                'status' => false,
                'message' => 'Path not found or no files in the specified path.',
            ];
        }

        $directoryTree = $this->buildDirectoryTree($files);

        return [
            'status' => true,
            'files' => $directoryTree,
        ];
    }


    private function buildDirectoryTree($files)
    {
        $tree = [];

        foreach ($files as $file) {
            if (!isset($file['name']) || !is_string($file['name'])) {
                throw new \InvalidArgumentException('File name must be a string.');
            }

            $parts = explode('/', $file['name']);
            $current = &$tree;

            foreach ($parts as $index => $part) {
                if ($part === '') {
                    continue;
                }

                if (!isset($current[$part])) {
                    if ($index < count($parts) - 1) {
                        // It's a folder
                        $current[$part] = [
                            'name' => $part,
                            'isFolder' => true,
                            'contents' => []
                        ];
                    } else {
                        // It's a file
                        $extension = '';
                        $dotPosition = strrpos($file['name'], '.');
                        if ($dotPosition !== false) {
                             $extension = '.'.substr($file['name'], $dotPosition + 1);
                        }
                        $current[$part] = array_merge($file, [
                            'name' => $part,
                            'extension' => $extension,
                            // 'isFolder' => false
                        ]);
                    }
                }

                if ($index < count($parts) - 1) {
                    // Navigate deeper into the tree
                    $current = &$current[$part]['contents'];
                }
            }
        }

        return $tree;
    }

    
    public function calculateTotalUploadSize($uploadedFiles)
    {
        $totalSize = 0;

        foreach ($uploadedFiles as $file) {
            $totalSize += $file->size;
        }

        return $totalSize;
    }

}
