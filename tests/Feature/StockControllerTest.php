<?php

namespace Tests\Feature;

use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StockControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $baseURL = '/api/stocks';

    public function test_if_gets_all_the_stocks()
    {
        $stocks = Stock::factory()->count(5)->create();

        $response = $this->get($this->baseURL);

        $this->assertDatabaseCount('stocks', 5);
        $response->assertOk();
        $response->assertJsonCount(5);
        foreach ($stocks as $s) {
            $this->assertDatabaseHas('stocks', [
                'name' => $s->name,
                'value' => $s->value,
                'volume' => $s->volume
            ]);
            $response->assertJsonFragment([
                'name' => $s->name,
                'value' => $s->value,
                'volume' => $s->volume
            ]);
        }
    }

    public function test_if_creates_a_stock()
    {
        $stock = Stock::factory()->make();

        $response = $this->post($this->baseURL, $stock->toArray());

        $this->assertDatabaseCount('stocks', 1);
        $this->assertDatabaseHas('stocks', [
            'name' => $stock->name,
            'value' => $stock->value,
            'volume' => $stock->volume
        ]);
        $response->assertCreated();
        $response->assertJsonPath('name', $stock->name);
        $response->assertJsonPath('value', $stock->value);
        $response->assertJsonPath('volume', $stock->volume);
    }

    public function test_if_fails_when_try_to_register_sending_no_data()
    {
        $response = $this->post($this->baseURL, []);

        $response->assertStatus(400);
        $response->assertJsonValidationErrors([
            'name' => 'O campo nome não pode ser vazio.',
            'value' => 'O campo valor não pode ser vazio.',
            'volume' => 'O campo volume não pode ser vazio.'
        ]);
    }

    public function test_if_fails_when_try_to_register_sending_invalid_data()
    {
        $response = $this->post($this->baseURL, [
            'name' => '',
            'value' => 'not a numeric',
            'volume' => 'not a number'
        ]);

        $response->assertStatus(400);
        $response->assertJsonValidationErrors([
            'name' => 'O campo nome não pode ser vazio.',
            'value' => 'O valor passado para valor não é um número.',
            'volume' => 'O valor passado para volume não é um inteiro.'
        ]);
    }

    public function test_if_fails_when_try_to_register_with_invalid_min_value()
    {
        $stock = Stock::factory()->make(['value' => '-110']);
        
        $response = $this->post($this->baseURL, $stock->toArray());

        $response->assertStatus(400);
        $response->assertJsonValidationErrors([
            'value' => 'O valor mínimo de valor não pode ser menor que -100.'
        ]);
    }

    public function test_if_updates_with_patch_request()
    {
        $stock = Stock::factory()->create();

        $response = $this->patch($this->baseURL . '/' . $stock->id, [
            'name' => 'another name',
            'value' => $stock->value + 5,
            'volume' => $stock->volume + 5
        ]);

        $this->assertDatabaseCount('stocks', 1);
        $this->assertDatabaseHas('stocks', [
            'name' => 'another name',
            'value' => $stock->value + 5,
            'volume' => $stock->volume + 5
        ]);
        $response->assertOk();
        $response->assertJsonPath('name', 'another name');
        $response->assertJsonPath('value', $stock->value + 5);
        $response->assertJsonPath('volume', $stock->volume + 5);
    }

    public function test_if_fails_to_update_with_invalid_data_in_patch_request()
    {
        $stock = Stock::factory()->create();

        $response = $this->patch($this->baseURL . '/' . $stock->id, [
            'name' => '',
            'value' => 'not a numeric',
            'volume' => 'not a number'
        ]);

        $response->assertStatus(400);
        $response->assertJsonValidationErrors([
            'name' => 'O campo nome não pode ser uma frase vazia.',
            'value' => 'O valor passado para valor não é um número.',
            'volume' => 'O valor passado para volume não é um inteiro.'
        ]);

    }

    public function test_if_fails_when_try_to_update_with_invalid_min_value()
    {
        $stock = Stock::factory()->create();
        
        $response = $this->patch($this->baseURL . '/' . $stock->id, ['value' => '-110']);

        $response->assertStatus(400);
        $response->assertJsonValidationErrors([
            'value' => 'O valor mínimo de valor não pode ser menor que -100.'
        ]);

    }

    public function test_if_fails_when_try_to_update_an_inexistent_content_in_patch_request()
    {
        $stock = Stock::factory()->create();

        $response = $this->patch($this->baseURL . '/' . '99999', $stock->toArray());

        $response->assertStatus(400);
        $response->assertJsonValidationErrors([
            'id' => 'Este conteúdo não existe.'
        ]);
    }

    public function test_if_fails_when_try_to_pass_a_string_as_an_id_when_updating_a_content_with_patch_request()
    {
        $stock = Stock::factory()->create();

        $response = $this->patch($this->baseURL . '/' . 'invalid', $stock->toArray());

        $response->assertStatus(400);
        $response->assertJsonValidationErrors([
            'id' => 'O valor passado para id não é um inteiro.'
        ]);
    }

    public function test_if_deletes_by_id()
    {
        $stock = Stock::factory()->create();

        $response = $this->delete($this->baseURL . '/' . $stock->id);

        $response->assertOk();
        $response->assertJsonPath('message', 'Excluído com sucesso.');
    }

    public function test_if_fails_when_try_to_delete_with_nonexistent_id()
    {
        $response = $this->delete($this->baseURL . '/' . '99999');

        $response->assertStatus(400);
        $response->assertJsonValidationErrors([
            'id' => 'Este conteúdo não existe.'
        ]);
    }
}
