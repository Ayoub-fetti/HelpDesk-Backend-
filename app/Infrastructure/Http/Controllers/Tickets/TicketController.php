<?php

namespace App\Infrastructure\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Application\Tickets\UseCases\AddNewTicket;
use App\Application\Tickets\UseCases\ReassignerTicket;
use App\Application\Tickets\UseCases\ChangeStatutTicket;
use App\Application\Tickets\UseCases\CloseTicket;
use App\Application\Tickets\DTOs\NewTicketDTO;
use App\Application\Tickets\DTOs\ReassignationTicketDTO;
use App\Application\Tickets\DTOs\ChangeStatutDTO;
use App\Application\Tickets\DTOs\CloseTicketDTO;
use App\Infrastructure\Http\Requests\Tickets\CreateTicketRequest;
use App\Infrastructure\Http\Requests\Tickets\AssignTicketRequest;
use App\Infrastructure\Http\Requests\Tickets\ChangeStatusRequest;
use App\Infrastructure\Http\Requests\Tickets\CloseTicketRequest;
use App\Infrastructure\Http\Resources\TicketResource;
use App\Domains\Tickets\Repositories\TicketRepositoryInterface;
use App\Infrastructure\Http\Requests\Tickets\UpdateTicketRequest;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    private $ticketRepository;

    public function __construct(TicketRepositoryInterface $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    public function index(Request $request): JsonResponse
    {
        $criteria = [];
        
        // Add filters from request
        if ($request->has('status')) {
            $criteria['statut'] = $request->status;
        }
        
        if ($request->has('priority')) {
            $criteria['priority'] = $request->priority;
        }
        
        if ($request->has('category_id')) {
            $criteria['category_id'] = $request->category_id;
        }
        
        if ($request->has('user_id')) {
            $criteria['user_id'] = $request->user_id;
        }
        
        if ($request->has('technician_id')) {
            $criteria['technician_id'] = $request->technician_id;
        }
        
        if ($request->has('text')) {
            $criteria['text'] = $request->text;
        }
        
        $tickets = empty($criteria) 
            ? $this->ticketRepository->findAll() 
            : $this->ticketRepository->search($criteria);
            
        return response()->json(TicketResource::collection($tickets));
    }

    public function show(int $id): JsonResponse
    {
        $ticket = $this->ticketRepository->findById($id);
        
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        
        return response()->json(new TicketResource($ticket));
    }

    public function store(CreateTicketRequest $request, AddNewTicket $useCase): JsonResponse
    {
        $user = Auth::user();
        
        $dto = new NewTicketDTO(
            $request['title'],
            $request['description'],
            $request['priority'],
            $request['category_id'],
            $user->id,
            $user->lastName,
            $user->firstName,
            $user->email,
            $user->user_type,
            $request->phone ?? '',
            $request->technician_id ?? null,
            $request->technician_last_name ?? null,
            $request->technician_first_name ?? null,
            $request->technician_email ?? null,
            $request->technician_phone ?? null
        );
        
        try {
            $ticketId = $useCase->execute($dto);
            $ticket = $this->ticketRepository->findById($ticketId);
            return response()->json(new TicketResource($ticket), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function assign(int $id, AssignTicketRequest $request, ReassignerTicket $useCase): JsonResponse
    {
        $user = Auth::user();
        
        $dto = new ReassignationTicketDTO(
            $id,
            $request['technician_id'],
            $request['technician_last_name'],
            $request['technician_first_name'],
            $request['technician_email'],
            $user->id,
            $user->lastName,
            $user->firstName,
            $user->user_type,
            $user->email,
            $request->technician_phone ?? '',
            $request->comment ?? ''
        );
        
        try {
            $useCase->execute($dto);
            return response()->json(['message' => 'Ticket assigned successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function changeStatus(int $id, ChangeStatusRequest $request, ChangeStatutTicket $useCase): JsonResponse
    {
        $user = Auth::user();
        
        $dto = new ChangeStatutDTO(
            $id,
            $request['newStatut'],
            $user->id,
            $user->lastName,
            $user->firstName,
            $user->user_type,
            $user->email,
            $request->comment ?? ''
        );
        
        try {
            $useCase->execute($dto);
            return response()->json(['message' => 'Ticket status changed successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function close(int $id, CloseTicketRequest $request, CloseTicket $useCase): JsonResponse
    {
        $user = Auth::user();
        
        $dto = new CloseTicketDTO(
            $id,
            $user->id,
            $user->lastName,
            $user->firstName,
            $user->user_type,
            $user->email,
            $request['solution'],
            $request->comment ?? ''
        );
        
        try {
            $useCase->execute($dto);
            return response()->json(['message' => 'Ticket closed successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    } 

    public function update(int $id, UpdateTicketRequest $request): JsonResponse
    {
        $ticket = $this->ticketRepository->findById($id);
        
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        
        // Use validated() to get only validated fields
        $validatedData = $request->validated();
        
        // Update only allowed fields
        if (isset($validatedData['title'])) {
            $ticket->setTitle($validatedData['title']);
        }
        
        if (isset($validatedData['description'])) {
            $ticket->setDescription($validatedData['description']);
        }
        
        if (isset($validatedData['priority'])) {
            $ticket->setPriority(\App\Domains\Tickets\ValueObjects\PriorityTicket::fromString($validatedData['priority']));
        }
        
        if (isset($validatedData['category_id'])) {
            $ticket->setCategoryId($validatedData['category_id']);
        }
        
        // Save the updated ticket
        $this->ticketRepository->update($ticket);
        
        return response()->json(new TicketResource($ticket));
    }
    public function destroy(int $id): JsonResponse
    {
        $ticket = $this->ticketRepository->findById($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $this->ticketRepository->delete($ticket->getId());

        return response()->json(['message' => 'Ticket deleted successfully']);
    }
}