<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Gère l'upload et la suppression des images de guides.
 */
final class GuideImageUploader
{
    public function __construct(
        private readonly string $guideImagesDirectory,
        private readonly SluggerInterface $slugger,
    ) {
    }

    /**
     * Upload une image et retourne le chemin relatif à stocker en base.
     *
     * Exemple :
     * uploads/guides/guide-demoniste-123456.webp
     */
    public function upload(UploadedFile $file, ?string $baseName = null): string
    {
        $originalFilename = $baseName ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = (string) $this->slugger->slug($originalFilename)->lower();
        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';

        $newFilename = sprintf('%s-%s.%s', $safeFilename, uniqid('', true), $extension);

        try {
            $file->move($this->guideImagesDirectory, $newFilename);
        } catch (FileException $exception) {
            throw new \RuntimeException('Impossible d’uploader l’image du guide.', 0, $exception);
        }

        return 'uploads/guides/' . $newFilename;
    }

    /**
     * Supprime une image si elle existe réellement.
     */
    public function remove(?string $relativePath): void
    {
        if ($relativePath === null || trim($relativePath) === '') {
            return;
        }

        $filename = basename($relativePath);
        $absolutePath = rtrim($this->guideImagesDirectory, '/') . '/' . $filename;

        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }
}