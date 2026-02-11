<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkPhaseImageRequest;
use App\Models\WorkPhaseImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WorkPhaseImageController extends Controller
{
    /**
     * Ottiene tutte le immagini per un dato FLLAV e OPART
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'fllav' => 'required|string|max:50',
            'opart' => 'required|string|max:50',
        ]);

        $images = WorkPhaseImage::where('fllav', $request->fllav)
            ->where('opart', $request->opart)
            ->with('uploader:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($images);
    }

    /**
     * Carica una o più immagini
     */
    public function store(StoreWorkPhaseImageRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $uploadedImages = [];

            foreach ($request->file('images') as $image) {
                // Genera un nome file unico
                $fileName = $image->getClientOriginalName();
                $extension = $image->getClientOriginalExtension();
                $uniqueName = Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) 
                    . '_' . time() 
                    . '_' . Str::random(8) 
                    . '.' . $extension;

                // Salva il file nella directory work_phase_images
                $path = $image->storeAs(
                    'work_phase_images/' . $request->fllav . '/' . $request->opart,
                    $uniqueName,
                    'public'
                );

                // Crea il record nel database
                $workPhaseImage = WorkPhaseImage::create([
                    'fllav' => $request->fllav,
                    'opart' => $request->opart,
                    'file_name' => $fileName,
                    'file_path' => $path,
                    'mime_type' => $image->getMimeType(),
                    'file_size' => $image->getSize(),
                    'uploaded_by' => auth()->id(),
                ]);

                $uploadedImages[] = $workPhaseImage->load('uploader:id,name');

                Log::info('Immagine caricata', [
                    'user_id' => auth()->id(),
                    'fllav' => $request->fllav,
                    'opart' => $request->opart,
                    'file_name' => $fileName,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => count($uploadedImages) > 1 
                    ? 'Immagini caricate con successo' 
                    : 'Immagine caricata con successo',
                'images' => $uploadedImages,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Errore upload immagini', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Errore durante il caricamento delle immagini',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Elimina un'immagine
     */
    public function destroy(WorkPhaseImage $workPhaseImage): JsonResponse
    {
        try {
            $workPhaseImage->delete(); // Il file viene eliminato automaticamente dal model event

            Log::info('Immagine eliminata', [
                'user_id' => auth()->id(),
                'image_id' => $workPhaseImage->id,
                'fllav' => $workPhaseImage->fllav,
                'opart' => $workPhaseImage->opart,
            ]);

            return response()->json([
                'message' => 'Immagine eliminata con successo'
            ]);

        } catch (\Exception $e) {
            Log::error('Errore eliminazione immagine', [
                'error' => $e->getMessage(),
                'image_id' => $workPhaseImage->id,
            ]);

            return response()->json([
                'message' => 'Errore durante l\'eliminazione dell\'immagine',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
