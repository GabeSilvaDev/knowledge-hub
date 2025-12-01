<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Neo4j Connection Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Neo4j connection settings. These settings
    | will be used by the Neo4j PHP client to connect to your Neo4j database.
    |
    */

    'host' => env('NEO4J_HOST', 'neo4j'),

    'port' => (int) env('NEO4J_PORT', 7687),

    'username' => env('NEO4J_USERNAME', 'neo4j'),

    'password' => env('NEO4J_PASSWORD', 'password'),

    'database' => env('NEO4J_DATABASE', 'neo4j'),

    /*
    |--------------------------------------------------------------------------
    | Connection URL
    |--------------------------------------------------------------------------
    |
    | The full connection URL for Neo4j Bolt protocol.
    |
    */

    'url' => env('NEO4J_URL', 'bolt://neo4j:7687'),

    /*
    |--------------------------------------------------------------------------
    | Recommendation Settings
    |--------------------------------------------------------------------------
    |
    | Settings for the recommendation system including limits and thresholds.
    |
    */

    'recommendations' => [
        'max_users' => (int) env('NEO4J_MAX_USER_RECOMMENDATIONS', 10),

        'max_articles' => (int) env('NEO4J_MAX_ARTICLE_RECOMMENDATIONS', 10),

        'max_authors' => (int) env('NEO4J_MAX_AUTHOR_RECOMMENDATIONS', 10),

        'max_topics' => (int) env('NEO4J_MAX_TOPIC_RECOMMENDATIONS', 10),

        'min_followers_for_influential' => (int) env('NEO4J_MIN_FOLLOWERS_INFLUENTIAL', 5),

        'cache_ttl' => (int) env('NEO4J_RECOMMENDATIONS_CACHE_TTL', 3600),
    ],
];
