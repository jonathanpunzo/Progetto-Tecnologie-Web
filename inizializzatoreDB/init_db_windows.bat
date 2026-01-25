@echo off
setlocal

REM --- TITOLO E CONFIGURAZIONE ---
echo ==========================================
echo   SETUP DATABASE AUTOMATICO (Universale)
echo ==========================================
echo.

set "SCRIPT_DIR=%~dp0"
set "PSQL="

REM --- 1. CERCA PSQL NELLE VARIABILI DI SISTEMA ---
where psql >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] Trovato psql nelle variabili globali.
    set "PSQL=psql"
    goto :Found
)

REM --- 2. CERCA NELLE CARTELLE STANDARD (da v18 a v9) ---
echo [INFO] Cerco psql nelle cartelle standard...
set "CheckVersions=18 17 16 15 14 13 12 11 10 9.6"

for %%V in (%CheckVersions%) do (
    if exist "C:\Program Files\PostgreSQL\%%V\bin\psql.exe" (
        set "PSQL=C:\Program Files\PostgreSQL\%%V\bin\psql.exe"
        goto :Found
    )
    if exist "C:\Program Files (x86)\PostgreSQL\%%V\bin\psql.exe" (
        set "PSQL=C:\Program Files (x86)\PostgreSQL\%%V\bin\psql.exe"
        goto :Found
    )
)

REM --- 3. SE NON TROVATO, CHIEDI ALL'UTENTE ---
:AskUser
cls
echo ==========================================
echo  ATTENZIONE: PostgreSQL non trovato!
echo ==========================================
echo.
echo Non riesco a trovare il file 'psql.exe' in automatico.
echo Per favore, trova il file psql.exe nel tuo computer
echo e TRASCINALO dentro questa finestra, poi premi INVIO.
echo.
set /p "PSQL=Trascina qui psql.exe > "

REM Rimuove le virgolette se l'utente le ha messe trascinando
set "PSQL=%PSQL:"=%"

if not exist "%PSQL%" (
    echo.
    echo [ERRORE] Il file non esiste. Riprova.
    pause
    goto :AskUser
)

:Found
echo.
echo [OK] Uso PostgreSQL qui: "%PSQL%"
echo.

REM --- ESECUZIONE SCRIPT SQL ---
echo Sto creando il database e il ruolo...
"%PSQL%" -U postgres -d postgres -f "%SCRIPT_DIR%00_create_role_and_db_ifantastici4.psql"

if %errorlevel% neq 0 (
    echo.
    echo [ERRORE] Qualcosa e' andato storto.
    echo Controlla la password o se il server e' avviato.
    pause
    exit /b 1
)

echo.
echo [SUCCESSO] Operazione completata!
echo Premi un tasto per chiudere.
pause