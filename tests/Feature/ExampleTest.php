<?php

use function Pest\Laravel\get;

test('the application returns a successful response', function (): void {
    get('/')->assertStatus(200);
});
