<?php

namespace App\Domains\Tickets\Services;

use App\Domains\Tickets\Entities\Ticket;
use App\Domains\Tickets\Entities\Commentaire;
use App\Domains\Tickets\ValueObjects\StatutTicket;
use App\Domains\Tickets\Exceptions\StatutInvalideException;
use App\Domains\Tickets\Exceptions\RegleMetierException;
use App\Domains\Shared\ValueObjects\IdentiteUtilisateur;

class TicketService
{
    
    // Résout un ticket en vérifiant que les règles métier sont respectées
 

    public function resoudreTicket(Ticket $ticket, string $solution, IdentiteUtilisateur $utilisateur): void
    {
        // Vérifier que l'utilisateur est autorisé à résoudre le ticket (technicien assigné ou admin)
        if (!$utilisateur->estTechnicien() && !$utilisateur->estAdministrateur() && 
            !($ticket->getTechnicien() && $ticket->getTechnicien()->getId() === $utilisateur->getId())) {
            throw new RegleMetierException('Seul le technicien assigné au ticket ou un administrateur peut le résoudre');
        }
        
        // Vérifier que le ticket n'est pas déjà fermé ou résolu
        if ($ticket->getStatut() === StatutTicket::FERME || $ticket->getStatut() === StatutTicket::RESOLU) {
            throw new StatutInvalideException('Impossible de résoudre un ticket déjà résolu ou fermé');
        }
        
        // Vérifier que la solution n'est pas vide
        if (empty(trim($solution))) {
            throw new RegleMetierException('La solution ne peut pas être vide pour résoudre un ticket');
        }
        
        // Résoudre le ticket
        $ticket->resoudre($solution);
    }
    
    
    //  Change le statut d'un ticket en vérifiant les règles de transition
    
    public function changerStatut(Ticket $ticket, StatutTicket $nouveauStatut, IdentiteUtilisateur $utilisateur): void
    {
        $statutActuel = $ticket->getStatut();
        
        // Vérifier la validité de la transition
        if (!$this->estTransitionValide($statutActuel, $nouveauStatut)) {
            throw new StatutInvalideException(
                "",
                $statutActuel,
                $nouveauStatut
            );
        }
        
        // Vérifier les permissions selon le type d'utilisateur
        $this->verifierPermissionChangementStatut($ticket, $nouveauStatut, $utilisateur);
        
        // Appliquer le changement de statut selon le type de statut
        switch ($nouveauStatut) {
            case StatutTicket::RESOLU:
                throw new RegleMetierException('Utilisez la méthode resoudreTicket() pour résoudre un ticket');
            case StatutTicket::EN_COURS:
                $ticket->marquerEnCours();
                break;
            case StatutTicket::EN_ATTENTE:
                $ticket->marquerEnAttente();
                break;
            case StatutTicket::FERME:
                if ($statutActuel !== StatutTicket::RESOLU) {
                    throw new StatutInvalideException('Un ticket doit etre resolu avant d\'etre ferme');
                }
                $ticket->fermer();
                break;
            case StatutTicket::ROUVERT:
                $ticket->rouvrir();
                break;
            default:
                $ticket->setStatut($nouveauStatut);
        }
    }
    
    
    // Ajoute un commentaire au ticket avec validation

    public function ajouterCommentaire(Ticket $ticket, string $contenu, IdentiteUtilisateur $utilisateur, bool $estPrive = false): void
    {
        // Vérifier que le contenu du commentaire n'est pas vide
        if (empty(trim($contenu))) {
            throw RegleMetierException::commentaireVide();
        }
        
        // Vérifier les permissions pour les commentaires privés
        if ($estPrive && !($utilisateur->estTechnicien() || $utilisateur->estAdministrateur() || $utilisateur->estSuperviseur())) {
            throw new RegleMetierException('Seul le personnel technique peut ajouter des commentaires privés');
        }
        
        // Créer et ajouter le commentaire
        $commentaire = new Commentaire(
            0, // ID temporaire, sera défini lors de la persistance
            $ticket->getId(),
            $utilisateur->getId(),
            $contenu,
            $estPrive,
            new \DateTime()
        );
        
        $ticket->ajouterCommentaire($commentaire);
    }
    
    
    // Assigne un ticket à un technicien

