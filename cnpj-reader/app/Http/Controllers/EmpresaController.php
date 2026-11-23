<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EmpresaController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cnpj' => [
                'required',
                'string',
                'regex:/^\d{14}$/',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! $this->isValidCnpj($value)) {
                        $fail('O CNPJ informado é inválido.');
                    }
                },
            ],
        ]);

        $cnpj = $validated['cnpj'];

        try {
            $response = Http::acceptJson()
                ->timeout(10)
                ->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");
        } catch (ConnectionException) {
            return response()->json([
                'message' => 'Não foi possível consultar a BrasilAPI.',
            ], 502);
        }

        if ($response->status() === 404) {
            return response()->json([
                'message' => 'CNPJ não encontrado na BrasilAPI.',
            ], 404);
        }

        if (! $response->successful()) {
            return response()->json([
                'message' => 'A BrasilAPI não conseguiu consultar o CNPJ.',
            ], 502);
        }

        $dados = $response->json();

        if (! is_array($dados) || ! isset($dados['cnpj']) || preg_replace('/\D/', '', (string) $dados['cnpj']) !== $cnpj) {
            return response()->json([
                'message' => 'A BrasilAPI retornou dados inválidos para este CNPJ.',
            ], 502);
        }

        $empresa = Empresa::firstOrNew(['cnpj' => $cnpj]);
        $created = ! $empresa->exists;
        $empresa->fill([
            'razao_social' => $this->stringValue($dados, 'razao_social'),
            'nome_fantasia' => $this->stringValue($dados, 'nome_fantasia'),
            'uf' => $this->stringValue($dados, 'uf'),
            'municipio' => $this->stringValue($dados, 'municipio'),
            'dados' => $dados,
        ]);
        $empresa->save();

        return response()->json(['data' => $empresa->dados], $created ? 201 : 200);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'uf' => ['sometimes', 'string', 'size:2'],
            'municipio' => ['sometimes', 'string', 'max:255'],
        ]);

        $empresas = Empresa::query()
            ->when(isset($filters['uf']), fn ($query) => $query->where('uf', strtoupper($filters['uf'])))
            ->when(isset($filters['municipio']), fn ($query) => $query->where('municipio', 'like', $filters['municipio']))
            ->orderBy('razao_social')
            ->get(['cnpj', 'razao_social', 'nome_fantasia', 'uf', 'municipio']);

        return response()->json([
            'data' => $empresas,
            'total' => $empresas->count(),
        ]);
    }

    public function show(string $cnpj): JsonResponse
    {
        $empresa = Empresa::where('cnpj', $cnpj)->firstOrFail();

        return response()->json(['data' => $empresa->dados]);
    }

    private function isValidCnpj(string $cnpj): bool
    {
        if (preg_match('/^(\d)\1{13}$/', $cnpj) === 1) {
            return false;
        }

        $digits = array_map('intval', str_split($cnpj));
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        foreach ([12, 13] as $position) {
            $sum = 0;

            for ($index = 0; $index < $position; $index++) {
                $weightIndex = count($weights) - $position + $index;
                $sum += $digits[$index] * $weights[$weightIndex];
            }

            $remainder = $sum % 11;
            $checkDigit = $remainder < 2 ? 0 : 11 - $remainder;

            if ($digits[$position] !== $checkDigit) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function stringValue(array $dados, string $key): ?string
    {
        return isset($dados[$key]) && is_string($dados[$key]) ? $dados[$key] : null;
    }
}
