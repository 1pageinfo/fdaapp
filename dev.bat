@echo off
powershell -NoProfile -Command "$env:Path = [Environment]::GetEnvironmentVariable('Path', 'User') + ';' + [Environment]::GetEnvironmentVariable('Path', 'Machine'); composer run dev"
