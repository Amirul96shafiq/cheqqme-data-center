<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
  public function index(Request $request)
  {
    $query = Client::query()
      ->with(['projects', 'documents', 'importantUrls', 'updatedBy']);

    $limit = (int) $request->input('limit', 50);
    $clients = $query->limit($limit)->get();

    return response()->json(['clients' => $clients]);
  }
}
