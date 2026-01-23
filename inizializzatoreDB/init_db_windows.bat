@echo off
setlocal enabledelayedexpansion

REM ==========================================================
REM init_db_windows.bat
REM Doppio click per creare ruolo+DB (PostgreSQL deve essere installato e avviato).
REM Se chiede password: inserire la password dell'utente postgres impostata in installazione.
REM ==========================================================

set SCRIPT_DIR=%~dp0
set PSQL=

REM 1) Se l'utente ha impostato PSQL_PATH nelle variabili d'ambiente, usalo
if not "%PSQL_PATH%"=="" (
  if exist "%PSQL_PATH%" set PSQL=%PSQL_PATH%
)

REM 2) Cerca psql nelle installazioni tipiche PostgreSQL (16-18)
if "%PSQL%"=="" (
  for %%V in (18 17 16) do (
    if exist "C:\Program Files\PostgreSQL\%%V\bin\psql.exe" (
      set PSQL=C:\Program Files\PostgreSQL\%%V\bin\psql.exe
      goto :found
    )
  )
)

:found
if "%PSQL%"=="" (
  echo ERRORE: non trovo psql.exe.
  echo - Installa PostgreSQL e i Command Line Tools (psql)
  echo - Oppure imposta la variabile d'ambiente PSQL_PATH col percorso completo di psql.exe
  pause
  exit /b 1
)

echo Uso psql: "%PSQL%"
echo Eseguo script di creazione DB/ruolo...
"%PSQL%" -U postgres -d postgres -f "%SCRIPT_DIR%00_create_role_and_db_ifantastici4.psql"

if errorlevel 1 (
  echo.
  echo ERRORE: inizializzazione non completata.
  echo Controlla che il servizio PostgreSQL sia avviato e che user/password siano corretti.
  pause
  exit /b 1
)

echo.
echo OK! Ora apri setup.php nel browser per creare tabelle e dati.
echo.
pause
