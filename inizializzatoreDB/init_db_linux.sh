#!/usr/bin/env bash
set -euo pipefail

# ==========================================================
# init_db_linux.sh
# Esegui: chmod +x init_db_linux.sh && ./init_db_linux.sh
# Ti chiederà la password di postgres se necessario.
# ==========================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
psql -U postgres -d postgres -f "${SCRIPT_DIR}/00_create_role_and_db_ifantastici4.psql"

echo
echo "OK! Ora apri setup.php nel browser per creare tabelle e dati."
