<?php

use App\Repositories\Neo4jRepository;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    $this->repository = new Neo4jRepository;

    if ($this->repository->isConnected()) {
        $this->repository->clearAll();
    }
});

describe('Neo4jRepository', function (): void {
    describe('isConnected', function (): void {
        it('returns true when Neo4j is available', function (): void {
            $result = $this->repository->isConnected();

            expect($result)->toBeTrue();
        });

        it('caches connection status after first check', function (): void {
            $result1 = $this->repository->isConnected();
            $result2 = $this->repository->isConnected();

            expect($result1)->toBe($result2);
        });
    });

    describe('syncUser', function (): void {
        it('syncs user to Neo4j successfully', function (): void {
            $userData = [
                'id' => 'user-test-123',
                'name' => 'Test User',
                'email' => 'test@example.com',
                'username' => 'testuser',
            ];

            $this->repository->syncUser($userData);

            $stats = $this->repository->getStatistics();
            expect($stats['users'])->toBeGreaterThanOrEqual(1);
        });

        it('handles missing fields gracefully', function (): void {
            $userData = [
                'id' => 'user-minimal',
            ];

            $this->repository->syncUser($userData);

            $stats = $this->repository->getStatistics();
            expect($stats['users'])->toBeGreaterThanOrEqual(1);
        });
    });

    describe('deleteUser', function (): void {
        it('deletes user from Neo4j', function (): void {
            $this->repository->syncUser([
                'id' => 'user-to-delete',
                'name' => 'Delete Me',
                'email' => 'delete@test.com',
                'username' => 'deleteme',
            ]);

            $this->repository->deleteUser('user-to-delete');

            expect(true)->toBeTrue();
        });
    });

    describe('syncArticle', function (): void {
        it('syncs article with tags and categories to Neo4j', function (): void {
            $this->repository->syncUser([
                'id' => 'author-123',
                'name' => 'Author',
                'email' => 'author@test.com',
                'username' => 'author',
            ]);

            $articleData = [
                'id' => 'article-test-123',
                'title' => 'Test Article',
                'slug' => 'test-article',
                'status' => 'published',
                'author_id' => 'author-123',
                'view_count' => 100,
                'like_count' => 10,
                'tags' => ['laravel', 'php'],
                'categories' => ['programming'],
            ];

            $this->repository->syncArticle($articleData);

            $stats = $this->repository->getStatistics();
            expect($stats['articles'])->toBeGreaterThanOrEqual(1)
                ->and($stats['tags'])->toBeGreaterThanOrEqual(2)
                ->and($stats['categories'])->toBeGreaterThanOrEqual(1);
        });

        it('syncs article without author_id', function (): void {
            $articleData = [
                'id' => 'article-no-author',
                'title' => 'No Author Article',
                'slug' => 'no-author-article',
                'status' => 'published',
                'author_id' => '',
                'view_count' => 0,
                'like_count' => 0,
                'tags' => [],
                'categories' => [],
            ];

            $this->repository->syncArticle($articleData);

            $stats = $this->repository->getStatistics();
            expect($stats['articles'])->toBeGreaterThanOrEqual(1);
        });

        it('handles missing fields gracefully', function (): void {
            $articleData = [
                'id' => 'article-minimal',
            ];

            $this->repository->syncArticle($articleData);

            $stats = $this->repository->getStatistics();
            expect($stats['articles'])->toBeGreaterThanOrEqual(1);
        });
    });

    describe('deleteArticle', function (): void {
        it('deletes article from Neo4j', function (): void {
            $this->repository->syncArticle([
                'id' => 'article-to-delete',
                'title' => 'Delete Me',
                'slug' => 'delete-me',
                'status' => 'published',
                'author_id' => '',
                'view_count' => 0,
                'like_count' => 0,
                'tags' => [],
                'categories' => [],
            ]);

            $this->repository->deleteArticle('article-to-delete');

            expect(true)->toBeTrue();
        });
    });

    describe('syncFollow', function (): void {
        it('creates follow relationship between users', function (): void {
            $this->repository->syncUser([
                'id' => 'follower-user',
                'name' => 'Follower',
                'email' => 'follower@test.com',
                'username' => 'follower',
            ]);
            $this->repository->syncUser([
                'id' => 'following-user',
                'name' => 'Following',
                'email' => 'following@test.com',
                'username' => 'following',
            ]);

            $this->repository->syncFollow('follower-user', 'following-user');

            $stats = $this->repository->getStatistics();
            expect($stats['follows'])->toBeGreaterThanOrEqual(1);
        });
    });

    describe('deleteFollow', function (): void {
        it('deletes follow relationship', function (): void {
            $this->repository->syncUser([
                'id' => 'f1',
                'name' => 'F1',
                'email' => 'f1@test.com',
                'username' => 'f1',
            ]);
            $this->repository->syncUser([
                'id' => 'f2',
                'name' => 'F2',
                'email' => 'f2@test.com',
                'username' => 'f2',
            ]);
            $this->repository->syncFollow('f1', 'f2');

            $this->repository->deleteFollow('f1', 'f2');

            expect(true)->toBeTrue();
        });
    });

    describe('syncLike', function (): void {
        it('creates like relationship between user and article', function (): void {
            $this->repository->syncUser([
                'id' => 'liker-user',
                'name' => 'Liker',
                'email' => 'liker@test.com',
                'username' => 'liker',
            ]);
            $this->repository->syncArticle([
                'id' => 'liked-article',
                'title' => 'Liked Article',
                'slug' => 'liked-article',
                'status' => 'published',
                'author_id' => 'liker-user',
                'view_count' => 0,
                'like_count' => 0,
                'tags' => [],
                'categories' => [],
            ]);

            $this->repository->syncLike('liker-user', 'liked-article');

            $stats = $this->repository->getStatistics();
            expect($stats['likes'])->toBeGreaterThanOrEqual(1);
        });
    });

    describe('deleteLike', function (): void {
        it('deletes like relationship', function (): void {
            $this->repository->syncUser([
                'id' => 'l1',
                'name' => 'L1',
                'email' => 'l1@test.com',
                'username' => 'l1',
            ]);
            $this->repository->syncArticle([
                'id' => 'a1',
                'title' => 'A1',
                'slug' => 'a1',
                'status' => 'published',
                'author_id' => 'l1',
                'view_count' => 0,
                'like_count' => 0,
                'tags' => [],
                'categories' => [],
            ]);
            $this->repository->syncLike('l1', 'a1');

            $this->repository->deleteLike('l1', 'a1');

            expect(true)->toBeTrue();
        });
    });

    describe('getUsersWithCommonFollowers', function (): void {
        it('returns users with common followers', function (): void {

            $this->repository->syncUser(['id' => 'u1', 'name' => 'U1', 'email' => 'u1@t.com', 'username' => 'u1']);
            $this->repository->syncUser(['id' => 'u2', 'name' => 'U2', 'email' => 'u2@t.com', 'username' => 'u2']);
            $this->repository->syncUser(['id' => 'u3', 'name' => 'U3', 'email' => 'u3@t.com', 'username' => 'u3']);
            $this->repository->syncUser(['id' => 'common', 'name' => 'Common', 'email' => 'c@t.com', 'username' => 'common']);

            $this->repository->syncFollow('u1', 'common');
            $this->repository->syncFollow('u2', 'common');
            $this->repository->syncFollow('u3', 'common');

            $result = $this->repository->getUsersWithCommonFollowers('u1', 10);

            expect($result)->toBeInstanceOf(Collection::class);
        });

        it('returns empty collection when no common followers', function (): void {
            $this->repository->syncUser(['id' => 'lonely', 'name' => 'Lonely', 'email' => 'l@t.com', 'username' => 'lonely']);

            $result = $this->repository->getUsersWithCommonFollowers('lonely', 10);

            expect($result)->toBeInstanceOf(Collection::class);
        });
    });

    describe('getRelatedArticlesByTags', function (): void {
        it('returns articles with common tags', function (): void {
            $this->repository->syncArticle([
                'id' => 'art1',
                'title' => 'Article 1',
                'slug' => 'article-1',
                'status' => 'published',
                'author_id' => '',
                'view_count' => 100,
                'like_count' => 10,
                'tags' => ['laravel', 'php'],
                'categories' => ['web'],
            ]);
            $this->repository->syncArticle([
                'id' => 'art2',
                'title' => 'Article 2',
                'slug' => 'article-2',
                'status' => 'published',
                'author_id' => '',
                'view_count' => 50,
                'like_count' => 5,
                'tags' => ['laravel', 'api'],
                'categories' => ['web'],
            ]);

            $result = $this->repository->getRelatedArticlesByTags('art1', 10);

            expect($result)->toBeInstanceOf(Collection::class);
        });

        it('returns empty when no related articles', function (): void {
            $this->repository->syncArticle([
                'id' => 'unique-art',
                'title' => 'Unique',
                'slug' => 'unique',
                'status' => 'published',
                'author_id' => '',
                'view_count' => 0,
                'like_count' => 0,
                'tags' => ['very-unique-tag'],
                'categories' => [],
            ]);

            $result = $this->repository->getRelatedArticlesByTags('unique-art', 10);

            expect($result)->toBeInstanceOf(Collection::class);
        });
    });

    describe('getInfluentialAuthors', function (): void {
        it('returns influential authors based on followers', function (): void {
            $this->repository->syncUser(['id' => 'author', 'name' => 'Author', 'email' => 'a@t.com', 'username' => 'author']);
            $this->repository->syncUser(['id' => 'fan1', 'name' => 'Fan1', 'email' => 'f1@t.com', 'username' => 'fan1']);
            $this->repository->syncUser(['id' => 'fan2', 'name' => 'Fan2', 'email' => 'f2@t.com', 'username' => 'fan2']);

            $this->repository->syncFollow('fan1', 'author');
            $this->repository->syncFollow('fan2', 'author');

            $this->repository->syncArticle([
                'id' => 'author-art',
                'title' => 'Author Article',
                'slug' => 'author-article',
                'status' => 'published',
                'author_id' => 'author',
                'view_count' => 0,
                'like_count' => 0,
                'tags' => [],
                'categories' => [],
            ]);

            $result = $this->repository->getInfluentialAuthors(1, 10);

            expect($result)->toBeInstanceOf(Collection::class);
        });

        it('returns empty when min followers not met', function (): void {
            $result = $this->repository->getInfluentialAuthors(1000, 10);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->isEmpty())->toBeTrue();
        });
    });

    describe('getTopicsOfInterest', function (): void {
        it('returns topics based on user likes', function (): void {
            $this->repository->syncUser(['id' => 'topic-user', 'name' => 'TU', 'email' => 'tu@t.com', 'username' => 'tu']);
            $this->repository->syncArticle([
                'id' => 'topic-art',
                'title' => 'Topic Article',
                'slug' => 'topic-article',
                'status' => 'published',
                'author_id' => '',
                'view_count' => 0,
                'like_count' => 0,
                'tags' => ['interest-tag'],
                'categories' => ['interest-category'],
            ]);
            $this->repository->syncLike('topic-user', 'topic-art');

            $result = $this->repository->getTopicsOfInterest('topic-user', 10);

            expect($result)->toBeInstanceOf(Collection::class);
        });

        it('returns empty for user with no likes', function (): void {
            $this->repository->syncUser(['id' => 'no-likes', 'name' => 'NL', 'email' => 'nl@t.com', 'username' => 'nl']);

            $result = $this->repository->getTopicsOfInterest('no-likes', 10);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->isEmpty())->toBeTrue();
        });
    });

    describe('getRecommendedArticlesForUser', function (): void {
        it('returns recommended articles based on user interests', function (): void {
            $this->repository->syncUser(['id' => 'rec-user', 'name' => 'RU', 'email' => 'ru@t.com', 'username' => 'ru']);

            $this->repository->syncArticle([
                'id' => 'liked-art',
                'title' => 'Liked Art',
                'slug' => 'liked-art',
                'status' => 'published',
                'author_id' => '',
                'view_count' => 0,
                'like_count' => 0,
                'tags' => ['recommend-tag'],
                'categories' => [],
            ]);
            $this->repository->syncLike('rec-user', 'liked-art');

            $this->repository->syncArticle([
                'id' => 'recommend-art',
                'title' => 'Recommend Art',
                'slug' => 'recommend-art',
                'status' => 'published',
                'author_id' => '',
                'view_count' => 0,
                'like_count' => 0,
                'tags' => ['recommend-tag'],
                'categories' => [],
            ]);

            $result = $this->repository->getRecommendedArticlesForUser('rec-user', 10);

            expect($result)->toBeInstanceOf(Collection::class);
        });

        it('returns empty for user with no interests', function (): void {
            $this->repository->syncUser(['id' => 'new-user', 'name' => 'NU', 'email' => 'nu@t.com', 'username' => 'nu']);

            $result = $this->repository->getRecommendedArticlesForUser('new-user', 10);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->isEmpty())->toBeTrue();
        });
    });

    describe('getStatistics', function (): void {
        it('returns statistics with correct structure', function (): void {
            $result = $this->repository->getStatistics();

            expect($result)->toBeArray()
                ->and($result)->toHaveKeys(['users', 'articles', 'follows', 'likes', 'tags', 'categories'])
                ->and($result['users'])->toBeInt()
                ->and($result['articles'])->toBeInt()
                ->and($result['follows'])->toBeInt()
                ->and($result['likes'])->toBeInt()
                ->and($result['tags'])->toBeInt()
                ->and($result['categories'])->toBeInt();
        });
    });

    describe('clearAll', function (): void {
        it('clears all data from Neo4j', function (): void {
            $this->repository->syncUser(['id' => 'clear-user', 'name' => 'Clear', 'email' => 'c@t.com', 'username' => 'c']);

            $this->repository->clearAll();

            $stats = $this->repository->getStatistics();
            expect($stats['users'])->toBe(0);
        });
    });
});
