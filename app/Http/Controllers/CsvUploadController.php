<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CsvUploadController extends Controller
{
    public function parse(Request $request): Response
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv'],
        ]);

        $rows = [];
        $firstRow = true;
        $handle = fopen($request->file('file')->getRealPath(), 'r');

        while (($row = fgetcsv($handle)) !== false) {

            if ($firstRow) {
                $firstRow = false;
                continue; // skip header row
            }

            $name = trim($row[0]);
            if ($name === '') continue;
            $rows[] = $this->splitName($name);
        }

        fclose($handle);

//       dd(response()->json($rows));

        return Inertia::render('Homepage', [
            'people' => $rows
        ]);
    }

    private function splitName(string $name): array
    {
        $parts = explode(' ', $name);

        $title = $parts[0];
        $last = end($parts);
        $first = null;
        $initial = null;

        if (count($parts) === 3) {
            $mid = rtrim($parts[1], '.');
            if (strlen($mid) === 1) {
                $initial = $mid;
            } else {
                $first = $parts[1];
            }
        }

        return [
            'title' => $title,
            'first_name' => $first,
            'initial' => $initial,
            'last_name' => $last,
        ];
    }
}
