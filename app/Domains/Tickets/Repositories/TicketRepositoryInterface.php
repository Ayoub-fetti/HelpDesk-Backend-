<?php

namespace App\Domains\Tickets\Repositories;

use App\Domains\Tickets\Entities\Ticket;
use App\Domains\Tickets\Entities\Comment;
use App\Domains\Tickets\ValueObjects\StatutTicket;
use App\Domains\Shared\ValueObjects\IdentiteUtilisateur;

interface TicketRepositoryInterface
{
    
    // Trouver un ticket par son ID
  
    public function findById(int $id): ?Ticket;
    
    
    // Récupérer tous les tickets

    public function findAll(): array;
    
    
    // Récupérer les tickets par statut

    public function findByStatut(StatutTicket $statut): array;
    
    
    // Récupérer les tickets d'un utilisateur

    public function findByUser(int $userId): array;
    
    
    // Récupérer les tickets assignés à un technicien

    public function findByTechnician(int $technicianId): array;
    
    
    // Récupérer les tickets par catégorie

    public function findByCategory(int $categoryId): array;
    
    
    // Enregistrer un nouveau ticket

    public function save(Ticket $ticket): int;
    
    
    // Mettre à jour un ticket existant

    public function update(Ticket $ticket): void;
    
    
    // Ajouter un comment à un ticket

    public function addComment(Comment $comment): int;
    
    
    // Récupérer les comments d'un ticket

    public function findCommentsByTicketId(int $ticketId): array;
    
    
    // Rechercher des tickets selon des critères
    
    public function search(array $criteria): array;
    
    
    // Récupérer des statistiques sur les tickets

    public function getStatistics(array $filtres = []): array;
    
    
    // Supprimer un ticket

    public function delete(int $ticketId): bool;
}