@echo off
echo Compilando Triptico Documental a PDF usando Edge Headless...

set EDGE_PATH="C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"

echo Procesando PROJECT_RULES...
%EDGE_PATH% --headless=new --print-to-pdf="%~dp0\archivo\legacy-specs\PROJECT_RULES.pdf" "file:///%~dp0archivo\legacy-specs\PROJECT_RULES.html"

echo Procesando APPLICATION_WORKFLOW_MANUAL...
%EDGE_PATH% --headless=new --print-to-pdf="%~dp0\archivo\legacy-specs\APPLICATION_WORKFLOW_MANUAL.pdf" "file:///%~dp0archivo\legacy-specs\APPLICATION_WORKFLOW_MANUAL.html"

echo Procesando ROLES_AND_WORKFLOWS_MANUAL...
%EDGE_PATH% --headless=new --print-to-pdf="%~dp0\archivo\legacy-specs\ROLES_AND_WORKFLOWS_MANUAL.pdf" "file:///%~dp0archivo\legacy-specs\ROLES_AND_WORKFLOWS_MANUAL.html"

echo Compilacion terminada.

