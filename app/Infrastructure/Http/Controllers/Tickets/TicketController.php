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
use App\Infrastructure\Http\Requests\Tickets\ResolveTicketRequest;
use App\Application\Tickets\UseCases\ResolveTicket;
use App\Application\Tickets\DTOs\ResolveTicketDTO;

use App\Infrastructure\Http\Requests\Tickets\CreateTicketRequest;
use App\Infrastructure\Http\Requests\Tickets\AssignTicketRequest;
use App\Infrastructure\Http\Requests\Tickets\ChangeStatusRequest;
use App\Infrastructure\Http\Requests\Tickets\CloseTicketRequest;
use App\Infrastructure\Http\Resources\TicketResource;
use App\Domains\Tickets\Repositories\TicketRepositoryInterface;
use App\Domains\Tickets\ValueObjects\PriorityTicket;
use App\Infrastructure\Http\Requests\Tickets\UpdateTicketRequest;
use Illuminate\Support\Facades\Auth;
use App\Infrastructure\Services\FileUploadService;
use App\Domains\Tickets\Entities\Attachment as AttachmentEntity;

class TicketController extends Controller
{
    private $ticketRepository;
    private $fileUploadService;

    public function __construct(
        TicketRepositoryInterface $ticketRepository,
        FileUploadService $fileUploadService
    ) {
        $this->ticketRepository = $ticketRepository;
        $this->fileUploadService = $fileUploadService;
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

    public function update(int $id, UpdateTicketRequest $request): JsonResponse
    {
        $ticket = $this->ticketRepository->findById($id);
        
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        
        
        $validatedData = array_filter($request->validated(), function($value) {
            return $value !== null && $value !== '';
        });
        
        // Update only allowed fields
        if (isset($validatedData['title'])) {
            $ticket->setTitle($validatedData['title']);
        }
        
        if (isset($validatedData['description'])) {
            $ticket->setDescription($validatedData['description']);
        }
        
        if (isset($validatedData['priority'])) {
            $ticket->setPriority(PriorityTicket::fromString($validatedData['priority']));
        }
        
        if (isset($validatedData['category_id'])) {
            $ticket->setCategoryId($validatedData['category_id']);
        }
        
        // Save the updated ticket
        $this->ticketRepository->update($ticket, $validatedData);
        
        // Debug information - remove in production
        $debug = [
            'has_file' => $request->hasFile('attachments'),
            'files_count' => $request->hasFile('attachments') ? count($request->file('attachments')) : 0,
            'all_input' => $request->all(),
        ];
        
        // Handle file attachments if any
        if ($request->hasFile('attachments')) {
            $files = $request->file('attachments');
            if (!is_array($files)) {
                $files = [$files]; // Convert to array if single file
            }
            
            foreach ($files as $file) {
                if ($file->isValid()) {
                    $fileData = $this->fileUploadService->uploadTicketAttachment($file, $id);
                    
                    $attachment = new AttachmentEntity(
                        0, // ID will be set when saved
                        $id,
                        $fileData['file_name'],
                        $fileData['file_path'],
                        $fileData['type_mime'],
                        $fileData['file_size']
                    );
                    
                    $this->ticketRepository->addAttachment($attachment);
                    $debug['uploaded_file'] = $fileData['file_name'];
                } else {
                    $debug['file_error'] = $file->getError();
                }
            }
        }
        
        // Refresh the ticket to include any new attachments
        $ticket = $this->ticketRepository->findById($id);
        
        // Return the updated ticket with debug info for troubleshooting
        return response()->json([
            'ticket' => new TicketResource($ticket),
            'debug' => $debug
        ]);
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
            // Create the ticket first
            $ticketId = $useCase->execute($dto);
            
            // Handle file attachments if any
            if ($request->hasFile('attachments')) {
                $ticket = $this->ticketRepository->findById($ticketId);
                
                foreach ($request->file('attachments') as $file) {
                    $fileData = $this->fileUploadService->uploadTicketAttachment($file, $ticketId);
                    
                    $attachment = new AttachmentEntity(
                        0, // ID will be set when saved
                        $ticketId,
                        $fileData['file_name'],
                        $fileData['file_path'],
                        $fileData['type_mime'],
                        $fileData['file_size']
                    );
                    
                    $this->ticketRepository->addAttachment($attachment);
                }
                
                // Get the updated ticket with attachments
                $ticket = $this->ticketRepository->findById($ticketId);
            } else {
                $ticket = $this->ticketRepository->findById($ticketId);
            }
            
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
    public function unassign(int $id): JsonResponse
    {
        $ticket = $this->ticketRepository->findById($id);
        
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        try {
            $ticket->withdrawTechnician();
            $this->ticketRepository->update($ticket);
            
            return response()->json([
                'message' => 'Technician unassigned successfully',
                'ticket' => new TicketResource($ticket)
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function changeStatus(int $id, ChangeStatusRequest $request, ChangeStatutTicket $useCase): JsonResponse
    {
        $user = Auth::user();
        
        $dto = new ChangeStatutDTO(
            $id,
            $request['statut'],
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
    public function resolveTicket(int $id, ResolveTicketRequest $request, ResolveTicket $useCase): JsonResponse
    {
        $user = Auth::user();
        
        $dto = new ResolveTicketDTO(
            $id,
            $request['solution'],
            $user->id,
            $user->lastName,
            $user->firstName,
            $user->user_type,
            $user->email,
            $request->comment ?? ''
        );
        
        try {
            $useCase->execute($dto);
            return response()->json(['message' => 'Ticket resolved successfully']);
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

    public function destroy(int $id): JsonResponse
    {
        $ticket = $this->ticketRepository->findById($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $this->ticketRepository->delete($ticket->getId());

        return response()->json(['message' => 'Ticket deleted successfully']);
    }
    public function addAttachments(int $id, Request $request): JsonResponse
    {
        $ticket = $this->ticketRepository->findById($id);
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        if ($request->hasFile('attachments')) {
            $files = $request->file('attachments');
            if (!is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $file) {
                if ($file->isValid()) {
                    $fileData = $this->fileUploadService->uploadTicketAttachment($file, $id);
                    $attachment = new AttachmentEntity(
                        0, $id,
                        $fileData['file_name'],
                        $fileData['file_path'],
                        $fileData['type_mime'],
                        $fileData['file_size']
                    );
                    $this->ticketRepository->addAttachment($attachment);
                }
            }
        }

        $ticket = $this->ticketRepository->findById($id);
        return response()->json([
            'ticket' => new TicketResource($ticket)
        ]);
    }
}