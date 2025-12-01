<?php

namespace App\Http\Controllers;

use App\Contracts\ArticleRepositoryInterface;
use App\Contracts\FollowerServiceInterface;
use App\Contracts\UserRepositoryInterface;
use App\DTOs\UpdateUserDTO;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * User Controller.
 *
 * Handles HTTP requests for user operations.
 */
final class UserController extends Controller
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository,
        private readonly FollowerServiceInterface $followerService,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Get user public profile.
     */
    public function show(User $user): JsonResponse
    {
        $isAuthenticated = Auth::check();

        $articlesQuery = $this->articleRepository->query()
            ->where('author_id', $user->id)
            ->where('status', 'published');

        if (! $isAuthenticated) {
            $articlesQuery->limit(10);
        }

        $articles = $articlesQuery->get();

        /** @var string $userId */
        $userId = $user->id;
        $counts = $this->followerService->getCounts($userId);

        $isFollowing = false;
        $currentUserId = Auth::id();

        if ($isAuthenticated && $currentUserId !== null && is_string($currentUserId) && $currentUserId !== $user->id) {
            $isFollowing = $this->followerService->isFollowing($currentUserId, $userId);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'avatar_url' => $user->avatar_url,
                    'bio' => $user->bio,
                    'created_at' => $user->created_at,
                ],
                'articles' => $articles,
                'articles_count' => $user->articles()->where('status', 'published')->count(),
                'followers_count' => $counts['followers_count'],
                'following_count' => $counts['following_count'],
                'is_following' => $isFollowing,
                'limited' => ! $isAuthenticated,
            ],
        ]);
    }

    /**
     * Get current authenticated user profile.
     */
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var string $userId */
        $userId = $user->id;
        $counts = $this->followerService->getCounts($userId);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'followers_count' => $counts['followers_count'],
                'following_count' => $counts['following_count'],
                'articles_count' => $user->articles()->count(),
            ],
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Update current authenticated user profile.
     */
    public function update(UpdateUserRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $dto = UpdateUserDTO::fromArray($request->validated());
        $data = $dto->toArray();

        if ($data === []) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum dado para atualizar.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $updatedUser = $this->userRepository->update($user, $data);

        return response()->json([
            'success' => true,
            'message' => 'Perfil atualizado com sucesso.',
            'data' => $updatedUser,
        ], JsonResponse::HTTP_OK);
    }
}
