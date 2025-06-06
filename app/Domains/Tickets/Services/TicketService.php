<?php

namespace App\Domains\Tickets\Services;

use App\Domains\Tickets\Entities\Ticket;
use App\Domains\Tickets\Entities\Comment;
use App\Domains\Tickets\ValueObjects\StatutTicket;
use App\Domains\Tickets\Exceptions\StatutInvalideException;
use App\Domains\Tickets\Exceptions\RegleMetierException;
use App\Domains\Shared\ValueObjects\IdentiteUser;

class TicketService
{
    
    // Résout un ticket en vérifiant que les règles métier sont respectées
 

    public function resolveTicket(Ticket $ticket, string $solution, IdentiteUser $user): void
    {
        // Vérifier que l'user est autorisé à résoudre le ticket (technicien assigné ou admin)
        if (!$user->isTechnician() && !$user->isAdministrator() && 
            !($ticket->getTechnician() && $ticket->getTechnician()->getId() === $user->getId())) {
            throw new RegleMetierException('Only the technician assigned to the ticket or an administrator can resolve it');
        }
        
        // Vérifier que le ticket n'est pas déjà fermé ou résolu
        if ($ticket->getStatut() === StatutTicket::CLOSED || $ticket->getStatut() === StatutTicket::RESOLVED) {
            throw new StatutInvalideException('Unable to resolve a ticket that has already been resolved or closed');
        }
        
        // Vérifier que la solution n'est pas vide
        if (empty(trim($solution))) {
            throw new RegleMetierException('Solution cannot be empty to resolve a ticket');
        }
        
        // Résoudre le ticket
        $ticket->solve($solution);
    }
    
    
    //  Change le statut d'un ticket en vérifiant les règles de transition
    
    public function changeStatut(Ticket $ticket, StatutTicket $newStatut, IdentiteUser $user): void
    {
        $statusCurrent = $ticket->getStatut();
        
        // Vérifier la validité de la transition
        if (!$this->isTransitionValid($statusCurrent, $newStatut)) {
            throw new StatutInvalideException(
                "",
                $statusCurrent,
                $newStatut
            );
        }
        
        // Vérifier les permissions selon le type d'user
        $this->checkPermissionChangeStatus($ticket, $newStatut, $user);
        
        // Appliquer le changement de statut selon le type de statut
        switch ($newStatut) {
            case StatutTicket::RESOLVED:
                throw new RegleMetierException('Use the resolveTicket() method to resolve a ticket');
            case StatutTicket::IN_PROGRESS:
                $ticket->markInProgress();
                break;
            case StatutTicket::ON_HOLD:
                $ticket->markOnHold();
                break;
            case StatutTicket::CLOSED:
                if ($statusCurrent !== StatutTicket::RESOLVED) {
                    throw new StatutInvalideException('A ticket must be resolved before it can be closed.');
                }
                $ticket->close();
                break;
            case StatutTicket::REOPEN:
                $ticket->reopen();
                break;
            default:
                $ticket->setStatut($newStatut);
        }
    }
    
    
    // Ajoute un commentaire au ticket avec validation

    public function addComment(Ticket $ticket, string $content, IdentiteUser $user, bool $isPrivate = false): void
    {
        // Vérifier que le contenu du commentaire n'est pas vide
        if (empty(trim($content))) {
            throw RegleMetierException::commentaireVide();
        }
        
        // Vérifier les permissions pour les commentaires privés
        if ($isPrivate && !($user->isTechnician() || $user->isAdministrator() || $user->isSupervisor())) {
            throw new RegleMetierException('Only technical staff can add private comments');
        }
        
        // Créer et ajouter le commentaire
        $comment = new Comment(
            0, // ID temporaire, sera défini lors de la persistance
            $ticket->getId(),
            $user->getId(),
            $content,
            $isPrivate,
            new \DateTime()
        );
        
        $ticket->addComment($comment);
    }
    
    
    // Assigne un ticket à un technicien

