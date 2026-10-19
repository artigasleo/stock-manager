<?php

namespace App\Http\Controllers;

use App\Models\Nfce;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class NfceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:sales.edit'),
        ];
    }

    public function export(Request $request): StreamedResponse
    {
        $month = $request->string('month')->value() ?: now()->subMonthNoOverflow()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $notes = Nfce::where('status', 'authorized')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('xml_path')
            ->get();

        $relativeZipPath = 'nfce-exports/'.uniqid('nfce-', true).'.zip';
        Storage::makeDirectory('nfce-exports');
        $zipPath = Storage::path($relativeZipPath);

        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE);

        foreach ($notes as $note) {
            if (Storage::exists($note->xml_path)) {
                $zip->addFile(Storage::path($note->xml_path), "{$note->chave_acesso}.xml");
            }
        }

        $zip->close();

        return response()->streamDownload(function () use ($relativeZipPath) {
            readfile(Storage::path($relativeZipPath));
            Storage::delete($relativeZipPath);
        }, "nfce-{$month}.zip", [
            'Content-Type' => 'application/zip',
        ]);
    }
}
