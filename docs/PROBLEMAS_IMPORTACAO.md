# Problemas Comuns na Importação de Planilhas

Este documento descreve os problemas mais comuns que podem impedir a importação de planilhas e suas soluções.

## Problemas Identificados e Corrigidos

### 1. Tratamento de Erros Aprimorado
**Problema**: Erros durante a importação não eram capturados adequadamente, dificultando a identificação do problema.

**Solução**: 
- Adicionado tratamento específico para `PhpOffice\PhpSpreadsheet\Reader\Exception`
- Mensagens de erro mais descritivas
- Logs detalhados no cache de progresso

### 2. Validação de Arquivo
**Problema**: Não havia verificação se o arquivo podia ser lido antes de processar.

**Solução**: 
- Adicionada verificação `is_readable()` antes de processar
- Mensagens de erro claras quando o arquivo não pode ser lido

## Problemas Comuns na Planilha

### 1. Estrutura da Planilha
A planilha deve ter a seguinte estrutura na primeira linha (cabeçalho):

| Coluna | Cabeçalho | Descrição |
|--------|-----------|-----------|
| A | ITINERÁRIO | Rota (ex: "Cidade A - Cidade B") |
| B | DATA | Data da viagem (formato DD/MM/YYYY ou data do Excel) |
| C | HORÁRIO SAÍDA | Horário de saída (formato HH:MM ou hora do Excel) |
| D | KM SAÍDA | Odômetro na saída (número) |
| E | HORÁRIO CHEGADA | Horário de chegada (formato HH:MM ou hora do Excel) |
| F | KM CHEGADA | Odômetro na chegada (número) |
| G | KM RODADOS | Não utilizado (calculado automaticamente) |
| H | Tipo/Qtde | Tipo e quantidade de combustível (ex: "G-22,20") |
| I | Valor | Valor do abastecimento (ex: "150,00") |
| J | CONDUTOR | Nome do motorista (deve existir no sistema) |

**Importante**: 
- A primeira linha deve ser o cabeçalho
- Os dados começam na linha 2
- A linha com "TOTAL KM RODADOS" encerra a leitura

### 2. Problemas com Datas

**Sintomas**: 
- Mensagens de erro como "Erro ao converter data"
- Linhas sendo puladas

**Possíveis Causas**:
- Formato de data incorreto
- Data como texto em formato não reconhecido
- Data vazia ou nula

**Soluções**:
- Use formato DD/MM/YYYY (ex: 15/01/2025)
- Ou use o formato de data nativo do Excel
- Certifique-se de que todas as datas estão preenchidas

### 3. Problemas com Motoristas (Condutores)

**Sintomas**:
- Mensagens de erro como "Motorista 'Nome' não encontrado"
- Linhas sendo puladas

**Possíveis Causas**:
- Nome do motorista não existe no sistema
- Nome com diferenças de maiúsculas/minúsculas
- Nome com espaços extras

**Soluções**:
- Certifique-se de que o motorista está cadastrado no sistema
- Use o nome exato como cadastrado
- Verifique se não há espaços extras no início ou fim

### 4. Problemas com Quilometragem

**Sintomas**:
- Mensagens de erro como "KM Chegada menor que KM Saída"
- Linhas sendo puladas

**Possíveis Causas**:
- KM de chegada menor que KM de saída
- Valores zero ou negativos
- Valores não numéricos

**Soluções**:
- Verifique que KM CHEGADA >= KM SAÍDA
- Certifique-se de que ambos os valores são números válidos
- Verifique que os valores são maiores que zero

### 5. Problemas com Abastecimento

**Sintomas**:
- Abastecimentos não sendo registrados

**Possíveis Causas**:
- Formato incorreto do campo "Tipo/Qtde"
- Valor não numérico ou zero
- Ambos os campos (Tipo/Qtde e Valor) devem estar preenchidos

**Soluções**:
- Formato: "G-22,20" onde:
  - G = Gasolina, E = Etanol, D = Diesel
  - 22,20 = quantidade em litros (use vírgula como separador decimal)
- Valor: use vírgula como separador decimal (ex: "150,00")

### 6. Problemas com Itinerário

**Sintomas**:
- Mensagens de erro sobre itinerário vazio ou inválido

**Possíveis Causas**:
- Campo vazio
- Formato incorreto

**Soluções**:
- Preencha o campo ITINERÁRIO
- Use formato: "Origem - Destino" ou "Origem - Parada - Destino"
- Múltiplas paradas: "Origem - Parada1 - Parada2 - Destino"

## Como Verificar se a Importação Funcionou

1. **Verifique os Logs**: Na página de progresso, os logs mostram:
   - Linhas processadas
   - Erros encontrados
   - Avisos sobre linhas puladas

2. **Verifique os Registros**: Após a importação:
   - Verifique se os percursos foram criados em "Percursos"
   - Verifique se os abastecimentos foram criados em "Abastecimentos"

3. **Verifique o Odômetro**: O odômetro do veículo deve ser atualizado automaticamente

## Mensagens de Erro Comuns

| Mensagem | Causa | Solução |
|----------|-------|---------|
| "Erro ao converter data" | Data em formato inválido | Verifique o formato da data |
| "Motorista 'X' não encontrado" | Motorista não cadastrado | Cadastre o motorista no sistema |
| "KM Chegada menor que KM Saída" | Valores incorretos | Verifique os valores de KM |
| "Campo 'X' está vazio" | Campo obrigatório vazio | Preencha todos os campos obrigatórios |
| "Arquivo não encontrado" | Problema com o arquivo | Tente fazer upload novamente |
| "Erro ao ler arquivo Excel" | Arquivo corrompido ou formato incorreto | Verifique se o arquivo é .xlsx ou .xls válido |

## Formato Esperado da Planilha

```
| ITINERÁRIO | DATA | HORÁRIO SAÍDA | KM SAÍDA | HORÁRIO CHEGADA | KM CHEGADA | KM RODADOS | Tipo/Qtde | Valor | CONDUTOR |
|------------|------|---------------|----------|-----------------|------------|------------|-----------|-------|----------|
| A - B      | 15/01/2025 | 08:00 | 10000 | 10:00 | 10050 | 50 | G-22,20 | 150,00 | João Silva |
```

**Notas**:
- Primeira linha é cabeçalho (linha 1)
- Dados começam na linha 2
- Linha com "TOTAL KM RODADOS" encerra a leitura
- Campos obrigatórios: ITINERÁRIO, DATA, CONDUTOR

## Melhorias Implementadas

1. ✅ Tratamento de erros aprimorado com mensagens mais claras
2. ✅ Validação de arquivo antes de processar
3. ✅ Logs detalhados de progresso
4. ✅ Tratamento específico para erros de leitura do Excel
5. ✅ Mensagens de erro mais descritivas

## Próximos Passos

Se a importação ainda falhar após verificar todos os pontos acima:

1. Verifique os logs na página de progresso
2. Verifique o log do Laravel em `storage/logs/laravel.log`
3. Verifique se todos os dados estão no formato correto
4. Tente importar uma planilha menor primeiro para testar
5. Entre em contato com o suporte técnico com os logs de erro

