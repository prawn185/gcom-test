<?php

use App\Http\Controllers\AffiliatesController;
use App\Models\Affiliates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->controller = new AffiliatesController();

    $this->affiliatesData = collect(array_map(function ($line) {
        return json_decode($line, true);
    }, file(storage_path('app/public/affiliates.txt'))));

    $this->mockAffiliates = Mockery::mock(Affiliates::class);
    $this->mockAffiliates->shouldReceive('getAffiliates')->andReturn($this->affiliatesData);
    app()->instance(Affiliates::class, $this->mockAffiliates);
});

test('index method returns view with affiliates', function () {
    $response = $this->controller->index();

    expect($response)->toBeInstanceOf(Illuminate\View\View::class);
    expect($response->getName())->toBe('welcome');

    $viewData = $response->getData();
    expect($viewData)->toHaveKey('affiliates');

    // Compare the content of the affiliates data
    $responseAffiliates = $viewData['affiliates'];
    expect($responseAffiliates)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($responseAffiliates->count())->toBe($this->affiliatesData->count());

    // Compare each affiliate in the response to the original data
    $responseAffiliates->each(function ($affiliate, $key) {
        expect($affiliate)->toEqual($this->affiliatesData[$key]);
    });
});


test('save method stores affiliates and returns correct count when database is empty', function () {
    $response = $this->controller->save();

    expect(Affiliates::count())->toBe($this->affiliatesData->count())
        ->and($response->getContent())->toBe(json_encode(['message' => $this->affiliatesData->count() . ' affiliates saved']));
});

test('save method returns message when affiliates are already saved', function () {
    // First save
    $this->controller->save();

    // Second save attempt
    $response = $this->controller->save();

    expect($response->getContent())->toBe(json_encode(['message' => 'Affiliates already saved']));
});

test('find method returns affiliates within distance', function () {
    $this->controller->save();

    $request = Request::create('', 'GET', [
        'lat' => '53.3498',
        'lon' => '-6.2603',
        'dis' => 100,
    ]);

    $response = $this->controller->find($request);

    $content = json_decode($response->getContent(), true);
    expect($content)->toBeArray()
        ->and(count($content))->toBeGreaterThan(0);

    foreach ($content as $affiliate) {
        expect($affiliate['distance'])->toBeLessThanOrEqual(100);
    }
});

test('calculateDistance method calculates correct distance', function () {
    $lat = 53.3498;
    $lon = -6.2603;
    $firstAffiliate = $this->affiliatesData->first();

    $reflector = new ReflectionClass(AffiliatesController::class);
    $method = $reflector->getMethod('calculateDistance');

    $calculatedDistance = $method->invokeArgs($this->controller, [
        $lat,
        $lon,
        $firstAffiliate['latitude'],
        $firstAffiliate['longitude']
    ]);

    expect($calculatedDistance)->toBeGreaterThan(0)
        ->and($calculatedDistance)->toBeLessThan(1000);
});