    public function assignTechnician(Ticket $ticket, IdentiteUser $technician, IdentiteUser $userEffectuantAction): void
    {
        // Vérifier que l'user est autorisé à assigner des tickets
        if (!$userEffectuantAction->isAdministrator() && !$userEffectuantAction->isSupervisor()) {
        throw RegleMetierException::autorisationInsuffisante('assigner un ticket', 'administrateur ou superviseur');

        }
        
        // Vérifier que la personne assignée est bien un technicien
        if (!$technician->isTechnician()) {
            throw new RegleMetierException('Only technicians can be assigned to tickets');
        }
        
        $ticket->assignTechnician($technician);
    }
    
    
    //  Vérifie si une transition de statut est valide

    private function isTransitionValid(StatutTicket $statusCurrent, StatutTicket $newStatut): bool
    {
        // Définir les transitions de statut autorisées
        $transitionsAutorisees = [
            StatutTicket::NEW->toString() => [
                StatutTicket::ASSIGNED->toString(),
                StatutTicket::IN_PROGRESS->toString(),
            ],
            StatutTicket::ASSIGNED->toString() => [
                StatutTicket::IN_PROGRESS->toString(),
                StatutTicket::ON_HOLD->toString(),
                StatutTicket::NEW->toString(), // Retirer l'assignation
                StatutTicket::RESOLVED->toString(), // Added this line

            ],
            StatutTicket::IN_PROGRESS->toString() => [
                StatutTicket::ON_HOLD->toString(),
                StatutTicket::RESOLVED->toString(),
            ],
            StatutTicket::ON_HOLD->toString() => [
                StatutTicket::IN_PROGRESS->toString(),
                StatutTicket::RESOLVED->toString(),
            ],
            StatutTicket::RESOLVED->toString() => [
                StatutTicket::CLOSED->toString(),
                StatutTicket::REOPEN->toString(),
            ],
            StatutTicket::CLOSED->toString() => [
                StatutTicket::REOPEN->toString(),
            ],
            StatutTicket::REOPEN->toString() => [
                StatutTicket::IN_PROGRESS->toString(),
                StatutTicket::ON_HOLD->toString(),
                StatutTicket::ASSIGNED->toString(),
            ],
        ];
        
        // Vérifier si la transition est permise
        return in_array(
            $newStatut->toString(),
            $transitionsAutorisees[$statusCurrent->toString()] ?? []
        );
    }
    
    
    //Check the permissions of the user to change the status of a ticket

    private function checkPermissionChangeStatus(Ticket $ticket, StatutTicket $newStatut, IdentiteUser $user): void
    {
        // Les administrateurs peuvent tout faire
        if ($user->isAdministrator()) {
            return;
        }
        
        //Check if the user is the technician assigned to the ticket
        $estTechnicienAssigne = $ticket->getTechniciAn() && 
                                $ticket->getTechniciAn()->getId() === $user->getId();
        
        //Check if the user is the user that created the ticket        
        $estCreateur = $ticket->getUser()->getId() === $user->getId();
        
        //Specific rules depending on the status
        switch ($newStatut) {
            case StatutTicket::CLOSED:
            case StatutTicket::RESOLVED:
                if (!$estTechnicienAssigne && !$user->isSupervisor()) {
                    throw new RegleMetierException('Only the assigned technician or supervisor can resolve/CLOSED this ticket');
                }
                break;
            case StatutTicket::REOPEN:
                if (!$estCreateur && !$user->isSupervisor()) {
                    throw new RegleMetierException('Only the ticket creator or a supervisor can reopen this ticket');
                }
                break;
            case StatutTicket::IN_PROGRESS:
            case StatutTicket::ON_HOLD:
                if (!$estTechnicienAssigne && !$user->isSupervisor()) {
                    throw new RegleMetierException('Only the assigned technician or a supervisor can change this status');
                }
                break;
        }
    }
}