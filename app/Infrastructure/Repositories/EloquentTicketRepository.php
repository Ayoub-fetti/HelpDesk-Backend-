<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Tickets\Entities\Ticket;
use App\Domains\Tickets\Entities\Commentaire;
use App\Domains\Tickets\Repositories\TicketRepositoryInterface;
use App\Domains\Tickets\ValueObjects\StatutTicket;
use App\Domains\Tickets\ValueObjects\PrioriteTicket;
use App\Domains\Shared\ValueObjects\IdentiteUtilisateur;
use App\Models\Ticket as TicketModel;
use App\Models\Commentaire as CommentaireModel;
use App\Models\User;
use DateTime;
use Illuminate\Support\Facades\DB;

class EloquentTicketRepository implements TicketRepositoryInterface
{
    public function findById(int $id): ?Ticket
    {
        $ticketModel = TicketModel::with(['utilisateur', 'technicien', 'commentaires', 'piecesJointes'])->find($id);
        
        if (!$ticketModel) {
            return null;
        }
        
        return $this->mapTicketModelToEntity($ticketModel);
    }
    
    public function findAll(): array
    {
        $ticketModels = TicketModel::with(['utilisateur', 'technicien'])->get();
        
        return $ticketModels->map(function ($ticketModel) {
            return $this->mapTicketModelToEntity($ticketModel);
        })->toArray();
    }
    
    public function findByStatut(StatutTicket $statut): array
    {
        $ticketModels = TicketModel::where('statut', $statut->toString())
            ->with(['utilisateur', 'technicien'])
            ->get();
            
        return $ticketModels->map(function ($ticketModel) {
            return $this->mapTicketModelToEntity($ticketModel);
        })->toArray();
    }
    
    public function findByUtilisateur(int $utilisateurId): array
    {
        $ticketModels = TicketModel::where('utilisateur_id', $utilisateurId)
            ->with(['utilisateur', 'technicien'])
            ->get();
            
        return $ticketModels->map(function ($ticketModel) {
            return $this->mapTicketModelToEntity($ticketModel);
        })->toArray();
    }
    
    public function findByTechnicien(int $technicienId): array
    {
        $ticketModels = TicketModel::where('technicien_id', $technicienId)
            ->with(['utilisateur', 'technicien'])
            ->get();
            
        return $ticketModels->map(function ($ticketModel) {
            return $this->mapTicketModelToEntity($ticketModel);
        })->toArray();
    }
    
    public function findByCategorie(int $categorieId): array
    {
        $ticketModels = TicketModel::where('categorie_id', $categorieId)
            ->with(['utilisateur', 'technicien'])
            ->get();
            
        return $ticketModels->map(function ($ticketModel) {
            return $this->mapTicketModelToEntity($ticketModel);
        })->toArray();
    }
    
    public function save(Ticket $ticket): int
    {
        $ticketData = $this->prepareTicketData($ticket);
        
        $ticketModel = TicketModel::create($ticketData);
        
        // Enregistrer les commentaires si présents
        foreach ($ticket->getCommentaires() as $commentaire) {
            $this->addCommentaire($commentaire, $ticketModel->id);
        }
        
        return $ticketModel->id;
    }
    
    public function update(Ticket $ticket): void
    {
        $ticketData = $this->prepareTicketData($ticket);
        
        TicketModel::where('id', $ticket->getId())->update($ticketData);
        
        // Si de nouveaux commentaires ont été ajoutés, les enregistrer
        $existingCommentaires = CommentaireModel::where('ticket_id', $ticket->getId())->pluck('id')->toArray();
        
        foreach ($ticket->getCommentaires() as $commentaire) {
            if ($commentaire->getId() === 0 || !in_array($commentaire->getId(), $existingCommentaires)) {
                $this->addCommentaire($commentaire, $ticket->getId());
            }
        }
    }
    
    public function addCommentaire(Commentaire $commentaire, ?int $ticketId = null): int
    {
        $commentaireModel = CommentaireModel::create([
            'ticket_id' => $ticketId ?? $commentaire->getTicketId(),
            'utilisateur_id' => $commentaire->getAuteurId(),
            'contenu' => $commentaire->getContenu(),
            'prive' => $commentaire->estPrive(),
        ]);
        
        return $commentaireModel->id;
    }
    
    public function findCommentairesByTicketId(int $ticketId): array
    {
        $commentaireModels = CommentaireModel::where('ticket_id', $ticketId)
            ->with('utilisateur')
            ->get();
            
        return $commentaireModels->map(function($commentaireModel) {
            return $this->mapCommentaireModelToEntity($commentaireModel);
        })->toArray();
    }
    
    public function search(array $criteria): array
    {
        $query = TicketModel::query();
        
        if (isset($criteria['statut'])) {
            $query->where('statut', $criteria['statut']);
        }
        
        if (isset($criteria['priorite'])) {
            $query->where('priorite', $criteria['priorite']);
        }
        
        if (isset($criteria['categorie_id'])) {
            $query->where('categorie_id', $criteria['categorie_id']);
        }
        
        if (isset($criteria['utilisateur_id'])) {
            $query->where('utilisateur_id', $criteria['utilisateur_id']);
        }
        
        if (isset($criteria['technicien_id'])) {
            $query->where('technicien_id', $criteria['technicien_id']);
        }
        
        if (isset($criteria['texte'])) {
            $query->where(function($q) use ($criteria) {
                $q->where('titre', 'like', '%' . $criteria['texte'] . '%')
                  ->orWhere('description', 'like', '%' . $criteria['texte'] . '%');
            });
        }
        
        if (isset($criteria['date_debut']) && isset($criteria['date_fin'])) {
            $query->whereBetween('created_at', [$criteria['date_debut'], $criteria['date_fin']]);
        }
        
        $ticketModels = $query->with(['utilisateur', 'technicien'])->get();
        
        return $ticketModels->map(function ($ticketModel) {
            return $this->mapTicketModelToEntity($ticketModel);
        })->toArray();
    }
    
