<?php

namespace App\Infrastructure\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Infrastructure\Http\Requests\Tickets\AddCommentRequest;
use App\Application\Tickets\UseCases\AddCommentTicket;
use App\Application\Tickets\DTOs\NewCommentDTO;
use App\Infrastructure\Http\Resources\CommentResource;
use App\Domains\Tickets\Repositories\TicketRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    private $ticketRepository;

    public function __construct(TicketRepositoryInterface $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    public function index(int $ticketId): JsonResponse
    {
        $comments = $this->ticketRepository->findCommentsByTicketId($ticketId);
        
        if (empty($comments)) {
            return response()->json([]);
        }
        
        return response()->json(CommentResource::collection($comments));
    }

    public function store(int $ticketId, AddCommentRequest $request, AddCommentTicket $useCase): JsonResponse
    {
        $user = Auth::user();
        
        $dto = new NewCommentDTO(
            $ticketId,
            $request['content'],
            $user->id,
            $user->lastName,
            $user->firstName,
            $user->user_type,
            $user->email,
            $request->is_private ?? false
        );
        
        try {
            $commentId = $useCase->execute($dto);
            return response()->json(['id' => $commentId, 'message' => 'Comment added successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}