<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
  public function index(Request $request)
  {
    $query = Document::query()
      ->with(['project', 'updatedBy']);

    $limit = (int) $request->input('limit', 50);
    $documents = $query->limit($limit)->get();

    return response()->json(['documents' => $documents]);
  }
}
