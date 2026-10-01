@echo off
REM Instalar agente ControlSala como inicio automatico (Win11)
pip install -r "%~dp0requirements.txt"
set SERVIDOR=http://192.168.1.10/controlsala
echo Edita agente.py y pon SERVIDOR=%SERVIDOR% y el TOKEN
schtasks /create /tn "ControlSala" /tr "pythonw \"%~dp0agente.py\"" /sc onlogon /rl highest /f
echo Tarea creada. Se ejecutara al iniciar sesion.
pause
