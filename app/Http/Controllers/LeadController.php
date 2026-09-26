<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class LeadController extends Controller
{
    public function index()
    {
        $this->ensureDemoLeads();

        $leads = Lead::latest()->paginate(25);
        return view('leads.index', compact('leads'));
    }

    public function create()
    {
        return view('leads.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'source' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:new,contacted,qualified,proposal,won,lost',
        ]);

        $data['status'] = $data['status'] ?? Lead::STATUS_NEW;

        $lead = Lead::create($data);

        return redirect()->route('leads.show', $lead)->with('status', 'Lead creado');
    }

    public function show(Lead $lead)
    {
        $statuses = Lead::statusOptions();
        return view('leads.show', compact('lead', 'statuses'));
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'status' => 'required|in:new,contacted,qualified,proposal,won,lost',
        ]);

        $lead->status = $data['status'];
        $lead->save();

        return back()->with('status', 'Estado actualizado a ' . $lead->status_label);
    }

    public function board()
    {
        $statuses = Lead::statusOptions();
        $grouped = Lead::orderBy('created_at', 'desc')
            ->get()
            ->groupBy('status');

        return view('leads.board', compact('statuses', 'grouped'));
    }

    public function importForm()
    {
        return view('leads.import');
    }

    protected function ensureDemoLeads(): void
    {
        if (! app()->environment('local') || Lead::count() > 0) {
            return;
        }

        Lead::create([
            'name' => 'María López',
            'email' => 'maria.lopez@example.com',
            'phone' => '5512345678',
            'source' => 'WhatsApp',
            'status' => Lead::STATUS_NEW,
            'notes' => 'Interesada en remera azul, espera cotización.',
        ]);

        Lead::create([
            'name' => 'Jorge Ramírez',
            'email' => 'jorge.ramirez@example.com',
            'phone' => '5598765432',
            'source' => 'Instagram',
            'status' => Lead::STATUS_CONTACTED,
            'notes' => 'Solicitó talla L y estimado de envío.',
        ]);

        Lead::create([
            'name' => 'Lucía Pérez',
            'email' => 'lucia.perez@example.com',
            'phone' => '5522233344',
            'source' => 'Web',
            'status' => Lead::STATUS_QUALIFIED,
            'notes' => 'Desea comprar 3 unidades en cuanto haya stock.',
        ]);
    }

    public function importProcess(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return back()->withErrors(['csv_file' => 'No se pudo abrir el archivo.']);
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            return back()->withErrors(['csv_file' => 'El archivo CSV está vacío o mal formado.']);
        }

        // normalize headers
        $cols = array_map(function ($h) { return Str::of($h)->trim()->lower()->__toString(); }, $header);

        $created = 0;
        $skipped = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === 1 && trim($row[0]) === '') {
                continue;
            }

            $data = [];
            foreach ($cols as $i => $col) {
                $value = $row[$i] ?? null;
                $data[$col] = $value !== null ? trim($value) : null;
            }

            // map known columns
            $leadData = [
                'name' => $data['name'] ?? ($data['full_name'] ?? null),
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? ($data['telefono'] ?? null),
                'source' => $data['source'] ?? null,
                'notes' => $data['notes'] ?? ($data['comentarios'] ?? null),
            ];

            if (empty($leadData['email']) && empty($leadData['phone'])) {
                $skipped++;
                $errors[] = 'Fila sin email ni teléfono: ' . json_encode($leadData);
                continue;
            }

            try {
                Lead::create($leadData);
                $created++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = 'Error fila: ' . $e->getMessage();
            }
        }

        fclose($handle);

        return redirect()->route('leads.index')->with('import_summary', compact('created','skipped','errors'));
    }
}
