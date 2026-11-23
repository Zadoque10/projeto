<?php

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertJsonPath('message', 'API de consulta de empresas');
});

test('serves the Swagger UI', function () {
    $this->get('/docs')
        ->assertOk()
        ->assertHeader('content-type', 'text/html; charset=UTF-8');
});
