# Script para configurar o repositório Git e fazer push para o GitHub
# Execute este script após instalar o Git e garantir que está no PATH

Write-Host "Inicializando repositório Git..." -ForegroundColor Green
git init

Write-Host "Adicionando README.md..." -ForegroundColor Green
git add README.md

Write-Host "Fazendo primeiro commit..." -ForegroundColor Green
git commit -m "first commit"

Write-Host "Renomeando branch para main..." -ForegroundColor Green
git branch -M main

Write-Host "Adicionando remote origin..." -ForegroundColor Green
git remote add origin https://github.com/razerz2/road-master.git

Write-Host "Fazendo push para o GitHub..." -ForegroundColor Green
git push -u origin main

Write-Host "Concluído!" -ForegroundColor Green

