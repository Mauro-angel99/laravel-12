# update.ps1 - Script di aggiornamento completo dell'applicazione
# Posizionarsi nella cartella del progetto ed eseguire: .\update.ps1

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   AGGIORNAMENTO APPLICAZIONE" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# 1. Git Pull
Write-Host "[1/4] Scarico aggiornamenti da Git..." -ForegroundColor Yellow
git stash
git pull
git stash pop
Write-Host "OK - Git aggiornato" -ForegroundColor Green
Write-Host ""

# 2. Build Frontend
Write-Host "[2/4] Compilazione Frontend (npm build)..." -ForegroundColor Yellow
docker run --rm -v ${PWD}:/app -w /app node:latest sh -c "npm install && npm run build"
Write-Host "OK - Frontend compilato" -ForegroundColor Green
Write-Host ""

# 3. Migrazione Database
Write-Host "[3/4] Esecuzione migrazioni database..." -ForegroundColor Yellow
docker compose exec app php artisan migrate --force
Write-Host "OK - Database aggiornato" -ForegroundColor Green
Write-Host ""

# 4. Pulizia Cache
Write-Host "[4/4] Pulizia cache applicazione..." -ForegroundColor Yellow
docker compose exec app php artisan optimize:clear
Write-Host "OK - Cache pulita" -ForegroundColor Green
Write-Host ""

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   AGGIORNAMENTO COMPLETATO!" -ForegroundColor Cyan
Write-Host "   Premi CTRL+F5 sul browser." -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
