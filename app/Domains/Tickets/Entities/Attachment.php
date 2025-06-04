<?php

namespace App\Domains\Tickets\Entities;

use DateTime;

class Attachment
{
    private int $id;
    private int $ticketId;
    private string $fileName;
    private string $filePath;
    private string $typeMime;
    private int $fileSize;
    private DateTime $uploadDate;

    public function __construct(
        int $id,
        int $ticketId,
        string $fileName,
        string $filePath,
        string $typeMime,
        int $fileSize,
        ?DateTime $uploadDate = null
    ) {
        $this->id = $id;
        $this->ticketId = $ticketId;
        $this->fileName = $fileName;
        $this->filePath = $filePath;
        $this->typeMime = $typeMime;
        $this->fileSize = $fileSize;
        $this->uploadDate = $uploadDate ?? new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTicketId(): int
    {
        return $this->ticketId;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getTypeMime(): string
    {
        return $this->typeMime;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function getUploadDate(): DateTime
    {
        return $this->uploadDate;
    }

    // Crée une représentation sous forme de tableau de l'entité
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticketId,
            'file_name' => $this->fileName,
            'file_path' => $this->filePath,
            'type_mime' => $this->typeMime,
            'file_size' => $this->fileSize,
            'upload_date' => $this->uploadDate->format('Y-m-d H:i:s'),
        ];
    }

    // Crée une entité à partir d'un tableau de données
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['ticket_id'],
            $data['file_name'],
            $data['file_path'],
            $data['type_mime'],
            $data['file_size'],
            isset($data['upload_date']) ? new DateTime($data['upload_date']) : null
        );
    }
}