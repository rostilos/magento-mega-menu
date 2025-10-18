<?php
namespace Rostilos\Base\Model;

use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Uploader;

class Upload
{
    protected UploaderFactory $uploaderFactory;

    public function __construct(UploaderFactory $uploaderFactory)
    {
        $this->uploaderFactory = $uploaderFactory;
    }

    public function processUpload($fileId, $destinationFolder, $data, $allowedExts = [])
    {
        $fileName = '';
        try {
            if (isset($data[$fileId]['delete']) && $data[$fileId]['delete']) {
                $imagePath = $destinationFolder . $data[$fileId]['value'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            } else {
                $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
                if ($allowedExts) {
                    $uploader->setAllowedExtensions($allowedExts);
                }
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);
                $result = $uploader->save($destinationFolder);
                unset($result['tmp_name']);
                unset($result['path']);

                $fileName = $result['file'];
            }
        } catch (\Exception $e) {
            if ($e->getCode() != Uploader::TMP_NAME_EMPTY) {
                throw new LocalizedException(new \Magento\Framework\Phrase($e->getMessage()));
            } else {
                if (isset($data[$fileId]['value'])) {
                    $fileName = $data[$fileId]['value'];
                }
            }
        }

        return $fileName;
    }
}
