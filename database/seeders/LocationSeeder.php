<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\LocationType;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar tipos de local
        $empresa = LocationType::where('slug', 'empresa')->first();
        $cliente = LocationType::where('slug', 'cliente')->first();
        $postoCombustivel = LocationType::where('slug', 'posto-combustivel')->first();
        $outro = LocationType::where('slug', 'outro')->first();

        $locations = [
            // Empresas
            ['name' => 'Matriz Principal', 'type' => 'empresa', 'location_type_id' => $empresa?->id, 'address' => 'Av. Paulista, 1000', 'street' => 'Av. Paulista', 'number' => '1000', 'neighborhood' => 'Bela Vista', 'zip_code' => '01310-100', 'city' => 'São Paulo', 'state' => 'SP', 'notes' => 'Sede administrativa'],
            ['name' => 'Filial Centro', 'type' => 'empresa', 'location_type_id' => $empresa?->id, 'address' => 'Rua Augusta, 500', 'street' => 'Rua Augusta', 'number' => '500', 'neighborhood' => 'Consolação', 'zip_code' => '01305-000', 'city' => 'São Paulo', 'state' => 'SP'],
            ['name' => 'Filial Vila Madalena', 'type' => 'empresa', 'location_type_id' => $empresa?->id, 'address' => 'Rua Harmonia, 200', 'street' => 'Rua Harmonia', 'number' => '200', 'neighborhood' => 'Vila Madalena', 'zip_code' => '05435-000', 'city' => 'São Paulo', 'state' => 'SP'],
            
            // Clientes
            ['name' => 'Cliente ABC Ltda', 'type' => 'cliente', 'location_type_id' => $cliente?->id, 'address' => 'Rua das Flores, 123', 'street' => 'Rua das Flores', 'number' => '123', 'neighborhood' => 'Centro', 'zip_code' => '20010-020', 'city' => 'Rio de Janeiro', 'state' => 'RJ', 'notes' => 'Cliente VIP'],
            ['name' => 'XYZ Comércio', 'type' => 'cliente', 'location_type_id' => $cliente?->id, 'address' => 'Av. Atlântica, 2000', 'street' => 'Av. Atlântica', 'number' => '2000', 'neighborhood' => 'Copacabana', 'zip_code' => '22021-000', 'city' => 'Rio de Janeiro', 'state' => 'RJ'],
            ['name' => 'Empresa DEF S.A.', 'type' => 'cliente', 'location_type_id' => $cliente?->id, 'address' => 'Rua do Comércio, 450', 'street' => 'Rua do Comércio', 'number' => '450', 'neighborhood' => 'Comércio', 'zip_code' => '40015-000', 'city' => 'Salvador', 'state' => 'BA'],
            ['name' => 'GHI Distribuidora', 'type' => 'cliente', 'location_type_id' => $cliente?->id, 'address' => 'Av. Beira Mar, 800', 'street' => 'Av. Beira Mar', 'number' => '800', 'neighborhood' => 'Meireles', 'zip_code' => '60165-121', 'city' => 'Fortaleza', 'state' => 'CE'],
            ['name' => 'JKL Indústria', 'type' => 'cliente', 'location_type_id' => $cliente?->id, 'address' => 'Av. Afonso Pena, 3000', 'street' => 'Av. Afonso Pena', 'number' => '3000', 'neighborhood' => 'Boa Viagem', 'zip_code' => '30130-009', 'city' => 'Belo Horizonte', 'state' => 'MG'],
            
            // Postos de Combustível
            ['name' => 'Posto Shell Paulista', 'type' => 'posto_combustivel', 'location_type_id' => $postoCombustivel?->id, 'address' => 'Av. Paulista, 2000', 'street' => 'Av. Paulista', 'number' => '2000', 'neighborhood' => 'Bela Vista', 'zip_code' => '01310-300', 'city' => 'São Paulo', 'state' => 'SP'],
            ['name' => 'Posto Ipiranga Centro', 'type' => 'posto_combustivel', 'location_type_id' => $postoCombustivel?->id, 'address' => 'Av. Ipiranga, 500', 'street' => 'Av. Ipiranga', 'number' => '500', 'neighborhood' => 'República', 'zip_code' => '01046-000', 'city' => 'São Paulo', 'state' => 'SP'],
            ['name' => 'Posto BR Marginal', 'type' => 'posto_combustivel', 'location_type_id' => $postoCombustivel?->id, 'address' => 'Marginal Tietê, Km 10', 'street' => 'Marginal Tietê', 'number' => 'S/N', 'neighborhood' => 'Limão', 'zip_code' => '02714-000', 'city' => 'São Paulo', 'state' => 'SP'],
            ['name' => 'Posto Petrobras Copacabana', 'type' => 'posto_combustivel', 'location_type_id' => $postoCombustivel?->id, 'address' => 'Av. Atlântica, 1500', 'street' => 'Av. Atlântica', 'number' => '1500', 'neighborhood' => 'Copacabana', 'zip_code' => '22021-000', 'city' => 'Rio de Janeiro', 'state' => 'RJ'],
            ['name' => 'Posto Shell Barra', 'type' => 'posto_combustivel', 'location_type_id' => $postoCombustivel?->id, 'address' => 'Av. Oceânica, 200', 'street' => 'Av. Oceânica', 'number' => '200', 'neighborhood' => 'Barra', 'zip_code' => '40140-130', 'city' => 'Salvador', 'state' => 'BA'],
        ];

        // Cidades e estados brasileiros para gerar mais locais
        $cities = [
            ['São Paulo', 'SP'],
            ['Rio de Janeiro', 'RJ'],
            ['Belo Horizonte', 'MG'],
            ['Curitiba', 'PR'],
            ['Porto Alegre', 'RS'],
            ['Brasília', 'DF'],
            ['Recife', 'PE'],
            ['Fortaleza', 'CE'],
            ['Salvador', 'BA'],
            ['Goiânia', 'GO'],
            ['Manaus', 'AM'],
            ['Belém', 'PA'],
            ['Vitória', 'ES'],
            ['Florianópolis', 'SC'],
            ['Campinas', 'SP'],
            ['Guarulhos', 'SP'],
            ['São Bernardo do Campo', 'SP'],
            ['Santos', 'SP'],
            ['Ribeirão Preto', 'SP'],
            ['Sorocaba', 'SP'],
        ];

        $streets = [
            'Rua', 'Avenida', 'Praça', 'Alameda', 'Travessa', 'Estrada', 'Rodovia',
        ];

        $streetNames = [
            'das Flores', 'do Comércio', 'Principal', 'Central', 'das Acácias', 'dos Ipês',
            'Brasil', 'Independência', 'Liberdade', 'da Paz', 'da Esperança', 'Bandeirantes',
            'Anchieta', 'Imigrantes', 'Castelo Branco', 'Washington Luís', 'Presidente Vargas',
            'Brigadeiro', 'Rebouças', 'Faria Lima', '9 de Julho', '23 de Maio', 'Europa',
            'América', 'Ásia', 'África', 'Antártica', 'Oceania', 'Atlântica', 'Pacífico',
            'da Praia', 'da Avenida', 'da Estação', 'do Parque', 'da Igreja', 'do Hospital',
        ];

        $neighborhoods = [
            'Centro', 'Vila Nova', 'Jardim', 'Parque', 'Alto', 'Baixo', 'Norte', 'Sul',
            'Leste', 'Oeste', 'Industrial', 'Residencial', 'Comercial', 'Rural',
            'Bela Vista', 'Copacabana', 'Ipanema', 'Leblon', 'Barra', 'Meireles',
            'Boa Viagem', 'Aldeota', 'Comércio', 'Pelourinho', 'Barra', 'Pituba',
        ];

        // Gerar mais locais variados
        $locationTypes = [
            ['type' => 'cliente', 'location_type' => $cliente],
            ['type' => 'posto_combustivel', 'location_type' => $postoCombustivel],
            ['type' => 'outro', 'location_type' => $outro],
        ];

        $clientNames = [
            'Comércio', 'Indústria', 'Distribuidora', 'Importadora', 'Exportadora',
            'Ltda', 'S.A.', 'EIRELI', 'ME', 'Empresa', 'Associados', 'Sociedade',
            'Grupo', 'Holding', 'Corporação', 'Companhia', 'Firma', 'Serviços',
        ];

        $postoNames = [
            'Posto Shell', 'Posto Ipiranga', 'Posto BR', 'Posto Petrobras',
            'Posto Texaco', 'Posto Shell Express', 'Auto Posto', 'Posto 24h',
        ];

        // Adicionar mais 90 locais variados
        for ($i = 0; $i < 90; $i++) {
            $cityData = $cities[array_rand($cities)];
            $city = $cityData[0];
            $state = $cityData[1];
            
            $locationTypeData = $locationTypes[array_rand($locationTypes)];
            $type = $locationTypeData['type'];
            $locationType = $locationTypeData['location_type'];

            $street = $streets[array_rand($streets)];
            $streetName = $streetNames[array_rand($streetNames)];
            $number = rand(10, 9999);
            $neighborhood = $neighborhoods[array_rand($neighborhoods)] . ' ' . ($i % 3 == 0 ? 'I' : ($i % 3 == 1 ? 'II' : 'III'));
            
            $zipCode = str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT) . '-' . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            
            if ($type === 'cliente') {
                $namePrefix = ['ABC', 'XYZ', 'DEF', 'GHI', 'JKL', 'MNO', 'PQR', 'STU', 'VWX', 'YZA'][array_rand(['ABC', 'XYZ', 'DEF', 'GHI', 'JKL', 'MNO', 'PQR', 'STU', 'VWX', 'YZA'])];
                $nameSuffix = $clientNames[array_rand($clientNames)];
                $name = $namePrefix . ' ' . $nameSuffix;
            } elseif ($type === 'posto_combustivel') {
                $postoName = $postoNames[array_rand($postoNames)];
                $name = $postoName . ' ' . $neighborhood;
            } else {
                $otherNames = ['Galpão', 'Depósito', 'Armazém', 'Oficina', 'Garagem', 'Loja', 'Escritório', 'Fábrica'];
                $name = $otherNames[array_rand($otherNames)] . ' ' . $neighborhood;
            }

            $fullStreet = $street . ' ' . $streetName;
            $address = $fullStreet . ', ' . $number;

            $locations[] = [
                'name' => $name,
                'type' => $type,
                'location_type_id' => $locationType?->id,
                'address' => $address,
                'street' => $fullStreet,
                'number' => (string)$number,
                'neighborhood' => $neighborhood,
                'zip_code' => $zipCode,
                'city' => $city,
                'state' => $state,
                'notes' => $i % 5 == 0 ? 'Local importante para rotas' : null,
            ];
        }

        // Criar todos os locais
        foreach ($locations as $locationData) {
            Location::create($locationData);
        }

        $this->command->info('100 locais cadastrados com sucesso!');
        $this->command->info('- Empresas: ' . Location::where('type', 'empresa')->count());
        $this->command->info('- Clientes: ' . Location::where('type', 'cliente')->count());
        $this->command->info('- Postos de Combustível: ' . Location::where('type', 'posto_combustivel')->count());
        $this->command->info('- Outros: ' . Location::where('type', 'outro')->count());
    }
}
