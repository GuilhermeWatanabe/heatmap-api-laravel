<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    public function __construct()
    {
        $this->validationRules = [
            'name' => 'required',
            'value' => 'required|min:-100|numeric',
            'volume' => 'required|integer'
        ];
        $this->validationMessages = [
            'required' => "O campo :attribute não pode ser vazio.",
            'min' => "O valor mínimo de :attribute não pode ser menor que -100.",
            'numeric' => "O valor passado para :attribute não é um número.",
            'integer' => "O valor passado para :attribute não é um inteiro.",
            'exists' => "Este conteúdo não existe.",
            'filled' => "O campo :attribute não pode ser uma frase vazia."
        ];
        $this->validationCustomAttributes = [
            'name' => 'nome',
            'value' => 'valor',
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Stock::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->only(['name', 'value', 'volume']),
            $this->validationRules,
            $this->validationMessages,
            $this->validationCustomAttributes
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        return response()->json(Stock::create($request->only(['name', 'value', 'volume'])), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validationRules = [
            'id' => 'required|integer|exists:stocks,id',
            'name' => 'filled',
            'value' => 'filled|min:-100|numeric',
            'volume' => 'filled|integer'
        ];

        $validator = Validator::make(
            array_merge(['id' => $id], $request->only(['name', 'value', 'volume'])),
            $validationRules,
            $this->validationMessages,
            $this->validationCustomAttributes
        );

        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $stock = Stock::find($id);
        $stock->fill($request->all());
        $stock->save();

        return response()->json($stock);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!Stock::destroy($id)) {
            return response()->json(['errors' => ['id' => "Este conteúdo não existe."]], 400);
        }

        return response()->json(['message' => 'Excluído com sucesso.']);
    }
}
