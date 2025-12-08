<?php

namespace App\Exports;

use App\Models\Trip;
use App\Models\Fueling;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class TripsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $vehicleId;
    protected $year;
    protected $trips;

    public function __construct($vehicleId, $year)
    {
        $this->vehicleId = $vehicleId;
        $this->year = $year;
        
        // Buscar viagens do veículo e ano especificados
        $this->trips = Trip::with(['originLocation', 'destinationLocation', 'driver', 'stops.location'])
            ->where('vehicle_id', $vehicleId)
            ->whereYear('date', $year)
            ->orderBy('date')
            ->orderBy('departure_time')
            ->get();
    }

    public function collection()
    {
        return $this->trips;
    }

    public function headings(): array
    {
        return [
            'ITINERÁRIO',
            'DATA',
            'HORÁRIO SAÍDA',
            'KM SAÍDA',
            'HORÁRIO CHEGADA',
            'KM CHEGADA',
            'KM RODADOS',
            'Tipo/Qtde',
            'Valor',
            'CONDUTOR',
        ];
    }

    public function map($trip): array
    {
        // Montar itinerário: Origem - Paradas - Destino
        $originName = $trip->originLocation->name ?? '';
        $destinationName = $trip->destinationLocation->name ?? '';
        
        $itinerario = $originName;
        
        // Adicionar paradas intermediárias
        foreach ($trip->stops as $stop) {
            $stopName = $stop->location->name ?? '';
            if ($stopName) {
                $itinerario .= ' - ' . $stopName;
            }
        }
        
        // Adicionar destino
        // Sempre adicionar o destino
        if ($destinationName) {
            $itinerario .= ' - ' . $destinationName;
        }
        
        // Se retornou à origem, adicionar origem no final
        // (mesmo que destino seja igual à origem, mostra o retorno)
        if ($trip->return_to_origin) {
            // Se destino é diferente da origem, adicionar origem no final
            if ($trip->origin_location_id !== $trip->destination_location_id) {
                $itinerario .= ' - ' . $originName;
            }
            // Se destino = origem, já está mostrado, não precisa adicionar novamente
        }

        // Formatar data (DD/MM/YYYY)
        $data = $trip->date ? Carbon::parse($trip->date)->format('d/m/Y') : '';

        // Formatar horários (HH:MM)
        $horarioSaida = '';
        if ($trip->departure_time) {
            try {
                $timeString = $this->formatTime($trip->departure_time);
                if ($timeString) {
                    $horarioSaida = Carbon::parse($timeString)->format('H:i');
                }
            } catch (\Exception $e) {
                $horarioSaida = '';
            }
        }

        $horarioChegada = '';
        if ($trip->return_time) {
            try {
                $timeString = $this->formatTime($trip->return_time);
                if ($timeString) {
                    $horarioChegada = Carbon::parse($timeString)->format('H:i');
                }
            } catch (\Exception $e) {
                $horarioChegada = '';
            }
        }

        // KM
        $kmSaida = $trip->odometer_start ?? 0;
        $kmChegada = $trip->odometer_end ?? 0;
        $kmRodados = $trip->km_total ?? ($kmChegada - $kmSaida);

        // Buscar abastecimento relacionado a esta viagem
        // Abastecimento geralmente acontece no mesmo dia e com odômetro próximo ao km_chegada
        $fueling = Fueling::where('vehicle_id', $this->vehicleId)
            ->whereDate('date_time', $trip->date)
            ->where('odometer', $kmChegada)
            ->first();

        // Se não encontrar exato, buscar o mais próximo do mesmo dia (dentro de ±10 km)
        if (!$fueling) {
            $fuelings = Fueling::where('vehicle_id', $this->vehicleId)
                ->whereDate('date_time', $trip->date)
                ->whereBetween('odometer', [$kmChegada - 10, $kmChegada + 10])
                ->get();
            
            // Encontrar o mais próximo manualmente (compatível com SQLite)
            if ($fuelings->count() > 0) {
                $fueling = $fuelings->sortBy(function($f) use ($kmChegada) {
                    return abs($f->odometer - $kmChegada);
                })->first();
            }
        }

        // Formatar Tipo/Qtde: G-22,20 (onde G=Gasolina, E=Etanol, D=Diesel)
        $tipoQtde = '';
        $valor = '';
        
        if ($fueling) {
            // Mapear tipo de combustível para letra
            $fuelTypeMap = [
                'Gasolina' => 'G',
                'Etanol' => 'E',
                'Diesel' => 'D',
            ];
            
            $fuelLetter = $fuelTypeMap[$fueling->fuel_type] ?? '';
            if ($fuelLetter) {
                // Formatar litros com vírgula como separador decimal
                $liters = number_format($fueling->liters, 2, ',', '');
                $tipoQtde = $fuelLetter . '-' . $liters;
                
                // Formatar valor com vírgula como separador decimal
                $valor = number_format($fueling->total_amount, 2, ',', '');
            }
        }

        // Nome do condutor
        $condutor = $trip->driver->name ?? '';

        return [
            $itinerario,
            $data,
            $horarioSaida,
            $kmSaida,
            $horarioChegada,
            $kmChegada,
            $kmRodados,
            $tipoQtde,
            $valor,
            $condutor,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Plan1';
    }

    /**
     * Formata um valor de time para o formato H:i:s válido
     * Remove zeros extras e garante formato correto
     */
    private function formatTime($time)
    {
        if (empty($time)) {
            return null;
        }

        // Se já for uma string, limpar e formatar
        $timeString = (string) $time;
        
        // Remover espaços
        $timeString = trim($timeString);
        
        // Se estiver vazio, retornar null
        if (empty($timeString)) {
            return null;
        }

        // Tentar diferentes formatos
        // Formato esperado: H:i:s ou H:i
        // Remover zeros extras no final (ex: 12:30:000 -> 12:30:00)
        $timeString = preg_replace('/:0+$/', '', $timeString); // Remove :000, :00 no final
        $timeString = preg_replace('/:0+:/', ':', $timeString); // Remove zeros extras no meio
        
        // Validar formato básico (deve ter pelo menos H:i)
        if (!preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $timeString)) {
            return null;
        }

        // Garantir formato H:i:s (adicionar :00 se necessário)
        $parts = explode(':', $timeString);
        if (count($parts) === 2) {
            $timeString .= ':00';
        } elseif (count($parts) === 3) {
            // Garantir que os segundos tenham 2 dígitos
            $parts[2] = str_pad($parts[2], 2, '0', STR_PAD_LEFT);
            $timeString = implode(':', $parts);
        }

        // Validar valores (hora 0-23, minuto 0-59, segundo 0-59)
        $parts = explode(':', $timeString);
        if (count($parts) === 3) {
            $hour = (int) $parts[0];
            $minute = (int) $parts[1];
            $second = (int) $parts[2];
            
            if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59 || $second < 0 || $second > 59) {
                return null;
            }
        }

        return $timeString;
    }
}

