<?php

namespace App\Repositories;

use App\Contracts\Neo4jRepositoryInterface;
use Illuminate\Support\Collection;
use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Databags\Statement;
use Laudis\Neo4j\Types\CypherMap;
use Throwable;

/**
 * Neo4j Repository.
 *
 * Implements Neo4j graph database operations for the recommendation system.
 */
class Neo4jRepository implements Neo4jRepositoryInterface
{
    private ?ClientInterface $client = null;

    private bool $connectionTested = false;

    private bool $isConnectedStatus = false;

    /**
     * Extract a string value from a Neo4j CypherMap.
     *
     * @param  CypherMap<mixed>  $record
     */
    private function getString(CypherMap $record, string $key): string
    {
        /** @var mixed $value */
        $value = $record->get($key);

        return is_string($value) ? $value : (string) $value;
    }

    /**
     * Extract an integer value from a Neo4j CypherMap.
     *
     * @param  CypherMap<mixed>  $record
     */
    private function getInt(CypherMap $record, string $key): int
    {
        /** @var mixed $value */
        $value = $record->get($key);

        return is_int($value) ? $value : (int) $value;
    }

    /**
     * Get the Neo4j client instance.
     */
    protected function getClient(): ClientInterface
    {
        if (! $this->client instanceof \Laudis\Neo4j\Contracts\ClientInterface) {
            /** @var string $host */
            $host = config('neo4j.host', 'neo4j');
            /** @var int $port */
            $port = config('neo4j.port', 7687);
            /** @var string $username */
            $username = config('neo4j.username', 'neo4j');
            /** @var string $password */
            $password = config('neo4j.password', 'password');

            $url = sprintf('bolt://%s:%s@%s:%d', $username, $password, $host, $port);

            $this->client = ClientBuilder::create()
                ->withDriver('default', $url)
                ->withDefaultDriver('default')
                ->build();
        }

        return $this->client;
    }

    /**
     * Check if Neo4j is connected.
     */
    public function isConnected(): bool
    {
        if ($this->connectionTested) {
            return $this->isConnectedStatus;
        }

        $this->getClient()->run('RETURN 1 as test');
        $this->isConnectedStatus = true;

        $this->connectionTested = true;

        return $this->isConnectedStatus;
    }

    /**
     * Sync a user node to Neo4j.
     *
     * @param  array<string, mixed>  $userData
     */
    public function syncUser(array $userData): void
    {
        if (! $this->isConnected()) {
            return;
        }

        /** @var string $id */
        $id = $userData['id'] ?? '';
        /** @var string $name */
        $name = $userData['name'] ?? '';
        /** @var string $email */
        $email = $userData['email'] ?? '';
        /** @var string $username */
        $username = $userData['username'] ?? '';

        $this->getClient()->run(
            'MERGE (u:User {id: $id})
                 SET u.name = $name, 
                     u.email = $email,
                     u.username = $username,
                     u.updated_at = datetime()',
            [
                'id' => (string) $id,
                'name' => (string) $name,
                'email' => (string) $email,
                'username' => (string) $username,
            ]
        );
    }

    /**
     * Delete a user node from Neo4j.
     */
    public function deleteUser(string $userId): void
    {
        if (! $this->isConnected()) {
            return;
        }

        $this->getClient()->run(
            'MATCH (u:User {id: $id}) DETACH DELETE u',
            ['id' => $userId]
        );
    }

    /**
     * Sync an article node to Neo4j.
     *
     * @param  array<string, mixed>  $articleData
     */
    public function syncArticle(array $articleData): void
    {
        if (! $this->isConnected()) {
            return;
        }

        /** @var string $id */
        $id = $articleData['id'] ?? '';
        /** @var string $title */
        $title = $articleData['title'] ?? '';
        /** @var string $slug */
        $slug = $articleData['slug'] ?? '';
        /** @var string $status */
        $status = $articleData['status'] ?? 'draft';
        /** @var string $authorId */
        $authorId = $articleData['author_id'] ?? '';
        /** @var int $viewCount */
        $viewCount = $articleData['view_count'] ?? 0;
        /** @var int $likeCount */
        $likeCount = $articleData['like_count'] ?? 0;

        $this->getClient()->run(
            'MERGE (a:Article {id: $id})
                 SET a.title = $title,
                     a.slug = $slug,
                     a.status = $status,
                     a.author_id = $author_id,
                     a.view_count = $view_count,
                     a.like_count = $like_count,
                     a.updated_at = datetime()',
            [
                'id' => (string) $id,
                'title' => (string) $title,
                'slug' => (string) $slug,
                'status' => (string) $status,
                'author_id' => (string) $authorId,
                'view_count' => (int) $viewCount,
                'like_count' => (int) $likeCount,
            ]
        );

        if ($authorId !== '') {
            $this->getClient()->run(
                'MATCH (a:Article {id: $article_id})
                     MATCH (u:User {id: $author_id})
                     MERGE (u)-[:AUTHORED]->(a)',
                [
                    'article_id' => (string) $id,
                    'author_id' => (string) $authorId,
                ]
            );
        }

        /** @var array<string> $tags */
        $tags = $articleData['tags'] ?? [];
        if (count($tags) > 0) {
            $this->syncArticleTags((string) $id, $tags);
        }

        /** @var array<string> $categories */
        $categories = $articleData['categories'] ?? [];
        if (count($categories) > 0) {
            $this->syncArticleCategories((string) $id, $categories);
        }
    }

