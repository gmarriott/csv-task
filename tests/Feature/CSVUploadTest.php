<?php

use Illuminate\Http\UploadedFile;

it('parses uploaded CSV into people and skips the first homeowner row', function () {

    $csv = <<<CSV
        homeowner
        Mr F. Fredrickson
        Mrs Jane Smith
    CSV;

    $file = UploadedFile::fake()->createWithContent('names.csv', $csv);

    $response = $this
        ->withHeader('X-Inertia', 'true')
        ->withHeader('Accept', 'application/json')
        ->post(route('csv.upload'), [
            'file' => $file,
        ]);

    // Make sure the request was OK
    $response->assertStatus(200);

    // Decode the Inertia JSON payload
    $data = $response->json();

    $people = $data['props']['people'] ?? [];

    expect($people)->toHaveCount(2);

    expect($people[0])->toMatchArray([
        'title' => 'Mr',
        'first_name' => null,
        'initial' => 'F',
        'last_name' => 'Fredrickson',
    ]);

    expect($people[1])->toMatchArray([
        'title' => 'Mrs',
        'first_name' => 'Jane',
        'initial' => null,
        'last_name' => 'Smith',
    ]);
});
