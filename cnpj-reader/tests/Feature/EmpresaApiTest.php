<?php

use App\Models\Empresa;
use Illuminate\Support\Facades\Http;

function brasilApiEmpresa(string $cnpj, string $uf = 'SP', string $municipio = 'São Paulo'): array
{
    return [
        'cnpj' => $cnpj,
        'razao_social' => 'Empresa de Teste Ltda',
        'nome_fantasia' => 'Empresa Teste',
        'uf' => $uf,
        'municipio' => $municipio,
        'descricao_situacao_cadastral' => 'ATIVA',
    ];
}

test('consulta a BrasilAPI, persiste os dados e responde com o JSON completo', function () {
    $cnpj = '61774098000129';
    $dados = brasilApiEmpresa($cnpj);
    Http::fake([
        "https://brasilapi.com.br/api/cnpj/v1/{$cnpj}" => Http::response($dados),
    ]);

    $this->postJson('/api/empresas', ['cnpj' => $cnpj])
        ->assertCreated()
        ->assertExactJson(['data' => $dados]);

    $this->assertDatabaseHas('empresas', [
        'cnpj' => $cnpj,
        'razao_social' => 'Empresa de Teste Ltda',
        'uf' => 'SP',
        'municipio' => 'São Paulo',
    ]);
    expect(Empresa::firstWhere('cnpj', $cnpj)?->dados)->toBe($dados);
});

test('rejeita CNPJ inválido sem consultar a BrasilAPI', function () {
    Http::fake([
        'https://brasilapi.com.br/api/cnpj/v1/*' => Http::response([]),
    ]);

    $this->postJson('/api/empresas', ['cnpj' => '11111111111111'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('cnpj');

    Http::assertNothingSent();
});

test('lista empresas com filtros e consulta individual com todos os dados', function () {
    $cnpjSp = '61774098000129';
    $cnpjRj = '00000000000191';
    $dadosSp = brasilApiEmpresa($cnpjSp);
    $dadosRj = brasilApiEmpresa($cnpjRj, 'RJ', 'Niterói');

    Empresa::create([
        'cnpj' => $cnpjSp,
        'razao_social' => $dadosSp['razao_social'],
        'nome_fantasia' => $dadosSp['nome_fantasia'],
        'uf' => $dadosSp['uf'],
        'municipio' => $dadosSp['municipio'],
        'dados' => $dadosSp,
    ]);
    Empresa::create([
        'cnpj' => $cnpjRj,
        'razao_social' => $dadosRj['razao_social'],
        'nome_fantasia' => $dadosRj['nome_fantasia'],
        'uf' => $dadosRj['uf'],
        'municipio' => $dadosRj['municipio'],
        'dados' => $dadosRj,
    ]);

    $this->getJson('/api/empresas?uf=sp&municipio=São%20Paulo')
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.cnpj', $cnpjSp);

    $this->getJson("/api/empresas/{$cnpjSp}")
        ->assertOk()
        ->assertExactJson(['data' => $dadosSp]);
});