    public function getStatistiques(array $filtres = []): array
    {
        $query = TicketModel::query();
        
        // Appliquer des filtres si nécessaire
        if (!empty($filtres)) {
            if (isset($filtres['date_debut']) && isset($filtres['date_fin'])) {
                $query->whereBetween('created_at', [$filtres['date_debut'], $filtres['date_fin']]);
            }
            
            if (isset($filtres['technicien_id'])) {
                $query->where('technicien_id', $filtres['technicien_id']);
            }
        }
        
        $totalTickets = $query->count();
        $ticketsParStatut = $query->select('statut', DB::raw('count(*) as total'))
                                  ->groupBy('statut')
                                  ->pluck('total', 'statut')
                                  ->toArray();
                                  
        $ticketsParPriorite = $query->select('priorite', DB::raw('count(*) as total'))
                                   ->groupBy('priorite')
                                   ->pluck('total', 'priorite')
                                   ->toArray();
                                   
        $tempsResolutionMoyen = $query->whereNotNull('date_resolution')
                                      ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, date_resolution)'));
        
        return [
            'total_tickets' => $totalTickets,
            'par_statut' => $ticketsParStatut,
            'par_priorite' => $ticketsParPriorite,
            'temps_resolution_moyen' => $tempsResolutionMoyen,
        ];
    }
    
    public function delete(int $ticketId): bool
    {
        return TicketModel::destroy($ticketId) > 0;
    }
    
    
    // Convertit un modèle Eloquent de ticket en entité du domaine
    
    private function mapTicketModelToEntity(TicketModel $ticketModel): Ticket
    {
        $utilisateur = new IdentiteUtilisateur(
            $ticketModel->utilisateur->id,
            $ticketModel->utilisateur->nom,
            $ticketModel->utilisateur->prenom,
            $ticketModel->utilisateur->email,
            $ticketModel->utilisateur->type_utilisateur
        );
        
        $technicien = null;
        if ($ticketModel->technicien) {
            $technicien = new IdentiteUtilisateur(
                $ticketModel->technicien->id,
                $ticketModel->technicien->nom,
                $ticketModel->technicien->prenom,
                $ticketModel->technicien->email,
                $ticketModel->technicien->type_utilisateur
            );
        }
        
        $ticket = new Ticket(
            $ticketModel->id,
            $ticketModel->titre,
            $ticketModel->description,
            StatutTicket::fromString($ticketModel->statut),
            PrioriteTicket::fromString($ticketModel->priorite),
            $utilisateur,
            $ticketModel->categorie_id,
            new DateTime($ticketModel->created_at)
        );
        
        if ($technicien) {
            $ticket->assignerTechnicien($technicien);
        }
        
        if ($ticketModel->date_resolution) {
            $ticket->setDateResolution(new DateTime($ticketModel->date_resolution));
        }
        
        if ($ticketModel->solution) {
            $ticket->setSolution($ticketModel->solution);
        }
        
        if ($ticketModel->temps_passe_total > 0) {
            $ticket->setTempsPasse($ticketModel->temps_passe_total);
        }
        
        // Charger les commentaires si disponibles
        if ($ticketModel->relationLoaded('commentaires')) {
            foreach ($ticketModel->commentaires as $commentaireModel) {
                $ticket->ajouterCommentaire($this->mapCommentaireModelToEntity($commentaireModel));
            }
        }
        
        return $ticket;
    }
    
    
    // Convertit un modèle Eloquent de commentaire en entité du domaine
     
    private function mapCommentaireModelToEntity(CommentaireModel $commentaireModel): Commentaire
    {
        $commentaire = new Commentaire(
            $commentaireModel->id,
            $commentaireModel->ticket_id,
            $commentaireModel->utilisateur_id,
            $commentaireModel->contenu,
            (bool) $commentaireModel->prive,
            new DateTime($commentaireModel->created_at)
        );
        
        if ($commentaireModel->updated_at && $commentaireModel->updated_at !== $commentaireModel->created_at) {
            $commentaire->setDateModification(new DateTime($commentaireModel->updated_at));
        }
        
        return $commentaire;
    }
    
    
    // Prépare les données pour l'enregistrement ou la mise à jour d'un ticket
     
    private function prepareTicketData(Ticket $ticket): array
    {
        $data = [
            'titre' => $ticket->getTitre(),
            'description' => $ticket->getDescription(),
            'statut' => $ticket->getStatut()->toString(),
            'priorite' => $ticket->getPriorite()->toString(),
            'utilisateur_id' => $ticket->getUtilisateur()->getId(),
            'categorie_id' => $ticket->getCategorieId(),
            'solution' => $ticket->getSolution(),
            'temps_passe_total' => $ticket->getTempsPasse(),
        ];
        
        if ($ticket->getTechnicien()) {
            $data['technicien_id'] = $ticket->getTechnicien()->getId();
        }
        
        if ($ticket->getDateResolution()) {
            $data['date_resolution'] = $ticket->getDateResolution()->format('Y-m-d H:i:s');
        }
        
        return $data;
    }
}