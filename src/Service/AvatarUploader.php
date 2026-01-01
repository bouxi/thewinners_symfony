<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class AvatarUploader
{
    /**
     * @param string $avatarDir Dossier absolu d’upload (ex: %kernel.project_dir%/public/uploads/avatars)
     * @param int    $maxSize   Taille max en octets (ex: 2*1024*1024)
     */
    public function __construct(
        private string $avatarDir,
        private int $maxSize
    ) {}

    /**
     * Upload et renvoie le chemin relatif (stockable en BDD).
     * ex: uploads/avatars/xxxx.webp
     */
    public function upload(UploadedFile $file): string
    {
        // --- Sécurité : taille max ---
        $size = $file->getSize();
        if ($size !== null && $size > $this->maxSize) {
            throw new \RuntimeException('Avatar trop volumineux (max 2 Mo).');
        }

        // --- Sécurité : MIME strict ---
        // getMimeType() utilise Fileinfo (fiable côté serveur). Si null, on fallback sur le client (moins fiable).
        $mime = (string) ($file->getMimeType() ?: $file->getClientMimeType());

        $mimeToExt = [
            'image/png'  => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
        ];

        if (!isset($mimeToExt[$mime])) {
            throw new \RuntimeException('Format non autorisé (png/jpg/webp).');
        }

        $ext = $mimeToExt[$mime];

        // --- Assure que le dossier existe ---
        if (!is_dir($this->avatarDir)) {
            if (!@mkdir($this->avatarDir, 0775, true) && !is_dir($this->avatarDir)) {
                throw new \RuntimeException("Impossible de créer le dossier d'upload des avatars.");
            }
        }

        // --- Nom de fichier aléatoire ---
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;

        // --- Upload ---
        try {
            $file->move($this->avatarDir, $filename);
        } catch (FileException $e) {
            throw new \RuntimeException("Échec de l'upload de l'avatar.");
        }

        // Chemin relatif public (utilisable avec asset())
        return 'uploads/avatars/' . $filename;
    }

    /**
     * Supprime un ancien avatar (si fichier existe).
     * Attend un chemin relatif type "uploads/avatars/xxx.png"
     */
    public function delete(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        // Sécurité : on supprime uniquement dans uploads/avatars/
        if (!str_starts_with($relativePath, 'uploads/avatars/')) {
            return;
        }

        // basename() protège contre les chemins chelous
        $fullPath = rtrim($this->avatarDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($relativePath);

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    /**
     * Remplace un avatar : upload + suppression de l’ancien.
     * Retourne le nouveau chemin relatif.
     */
    public function replace(UploadedFile $newFile, ?string $oldRelativePath): string
    {
        $newPath = $this->upload($newFile);
        $this->delete($oldRelativePath);

        return $newPath;
    }
}
