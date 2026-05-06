<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

// Exemplo 1 GET
Route::get('user/{id}', function ($id) {
    $response = Http::get("https://dummyjson.com/user/$id");

    if ($response->successful()) {
        $dados = $response->json();
        return response()->json([
            'status' => 'sucesso',
            'resultado' => [
                'identificador' => $dados['id'],
                'nome' => $dados['firstName'] . ' ' . $dados['lastName'],
                'email' => $dados['email'],
                'telefone' => $dados['phone']
            ]
        ]);
    } else {
        return response()->json(['error' => 'Usuário não encontrado'], 404);
    }
});

// Exemplo 2 POST
Route::post('user/novo', function (Request $request) {
    $dados = $request->validate([
        'firstName' => 'required|string',
        'lastName' => 'required|string',
        'email' => 'required|email',
        'phone' => 'required|string'
    ]);

    $response = Http::post('https://dummyjson.com/user/add', [
        'firstName' => $dados['firstName'],
        'lastName' => $dados['lastName'],
        'email' => $dados['email'],
        'phone' => $dados['phone']
    ]);

    if ($response->successful()) {
        $resultado = $response->json();
        return response()->json([
            'status' => 'sucesso',
            'id' => $resultado['id'],
            'resultado' => [
                'nome' => $resultado['firstName'] . ' ' . $resultado['lastName'],
                'email' => $resultado['email'],
                'telefone' => $resultado['phone']
            ]
        ], 201);
    } else {
        return response()->json(['error' => 'Erro ao criar usuário'], 400);
    }
});