    /**
     * Sync article tags.
     *
     * @param  array<string>  $tags
     */
    private function syncArticleTags(string $articleId, array $tags): void
    {
        $this->getClient()->run(
            'MATCH (a:Article {id: $id})-[r:HAS_TAG]->() DELETE r',
            ['id' => $articleId]
        );

        foreach ($tags as $tag) {
            $this->getClient()->run(
                'MATCH (a:Article {id: $article_id})
                 MERGE (t:Tag {name: $tag_name})
                 MERGE (a)-[:HAS_TAG]->(t)',
                [
                    'article_id' => $articleId,
                    'tag_name' => (string) $tag,
                ]
            );
        }
    }

    /**
     * Sync article categories.
     *
     * @param  array<string>  $categories
     */
    private function syncArticleCategories(string $articleId, array $categories): void
    {
        $this->getClient()->run(
            'MATCH (a:Article {id: $id})-[r:IN_CATEGORY]->() DELETE r',
            ['id' => $articleId]
        );

        foreach ($categories as $category) {
            $this->getClient()->run(
                'MATCH (a:Article {id: $article_id})
                 MERGE (c:Category {name: $category_name})
                 MERGE (a)-[:IN_CATEGORY]->(c)',
                [
                    'article_id' => $articleId,
                    'category_name' => (string) $category,
                ]
            );
        }
    }

    /**
     * Delete an article node from Neo4j.
     */
    public function deleteArticle(string $articleId): void
    {
        if (! $this->isConnected()) {
            return;
        }

        $this->getClient()->run(
            'MATCH (a:Article {id: $id}) DETACH DELETE a',
            ['id' => $articleId]
        );
    }

    /**
     * Sync a follow relationship.
     */
    public function syncFollow(string $followerId, string $followingId): void
    {
        if (! $this->isConnected()) {
            return;
        }

        $this->getClient()->run(
            'MATCH (follower:User {id: $follower_id})
                 MATCH (following:User {id: $following_id})
                 MERGE (follower)-[:FOLLOWS]->(following)',
            [
                'follower_id' => $followerId,
                'following_id' => $followingId,
            ]
        );
    }

    /**
     * Delete a follow relationship.
     */
    public function deleteFollow(string $followerId, string $followingId): void
    {
        if (! $this->isConnected()) {
            return;
        }

        $this->getClient()->run(
            'MATCH (follower:User {id: $follower_id})-[r:FOLLOWS]->(following:User {id: $following_id})
                 DELETE r',
            [
                'follower_id' => $followerId,
                'following_id' => $followingId,
            ]
        );
    }

    /**
     * Sync a like relationship.
     */
    public function syncLike(string $userId, string $articleId): void
    {
        if (! $this->isConnected()) {
            return;
        }

        $this->getClient()->run(
            'MATCH (u:User {id: $user_id})
                 MATCH (a:Article {id: $article_id})
                 MERGE (u)-[:LIKES]->(a)',
            [
                'user_id' => $userId,
                'article_id' => $articleId,
            ]
        );
    }

    /**
     * Delete a like relationship.
     */
    public function deleteLike(string $userId, string $articleId): void
    {
        if (! $this->isConnected()) {
            return;
        }

        $this->getClient()->run(
            'MATCH (u:User {id: $user_id})-[r:LIKES]->(a:Article {id: $article_id})
                 DELETE r',
            [
                'user_id' => $userId,
                'article_id' => $articleId,
            ]
        );
    }

    /**
     * Get users with common followers.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getUsersWithCommonFollowers(string $userId, int $limit): Collection
    {
        /** @var Collection<int, array<string, mixed>> $emptyCollection */
        $emptyCollection = collect();

        if (! $this->isConnected()) {
            return $emptyCollection;
        }

