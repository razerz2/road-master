@echo off
REM Script para configurar o repositório Git e fazer push para o GitHub
REM Execute este script após instalar o Git e garantir que está no PATH

echo Inicializando repositório Git...
git init

echo Adicionando README.md...
git add README.md

echo Fazendo primeiro commit...
git commit -m "first commit"

echo Renomeando branch para main...
git branch -M main

echo Adicionando remote origin...
git remote add origin https://github.com/razerz2/road-master.git

echo Fazendo push para o GitHub...
git push -u origin main

echo Concluído!
pause