    public function assignerTechnicien(Ticket $ticket, IdentiteUtilisateur $technicien, IdentiteUtilisateur $utilisateurEffectuantAction): void
    {
        // Vérifier que l'utilisateur est autorisé à assigner des tickets
        if (!$utilisateurEffectuantAction->estAdministrateur() && !$utilisateurEffectuantAction->estSuperviseur()) {
        throw RegleMetierException::autorisationInsuffisante('assigner un ticket', 'administrateur ou superviseur');

        }
        
        // Vérifier que la personne assignée est bien un technicien
        if (!$technicien->estTechnicien()) {
            throw new RegleMetierException('Seuls les techniciens peuvent être assignés à des tickets');
        }
        
        $ticket->assignerTechnicien($technicien);
    }
    
    
    //  Vérifie si une transition de statut est valide

    private function estTransitionValide(StatutTicket $statutActuel, StatutTicket $nouveauStatut): bool
    {
        // Définir les transitions de statut autorisées
        $transitionsAutorisees = [
            StatutTicket::NOUVEAU->toString() => [
                StatutTicket::ASSIGNE->toString(),
                StatutTicket::EN_COURS->toString(),
            ],
            StatutTicket::ASSIGNE->toString() => [
                StatutTicket::EN_COURS->toString(),
                StatutTicket::EN_ATTENTE->toString(),
                StatutTicket::NOUVEAU->toString(), // Retirer l'assignation
            ],
            StatutTicket::EN_COURS->toString() => [
                StatutTicket::EN_ATTENTE->toString(),
                StatutTicket::RESOLU->toString(),
            ],
            StatutTicket::EN_ATTENTE->toString() => [
                StatutTicket::EN_COURS->toString(),
                StatutTicket::RESOLU->toString(),
            ],
            StatutTicket::RESOLU->toString() => [
                StatutTicket::FERME->toString(),
                StatutTicket::ROUVERT->toString(),
            ],
            StatutTicket::FERME->toString() => [
                StatutTicket::ROUVERT->toString(),
            ],
            StatutTicket::ROUVERT->toString() => [
                StatutTicket::EN_COURS->toString(),
                StatutTicket::EN_ATTENTE->toString(),
                StatutTicket::ASSIGNE->toString(),
            ],
        ];
        
        // Vérifier si la transition est permise
        return in_array(
            $nouveauStatut->toString(),
            $transitionsAutorisees[$statutActuel->toString()] ?? []
        );
    }
    
    
    // Vérifie les permissions de l'utilisateur pour changer le statut d'un ticket

    private function verifierPermissionChangementStatut(Ticket $ticket, StatutTicket $nouveauStatut, IdentiteUtilisateur $utilisateur): void
    {
        // Les administrateurs peuvent tout faire
        if ($utilisateur->estAdministrateur()) {
            return;
        }
        
        // Vérifier si l'utilisateur est le technicien assigné au ticket
        $estTechnicienAssigne = $ticket->getTechnicien() && 
                                $ticket->getTechnicien()->getId() === $utilisateur->getId();
        
        // Vérifier si l'utilisateur est l'utilisateur qui a créé le ticket
        $estCreateur = $ticket->getUtilisateur()->getId() === $utilisateur->getId();
        
        // Règles spécifiques selon le statut
        switch ($nouveauStatut) {
            case StatutTicket::FERME:
            case StatutTicket::RESOLU:
                if (!$estTechnicienAssigne && !$utilisateur->estSuperviseur()) {
                    throw new RegleMetierException('Seul le technicien assigné ou un superviseur peut résoudre/fermer ce ticket');
                }
                break;
            case StatutTicket::ROUVERT:
                if (!$estCreateur && !$utilisateur->estSuperviseur()) {
                    throw new RegleMetierException('Seul le créateur du ticket ou un superviseur peut rouvrir ce ticket');
                }
                break;
            case StatutTicket::EN_COURS:
            case StatutTicket::EN_ATTENTE:
                if (!$estTechnicienAssigne && !$utilisateur->estSuperviseur()) {
                    throw new RegleMetierException('Seul le technicien assigné ou un superviseur peut changer ce statut');
                }
                break;
        }
    }
}