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

            //skip first row to miss out CSV header
            if ($firstRow) {
                $firstRow = false;
                continue;
            }

            $name = trim($row[0]);
            if ($name === '') continue;

            // Split into individual entries - "Dr & Mrs Joe Bloggs"- ["Dr", "Mrs Joe Bloggs"]
            $names = preg_split('/\s+(and|&)\s+/i', $name);
            $names = array_map('trim', $names);

            $lastPosition = end($names);
            $lastPerson = explode(' ', trim($lastPosition));

            // The shared last name we assume if the lastPerson has multiple words
            $hasMultipleWords = count($lastPerson) > 1;

            //if last person has length greater than 1 then set shared last to final position (assumed last name)
            $sharedLast = $hasMultipleWords ? end($lastPerson) : null;

            foreach ($names as $singleName) {
                $rows[] = $this->splitName($singleName, $sharedLast);
            }
        }

        fclose($handle);

        // return inertia response to the frontend instead of response->json()
        return Inertia::render('Homepage', [
            'people' => $rows
        ]);
    }

    private function splitName(string $name, ?string $sharedLast = null): array
    {
        // split into array, trim any extra spacing ['Mr', 'John', 'Smith']
        $nameParts = preg_split('/\s+/', trim($name));

        // grab title from first array position
        $title = array_shift($nameParts);

        $initial = null;
        $first = null;
        $last = null;

        if (!empty($nameParts)) {
            // set last name to last item in the array
            $lastName = array_pop($nameParts);

            // handle length for possible initial case when . is trimmed
            $checkInitial = strlen(rtrim($lastName, '.')) === 1;

            if ($checkInitial) {
                $initial = rtrim($lastName, '.');
            } else {
                // set last to the lastName if length greater than 1
                $last = $lastName;
            }
        }

        // First name or initial
        if (!empty($nameParts)) {
            $firstName = $nameParts[0];
            $trimFirstName = rtrim($firstName, '.');

            if (strlen($trimFirstName) === 1) {
                $initial = $trimFirstName;
            } else {
                $first = $firstName;
            }
        }

        // handle case where $sharedLast is passed in - Dr & Mrs Joe Bloggs
        if (!$last && $sharedLast) {
            $last = $sharedLast;
        }

        return [
            'title'      => $title,
            'first_name' => $first,
            'initial'    => $initial,
            'last_name'  => $last,
        ];
    }

}
