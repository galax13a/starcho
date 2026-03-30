<?php

test('returns a successful response', function () {
    $response = $this->get(route('app.dashboard'));

    $response->assertOk();
});