        try {
            $result = $this->getClient()->run(
                'MATCH (u:User {id: $user_id})-[:FOLLOWS]->(common:User)<-[:FOLLOWS]-(similar:User)
                 WHERE similar.id <> $user_id
                 AND NOT (u)-[:FOLLOWS]->(similar)
                 WITH similar, COUNT(DISTINCT common) as commonFollows
                 RETURN similar.id as id, similar.name as name, similar.username as username, 
                        commonFollows
                 ORDER BY commonFollows DESC
                 LIMIT $limit',
                [
                    'user_id' => $userId,
                    'limit' => $limit,
                ]
            );

            /** @var array<int, array<string, mixed>> $users */
            $users = [];
            foreach ($result as $record) {
                $users[] = [
                    'id' => $this->getString($record, 'id'),
                    'name' => $this->getString($record, 'name'),
                    'username' => $this->getString($record, 'username'),
                    'common_followers' => $this->getInt($record, 'commonFollows'),
                ];
            }

            /** @var Collection<int, array<string, mixed>> $collection */
            $collection = collect($users);

            return $collection;
        } catch (Throwable) {
            return $emptyCollection;
        }
    }

    /**
     * Get articles related by tags.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getRelatedArticlesByTags(string $articleId, int $limit): Collection
    {
        /** @var Collection<int, array<string, mixed>> $emptyCollection */
        $emptyCollection = collect();

        if (! $this->isConnected()) {
            return $emptyCollection;
        }

        try {
            $result = $this->getClient()->run(
                'MATCH (a:Article {id: $article_id})-[:HAS_TAG|IN_CATEGORY]->(shared)<-[:HAS_TAG|IN_CATEGORY]-(related:Article)
                 WHERE related.id <> $article_id
                 AND related.status = "published"
                 WITH related, COUNT(DISTINCT shared) as commonTags
                 RETURN related.id as id, related.title as title, related.slug as slug,
                        related.author_id as author_id, commonTags
                 ORDER BY commonTags DESC, related.view_count DESC
                 LIMIT $limit',
                [
                    'article_id' => $articleId,
                    'limit' => $limit,
                ]
            );

            /** @var array<int, array<string, mixed>> $articles */
            $articles = [];
            foreach ($result as $record) {
                $articles[] = [
                    'id' => $this->getString($record, 'id'),
                    'title' => $this->getString($record, 'title'),
                    'slug' => $this->getString($record, 'slug'),
                    'author_id' => $this->getString($record, 'author_id'),
                    'common_tags' => $this->getInt($record, 'commonTags'),
                ];
            }

            /** @var Collection<int, array<string, mixed>> $collection */
            $collection = collect($articles);

            return $collection;
        } catch (Throwable) {
            return $emptyCollection;
        }
    }

    /**
     * Get influential authors.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getInfluentialAuthors(int $minFollowers, int $limit): Collection
    {
        /** @var Collection<int, array<string, mixed>> $emptyCollection */
        $emptyCollection = collect();

        if (! $this->isConnected()) {
            return $emptyCollection;
        }

        $result = $this->getClient()->run(
            'MATCH (author:User)<-[:FOLLOWS]-(follower:User)
                 WITH author, COUNT(DISTINCT follower) as followers
                 WHERE followers >= $min_followers
                 OPTIONAL MATCH (author)-[:AUTHORED]->(article:Article)
                 WITH author, followers, COUNT(DISTINCT article) as articles
                 RETURN author.id as id, author.name as name, author.username as username,
                        followers, articles
                 ORDER BY followers DESC, articles DESC
                 LIMIT $limit',
            [
                'min_followers' => $minFollowers,
                'limit' => $limit,
            ]
        );

        /** @var array<int, array<string, mixed>> $authors */
        $authors = [];
        foreach ($result as $record) {
            $authors[] = [
                'id' => $this->getString($record, 'id'),
                'name' => $this->getString($record, 'name'),
                'username' => $this->getString($record, 'username'),
                'followers' => $this->getInt($record, 'followers'),
                'articles' => $this->getInt($record, 'articles'),
            ];
        }

        /** @var Collection<int, array<string, mixed>> $collection */
        $collection = collect($authors);

        return $collection;
    }

    /**
     * Get topics of interest for a user.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getTopicsOfInterest(string $userId, int $limit): Collection
    {
        /** @var Collection<int, array<string, mixed>> $emptyCollection */
        $emptyCollection = collect();

        if (! $this->isConnected()) {
            return $emptyCollection;
        }

        try {
            $result = $this->getClient()->run(
                'MATCH (u:User {id: $user_id})-[:LIKES]->(a:Article)-[:HAS_TAG]->(tag:Tag)
                 WITH tag, COUNT(DISTINCT a) as interactions
                 RETURN tag.name as name, interactions, "tag" as type
                 ORDER BY interactions DESC
                 LIMIT $limit
                 UNION
                 MATCH (u:User {id: $user_id})-[:LIKES]->(a:Article)-[:IN_CATEGORY]->(cat:Category)
                 WITH cat, COUNT(DISTINCT a) as interactions
                 RETURN cat.name as name, interactions, "category" as type
                 ORDER BY interactions DESC
                 LIMIT $limit',
                [
                    'user_id' => $userId,
                    'limit' => $limit,
                ]
            );

            /** @var array<int, array<string, mixed>> $topics */
            $topics = [];
            foreach ($result as $record) {
                $topics[] = [
                    'name' => $this->getString($record, 'name'),
                    'interactions' => $this->getInt($record, 'interactions'),
                    'type' => $this->getString($record, 'type'),
                ];
            }

            /** @var Collection<int, array<string, mixed>> $collection */
            $collection = collect($topics)
                ->sortByDesc('interactions')
                ->take($limit)
                ->values();

            return $collection;
        } catch (Throwable) {
            return $emptyCollection;
        }
    }

    /**
     * Get recommended articles for a user.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getRecommendedArticlesForUser(string $userId, int $limit): Collection
    {
        /** @var Collection<int, array<string, mixed>> $emptyCollection */
        $emptyCollection = collect();

        if (! $this->isConnected()) {
            return $emptyCollection;
        }

        $result = $this->getClient()->run(
            'MATCH (u:User {id: $user_id})-[:LIKES]->(liked:Article)-[:HAS_TAG|IN_CATEGORY]->(shared)<-[:HAS_TAG|IN_CATEGORY]-(rec:Article)
                 WHERE NOT (u)-[:LIKES]->(rec)
                 AND rec.status = "published"
                 WITH rec, COUNT(DISTINCT shared) as score
                 RETURN rec.id as id, rec.title as title, rec.slug as slug,
                        rec.author_id as author_id, score
                 ORDER BY score DESC, rec.view_count DESC
                 LIMIT $limit',
            [
                'user_id' => $userId,
                'limit' => $limit,
            ]
        );

        /** @var array<int, array<string, mixed>> $articles */
        $articles = [];
        foreach ($result as $record) {
            $articles[] = [
                'id' => $this->getString($record, 'id'),
                'title' => $this->getString($record, 'title'),
                'slug' => $this->getString($record, 'slug'),
                'author_id' => $this->getString($record, 'author_id'),
                'relevance_score' => $this->getInt($record, 'score'),
            ];
        }

        /** @var Collection<int, array<string, mixed>> $collection */
        $collection = collect($articles);

        return $collection;
    }

    /**
     * Get statistics about the graph.
     *
     * @return array<string, int>
     */
    public function getStatistics(): array
    {
        if (! $this->isConnected()) {
            return [
                'users' => 0,
                'articles' => 0,
                'follows' => 0,
                'likes' => 0,
                'tags' => 0,
                'categories' => 0,
            ];
        }

        try {
            $statements = [
                new Statement('MATCH (u:User) RETURN COUNT(u) as count', []),
                new Statement('MATCH (a:Article) RETURN COUNT(a) as count', []),
                new Statement('MATCH ()-[r:FOLLOWS]->() RETURN COUNT(r) as count', []),
                new Statement('MATCH ()-[r:LIKES]->() RETURN COUNT(r) as count', []),
                new Statement('MATCH (t:Tag) RETURN COUNT(t) as count', []),
                new Statement('MATCH (c:Category) RETURN COUNT(c) as count', []),
            ];

            $results = $this->getClient()->runStatements($statements);

            $usersRecord = $results[0]->first();
            $articlesRecord = $results[1]->first();
            $followsRecord = $results[2]->first();
            $likesRecord = $results[3]->first();
            $tagsRecord = $results[4]->first();
            $categoriesRecord = $results[5]->first();

            return [
                'users' => $usersRecord !== null ? $this->getInt($usersRecord, 'count') : 0,
                'articles' => $articlesRecord !== null ? $this->getInt($articlesRecord, 'count') : 0,
                'follows' => $followsRecord !== null ? $this->getInt($followsRecord, 'count') : 0,
                'likes' => $likesRecord !== null ? $this->getInt($likesRecord, 'count') : 0,
                'tags' => $tagsRecord !== null ? $this->getInt($tagsRecord, 'count') : 0,
                'categories' => $categoriesRecord !== null ? $this->getInt($categoriesRecord, 'count') : 0,
            ];
        } catch (Throwable) {
            return [
                'users' => 0,
                'articles' => 0,
                'follows' => 0,
                'likes' => 0,
                'tags' => 0,
                'categories' => 0,
            ];
        }
    }

    /**
     * Clear all data from the graph.
     */
    public function clearAll(): void
    {
        if (! $this->isConnected()) {
            return;
        }

        $this->getClient()->run('MATCH (n) DETACH DELETE n');
    }
}
